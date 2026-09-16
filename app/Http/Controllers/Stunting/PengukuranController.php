<?php

namespace App\Http\Controllers\Stunting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stunting\StorePengukuranRequest;
use App\Models\Anak;
use App\Models\Pengukuran;
use Illuminate\Http\RedirectResponse;

class PengukuranController extends Controller
{
    public function store(StorePengukuranRequest $request, Anak $anak): RedirectResponse
    {
        // Setiap pengukuran adalah record baru; data pengukuran lama tidak pernah ditimpa (PRD Bagian 21).
        $anak->pengukurans()->create([
            'tanggal_pengukuran' => $request->validated('tanggal_pengukuran'),
            'berat_badan' => $request->validated('berat_badan'),
            'panjang_tinggi_badan' => $request->validated('panjang_tinggi_badan'),
            'lingkar_kepala' => $request->validated('lingkar_kepala'),
            'lingkar_lengan_atas' => $request->validated('lingkar_lengan_atas'),
            'petugas_pengukur_id' => $request->user()->id,
            'tempat_pengukuran' => $request->validated('tempat_pengukuran'),
            'indikator_antropometri' => 'BB/U, TB/U, BB/TB',
            'hasil_kategori' => $request->validated('hasil_kategori'),
        ]);

        return back()->with('status', 'Data pengukuran berhasil ditambahkan.');
    }

    public function destroy(Anak $anak, Pengukuran $pengukuran): RedirectResponse
    {
        abort_unless($pengukuran->anak_id === $anak->id, 404);

        $pengukuran->delete();

        return back()->with('status', 'Data pengukuran berhasil dihapus.');
    }
}
