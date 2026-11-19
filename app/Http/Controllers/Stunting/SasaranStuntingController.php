<?php

namespace App\Http\Controllers\Stunting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stunting\UpdateSasaranStuntingRequest;
use App\Models\Kecamatan;
use App\Models\SasaranStunting;
use App\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Data sasaran (populasi) stunting per kecamatan per tahun untuk Laporan Analisis Situasi.
 */
class SasaranStuntingController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = $this->tahunDipilih($request);

        return view('stunting.sasaran.index', [
            'tahun' => $tahun,
            'daftarTahun' => range((int) now()->year + 1, (int) now()->year - 5),
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('kode')->get(),
            'sasaran' => SasaranStunting::where('tahun', $tahun)->get()->keyBy('kecamatan_id'),
            'bisaUbah' => $request->user()->hasPermission(Permission::StuntingEdit),
        ]);
    }

    public function update(UpdateSasaranStuntingRequest $request): RedirectResponse
    {
        $tahun = (int) $request->validated('tahun');

        DB::transaction(function () use ($request, $tahun) {
            foreach ($request->validated('sasaran') as $baris) {
                $nilai = collect(SasaranStunting::KOLOM)
                    ->keys()
                    ->mapWithKeys(fn (string $kolom) => [$kolom => isset($baris[$kolom]) && $baris[$kolom] !== '' ? (int) $baris[$kolom] : null])
                    ->all();

                SasaranStunting::updateOrCreate(
                    ['kecamatan_id' => (int) $baris['kecamatan_id'], 'tahun' => $tahun],
                    $nilai,
                );
            }
        });

        return redirect()
            ->route('stunting.sasaran.index', ['tahun' => $tahun])
            ->with('status', "Data sasaran tahun {$tahun} berhasil disimpan.");
    }

    private function tahunDipilih(Request $request): int
    {
        $tahun = $request->integer('tahun', (int) now()->year);

        return $tahun >= 2000 && $tahun <= 2100 ? $tahun : (int) now()->year;
    }
}
