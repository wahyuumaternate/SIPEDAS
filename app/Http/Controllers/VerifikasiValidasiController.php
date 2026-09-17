<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VerifikasiValidasiController extends Controller
{
    /**
     * Status yang berarti data masih dalam antrian verifikasi (PRD Bagian 34).
     *
     * @var array<int, string>
     */
    private const STATUS_ANTRIAN = ['dikirim', 'dalam_verifikasi'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $lihatKemiskinan = $user->hasPermission(Permission::KemiskinanVerify);
        $lihatStunting = $user->hasPermission(Permission::StuntingVerify);

        $filters = $request->only(['status_data', 'kecamatan_id']);

        $antrianKemiskinan = null;
        $antrianStunting = null;
        $duplikatKeluarga = collect();
        $duplikatAnak = collect();

        if ($lihatKemiskinan) {
            $queryKeluarga = Keluarga::query()
                ->with(['kecamatan', 'desaKelurahan', 'petugas'])
                ->whereIn('status_data', self::STATUS_ANTRIAN);

            $this->terapkanFilter($queryKeluarga, $filters);

            $antrianKemiskinan = $queryKeluarga->oldest('tanggal_pendataan')
                ->paginate(10, ['*'], 'halaman_kemiskinan')->withQueryString();

            $duplikatKeluarga = $this->identitasDuplikat(Keluarga::class, 'nik_kepala_keluarga', 'nomor_kk');
        }

        if ($lihatStunting) {
            $queryAnak = Anak::query()
                ->with(['kecamatan', 'desaKelurahan', 'petugas'])
                ->whereIn('status_data', self::STATUS_ANTRIAN);

            $this->terapkanFilter($queryAnak, $filters);

            $antrianStunting = $queryAnak->oldest('tanggal_pendataan')
                ->paginate(10, ['*'], 'halaman_stunting')->withQueryString();

            $duplikatAnak = $this->identitasDuplikat(Anak::class, 'nik_anak', 'nomor_kk');
        }

        return view('verifikasi-validasi.index', [
            'lihatKemiskinan' => $lihatKemiskinan,
            'lihatStunting' => $lihatStunting,
            'antrianKemiskinan' => $antrianKemiskinan,
            'antrianStunting' => $antrianStunting,
            'duplikatKeluarga' => $duplikatKeluarga,
            'duplikatAnak' => $duplikatAnak,
            'ringkasan' => [
                'kemiskinan_dikirim' => $lihatKemiskinan ? Keluarga::where('status_data', 'dikirim')->count() : 0,
                'kemiskinan_dalam_verifikasi' => $lihatKemiskinan ? Keluarga::where('status_data', 'dalam_verifikasi')->count() : 0,
                'stunting_dikirim' => $lihatStunting ? Anak::where('status_data', 'dikirim')->count() : 0,
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

    /**
     * Cari nilai NIK/nomor KK yang muncul lebih dari sekali (validasi otomatis PRD Bagian 33.3),
     * supaya baris antrian yang identitasnya berpotensi duplikat bisa ditandai tanpa query per-baris.
     *
     * @return Collection<int, string>
     */
    private function identitasDuplikat(string $model, string $kolomNik, string $kolomKk): Collection
    {
        $nikDuplikat = $model::query()
            ->select($kolomNik)
            ->whereNotNull($kolomNik)
            ->groupBy($kolomNik)
            ->havingRaw('count(*) > 1')
            ->pluck($kolomNik);

        $kkDuplikat = $model::query()
            ->select($kolomKk)
            ->whereNotNull($kolomKk)
            ->groupBy($kolomKk)
            ->havingRaw('count(*) > 1')
            ->pluck($kolomKk);

        return $nikDuplikat->merge($kkDuplikat)->unique()->values();
    }
}
