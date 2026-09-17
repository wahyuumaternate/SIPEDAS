<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreKecamatanRequest;
use App\Http\Requests\Master\UpdateKecamatanRequest;
use App\Models\Kecamatan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class KecamatanController extends Controller
{
    public function store(StoreKecamatanRequest $request): RedirectResponse
    {
        Kecamatan::create($request->validated());

        return back()->with('status', 'Kecamatan berhasil ditambahkan.');
    }

    public function update(UpdateKecamatanRequest $request, Kecamatan $kecamatan): RedirectResponse
    {
        $kecamatan->update($request->validated());

        return back()->with('status', 'Kecamatan berhasil diperbarui.');
    }

    public function destroy(Kecamatan $kecamatan): RedirectResponse
    {
        try {
            $kecamatan->delete();
        } catch (QueryException) {
            return back()->with('error', 'Kecamatan tidak dapat dihapus karena masih memiliki data terkait (desa/kelurahan, petugas, atau data pendataan).');
        }

        return back()->with('status', 'Kecamatan berhasil dihapus.');
    }
}
