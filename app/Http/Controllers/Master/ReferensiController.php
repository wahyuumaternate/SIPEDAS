<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreReferensiRequest;
use App\Http\Requests\Master\UpdateReferensiRequest;
use App\Models\Referensi;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferensiController extends Controller
{
    public function index(Request $request): View
    {
        $query = Referensi::query();

        if ($kategori = $request->string('kategori')->trim()->value()) {
            $query->where('kategori', $kategori);
        }

        $referensis = $query->orderBy('kategori')->orderBy('urutan')->paginate(20)->withQueryString();

        $kategoris = Referensi::query()->distinct()->orderBy('kategori')->pluck('kategori');

        return view('master.referensi.index', [
            'referensis' => $referensis,
            'kategoris' => $kategoris,
            'filters' => $request->only('kategori'),
        ]);
    }

    public function store(StoreReferensiRequest $request): RedirectResponse
    {
        Referensi::create($request->validated());

        return back()->with('status', 'Referensi berhasil ditambahkan.');
    }

    public function update(UpdateReferensiRequest $request, Referensi $referensi): RedirectResponse
    {
        $referensi->update($request->validated());

        return back()->with('status', 'Referensi berhasil diperbarui.');
    }

    public function destroy(Referensi $referensi): RedirectResponse
    {
        try {
            $referensi->delete();
        } catch (QueryException) {
            return back()->with('error', 'Referensi tidak dapat dihapus karena masih digunakan pada data lain.');
        }

        return back()->with('status', 'Referensi berhasil dihapus.');
    }
}
