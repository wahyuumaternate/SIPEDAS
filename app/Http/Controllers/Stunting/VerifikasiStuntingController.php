<?php

namespace App\Http\Controllers\Stunting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stunting\StoreVerifikasiStuntingRequest;
use App\Models\Anak;
use App\Models\AuditLog;
use App\Models\Kecamatan;
use App\Models\VerifikasiStunting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifikasiStuntingController extends Controller
{
    /**
     * Status anak yang boleh ditransisikan dari status saat ini (PRD Bagian 34).
     *
     * @var array<string, array<int, string>>
     */
    private const TRANSISI_DIIZINKAN = [
        'dalam_verifikasi' => ['valid', 'perlu_perbaikan', 'tidak_valid'],
    ];

    public function index(Request $request): View
    {
        $query = Anak::query()
            ->with(['kecamatan', 'desaKelurahan', 'petugas'])
            ->whereIn('status_data', array_keys(self::TRANSISI_DIIZINKAN));

        if ($status = $request->string('status_data')->trim()->value()) {
            $query->where('status_data', $status);
        }

        if ($kecamatanId = $request->integer('kecamatan_id')) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        $antrian = $query->oldest('tanggal_pendataan')->paginate(15)->withQueryString();

        return view('stunting.verifikasi.index', [
            'antrian' => $antrian,
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $request->only(['status_data', 'kecamatan_id']),
        ]);
    }

    public function store(StoreVerifikasiStuntingRequest $request, Anak $anak): RedirectResponse
    {
        $status = $request->validated('status');
        $statusLama = $anak->status_data;
        $diizinkan = self::TRANSISI_DIIZINKAN[$statusLama] ?? [];

        if (! in_array($status, $diizinkan, true)) {
            return back()->with('error', "Data berstatus \"{$anak->status_data}\" tidak dapat diubah menjadi \"{$status}\".");
        }

        $anak->update(['status_data' => $status]);

        VerifikasiStunting::create([
            'anak_id' => $anak->id,
            'verifikator_id' => $request->user()->id,
            'status' => $status,
            'catatan' => $request->validated('catatan'),
            'tanggal_verifikasi' => now(),
        ]);

        AuditLog::catat('verifikasi_stunting', 'verify', $anak, ['status_data' => $statusLama], ['status_data' => $status], "Mengubah status verifikasi anak \"{$anak->nama_anak}\" dari {$statusLama} menjadi {$status}.");

        return redirect()->route('stunting.show', $anak)->with('status', 'Status verifikasi berhasil diperbarui.');
    }
}
