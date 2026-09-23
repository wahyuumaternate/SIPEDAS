<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VerifikasiValidasiController extends Controller
{
    /**
     * Status yang berarti data masih dalam antrian verifikasi (PRD Bagian 34).
     *
     * @var array<int, string>
     */
    private const STATUS_ANTRIAN = ['dalam_verifikasi'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $lihatKemiskinan = $user->hasPermission(Permission::KemiskinanVerify);
        $lihatStunting = $user->hasPermission(Permission::StuntingVerify);

        $filters = $request->only(['status_data', 'kecamatan_id']);

        $antrianKemiskinan = null;
        $antrianStunting = null;

        if ($lihatKemiskinan) {
            $queryKeluarga = Keluarga::query()
                ->with(['kecamatan', 'desaKelurahan', 'petugas'])
                ->whereIn('status_data', self::STATUS_ANTRIAN);

            $this->terapkanFilter($queryKeluarga, $filters);

            $antrianKemiskinan = $queryKeluarga->oldest('tanggal_pendataan')
                ->paginate(10, ['*'], 'halaman_kemiskinan')->withQueryString();

        }

        if ($lihatStunting) {
            $queryAnak = Anak::query()
                ->with(['kecamatan', 'desaKelurahan', 'petugas'])
                ->whereIn('status_data', self::STATUS_ANTRIAN);

            $this->terapkanFilter($queryAnak, $filters);

            $antrianStunting = $queryAnak->oldest('tanggal_pendataan')
                ->paginate(10, ['*'], 'halaman_stunting')->withQueryString();

        }

        return view('verifikasi-validasi.index', [
            'lihatKemiskinan' => $lihatKemiskinan,
            'lihatStunting' => $lihatStunting,
            'antrianKemiskinan' => $antrianKemiskinan,
            'antrianStunting' => $antrianStunting,
            'ringkasan' => [
                'kemiskinan_dalam_verifikasi' => $lihatKemiskinan ? Keluarga::where('status_data', 'dalam_verifikasi')->count() : 0,
                'stunting_dalam_verifikasi' => $lihatStunting ? Anak::where('status_data', 'dalam_verifikasi')->count() : 0,
            ],
            'kecamatans' => Kecamatan::where('is_active', true)->orderBy('nama')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function terapkanFilter(Builder $query, array $filters): void
    {
        if ($status = $filters['status_data'] ?? null) {
            $query->where('status_data', $status);
        }

        if ($kecamatanId = $filters['kecamatan_id'] ?? null) {
            $query->where('kecamatan_id', $kecamatanId);
        }
    }
}
