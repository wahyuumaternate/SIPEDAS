<?php

namespace App\Http\Controllers\Kemiskinan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kemiskinan\StoreDokumenKeluargaRequest;
use App\Models\DokumenKeluarga;
use App\Models\Keluarga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class DokumenKeluargaController extends Controller
{
    public function store(StoreDokumenKeluargaRequest $request, Keluarga $keluarga): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store("dokumen-keluarga/{$keluarga->id}", 'public');

        $keluarga->dokumen()->create([
            'nama_file' => $file->getClientOriginalName(),
            'path_file' => $path,
            'jenis_dokumentasi' => $request->validated('jenis_dokumentasi'),
            'tanggal_upload' => now(),
            'user_id' => $request->user()->id,
            'keterangan' => $request->validated('keterangan'),
        ]);

        return back()->with('status', 'Dokumentasi berhasil diunggah.');
    }

    public function destroy(Keluarga $keluarga, DokumenKeluarga $dokumen): RedirectResponse
    {
        abort_unless($dokumen->keluarga_id === $keluarga->id, 404);

        Storage::disk('public')->delete($dokumen->path_file);
        $dokumen->delete();

        return back()->with('status', 'Dokumentasi berhasil dihapus.');
    }
}
