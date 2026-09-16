<?php

namespace App\Http\Controllers\Kemiskinan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kemiskinan\StoreVerifikasiRequest;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\VerifikasiKemiskinan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifikasiKemiskinanController extends Controller
{
    /**
     * Status keluarga yang boleh ditransisikan dari status saat ini (PRD Bagian 25).
     *
     * @var array<string, array<int, string>>
     */
    private const TRANSISI_DIIZINKAN = [
        'dikirim' => ['dalam_verifikasi', 'valid', 'perlu_perbaikan', 'tidak_valid', 'duplikat'],
        'dalam_verifikasi' => ['valid', 'perlu_perbaikan', 'tidak_valid', 'duplikat'],
    ];

    public function index(Request $request): View
    {
        $query = Keluarga::query()
            ->with(['kecamatan', 'desaKelurahan', 'petugas'])
            ->whereIn('status_data', array_keys(self::TRANSISI_DIIZINKAN));

        if ($status = $request->string('status_data')->trim()->value()) {
            $query->where('status_data', $status);
        }

        if ($kecamatanId = $request->integer('kecamatan_id')) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        $antrian = $query->oldest('tanggal_pendataan')->paginate(15)->withQueryString();

        return view('kemiskinan.verifikasi.index', [
            'antrian' => $antrian,
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $request->only(['status_data', 'kecamatan_id']),
        ]);
    }

    public function store(StoreVerifikasiRequest $request, Keluarga $keluarga): RedirectResponse
    {
        $status = $request->validated('status');
        $diizinkan = self::TRANSISI_DIIZINKAN[$keluarga->status_data] ?? [];

        if (! in_array($status, $diizinkan, true)) {
            return back()->with('error', "Data berstatus \"{$keluarga->status_data}\" tidak dapat diubah menjadi \"{$status}\".");
        }

        $keluarga->update(['status_data' => $status]);

        VerifikasiKemiskinan::create([
            'keluarga_id' => $keluarga->id,
            'verifikator_id' => $request->user()->id,
            'status' => $status,
            'catatan' => $request->validated('catatan'),
            'tanggal_verifikasi' => now(),
        ]);

        return redirect()->route('kemiskinan.show', $keluarga)->with('status', 'Status verifikasi berhasil diperbarui.');
    }
}
