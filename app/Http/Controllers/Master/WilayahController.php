<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use Illuminate\Contracts\View\View;

class WilayahController extends Controller
{
    public function index(): View
    {
        $kecamatans = Kecamatan::withCount(['desaKelurahans', 'users', 'keluargas', 'anaks'])
            ->orderBy('nama')
            ->get();

        $desaKelurahans = DesaKelurahan::with('kecamatan')
            ->withCount(['users', 'keluargas', 'anaks'])
            ->orderBy('nama')
            ->get();

        return view('master.wilayah.index', [
            'kecamatans' => $kecamatans,
            'desaKelurahans' => $desaKelurahans,
        ]);
    }
}
