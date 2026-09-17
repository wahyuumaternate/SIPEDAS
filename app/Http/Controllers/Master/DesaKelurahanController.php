<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreDesaKelurahanRequest;
use App\Http\Requests\Master\UpdateDesaKelurahanRequest;
use App\Models\DesaKelurahan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class DesaKelurahanController extends Controller
{
    public function store(StoreDesaKelurahanRequest $request): RedirectResponse
    {
        DesaKelurahan::create($request->validated());

        return back()->with('status', 'Desa/Kelurahan berhasil ditambahkan.');
    }

    public function update(UpdateDesaKelurahanRequest $request, DesaKelurahan $desaKelurahan): RedirectResponse
    {
        $desaKelurahan->update($request->validated());

        return back()->with('status', 'Desa/Kelurahan berhasil diperbarui.');
    }

    public function destroy(DesaKelurahan $desaKelurahan): RedirectResponse
    {
        try {
            $desaKelurahan->delete();
        } catch (QueryException) {
            return back()->with('error', 'Desa/Kelurahan tidak dapat dihapus karena masih memiliki data terkait (petugas atau data pendataan).');
        }

        return back()->with('status', 'Desa/Kelurahan berhasil dihapus.');
    }
}
