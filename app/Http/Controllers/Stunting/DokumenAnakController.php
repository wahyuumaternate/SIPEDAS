<?php

namespace App\Http\Controllers\Stunting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stunting\StoreDokumenAnakRequest;
use App\Models\Anak;
use App\Models\DokumenAnak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class DokumenAnakController extends Controller
{
    public function store(StoreDokumenAnakRequest $request, Anak $anak): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store("dokumen-anak/{$anak->id}", 'public');

        $anak->dokumen()->create([
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $path,
            'jenis_dokumentasi' => $request->validated('jenis_dokumentasi'),
            'tanggal_upload' => now(),
            'user_id' => $request->user()->id,
            'keterangan' => $request->validated('keterangan'),
        ]);

        return back()->with('status', 'Dokumentasi berhasil diunggah.');
    }

    public function destroy(Anak $anak, DokumenAnak $dokumen): RedirectResponse
    {
        abort_unless($dokumen->anak_id === $anak->id, 404);

        Storage::disk('public')->delete($dokumen->path_file);
        $dokumen->delete();

        return back()->with('status', 'Dokumentasi berhasil dihapus.');
    }
}
