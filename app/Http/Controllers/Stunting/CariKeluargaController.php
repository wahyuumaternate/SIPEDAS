<?php

namespace App\Http\Controllers\Stunting;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\AnggotaKeluarga;
use App\Models\Keluarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Mencari data keluarga (modul kemiskinan) berdasarkan Nomor KK agar data orang tua & anak
 * dapat dipakai ulang saat pendataan stunting.
 */
class CariKeluargaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $nomorKk = $request->string('nomor_kk')->trim()->value();

        if (! preg_match('/^\d{16}$/', $nomorKk)) {
            return response()->json(['ditemukan' => false]);
        }

        $keluarga = Keluarga::query()
            ->with(['anggotaKeluarga', 'kecamatan', 'desaKelurahan'])
            ->where('nomor_kk', $nomorKk)
            ->latest('id')
            ->first();

        if ($keluarga === null) {
            return response()->json(['ditemukan' => false]);
        }

        $anggota = $keluarga->anggotaKeluarga;
        $kepala = $anggota->firstWhere('hubungan_keluarga', 'kepala_keluarga');

        // Hubungan keluarga lebih diutamakan daripada jenis kelamin (yang lebih rawan salah input):
        // ibu = istri, atau kepala keluarga perempuan; ayah = suami, atau kepala keluarga laki-laki.
        $ibu = $anggota->firstWhere('hubungan_keluarga', 'istri')
            ?? ($kepala?->jenis_kelamin === 'perempuan' ? $kepala : null);
        $ayah = $anggota->firstWhere('hubungan_keluarga', 'suami')
            ?? ($kepala?->jenis_kelamin === 'laki-laki' ? $kepala : null);

        // Kepala keluarga yang belum terpakai (mis. jenis kelamin keliru) melengkapi peran yang masih kosong.
        if ($kepala && $kepala->isNot($ibu) && $kepala->isNot($ayah)) {
            $ayah ??= $kepala;
        }

        $balita = $anggota
            ->where('hubungan_keluarga', 'anak')
            ->filter(fn (AnggotaKeluarga $a) => $a->tanggal_lahir && $a->tanggal_lahir->gt(now()->subYears(5)));
        $sudahTerdata = $this->anakSudahTerdata($nomorKk, $request->integer('kecuali_anak_id'));

        return response()->json([
            'ditemukan' => true,
            'keluarga' => [
                'nama_kepala_keluarga' => $keluarga->nama_kepala_keluarga,
                'nomor_hp' => $keluarga->nomor_hp,
                'alamat' => $keluarga->alamat,
                'kecamatan_id' => $keluarga->kecamatan_id,
                'desa_kelurahan_id' => $keluarga->desa_kelurahan_id,
                'wilayah' => trim(($keluarga->desaKelurahan?->nama ?? '').', '.($keluarga->kecamatan?->nama ?? ''), ', '),
            ],
            'ayah' => $this->dataOrangTua($ayah),
            'ibu' => $this->dataOrangTua($ibu),
            // Anak balita (di bawah 5 tahun) yang belum terdata di modul stunting.
            'anak' => $balita
                ->reject(fn (AnggotaKeluarga $a) => $this->cocokDenganAnakTerdata($a, $sudahTerdata))
                ->map(fn (AnggotaKeluarga $a) => [
                    'nik' => $a->nik,
                    'nama' => $a->nama_lengkap,
                    'jenis_kelamin' => $a->jenis_kelamin,
                    'tempat_lahir' => $a->tempat_lahir,
                    'tanggal_lahir' => $a->tanggal_lahir->format('Y-m-d'),
                ])
                ->values(),
            'jumlah_balita_terdata' => $balita->filter(fn (AnggotaKeluarga $a) => $this->cocokDenganAnakTerdata($a, $sudahTerdata))->count(),
        ]);
    }

    /**
     * Anak yang sudah tercatat di modul stunting untuk Nomor KK ini (kecuali anak yang sedang diedit).
     *
     * @return Collection<int, Anak>
     */
    private function anakSudahTerdata(string $nomorKk, int $kecualiAnakId): Collection
    {
        return Anak::query()
            ->where('nomor_kk', $nomorKk)
            ->when($kecualiAnakId > 0, fn ($q) => $q->whereKeyNot($kecualiAnakId))
            ->get(['id', 'nik_anak', 'nama_anak', 'tanggal_lahir']);
    }

    /**
     * Cocok bila NIK sama, atau (untuk anggota tanpa NIK) nama & tanggal lahir sama.
     *
     * @param  Collection<int, Anak>  $terdata
     */
    private function cocokDenganAnakTerdata(AnggotaKeluarga $anggota, Collection $terdata): bool
    {
        return $terdata->contains(fn (Anak $anak) => (filled($anggota->nik) && $anak->nik_anak === $anggota->nik)
            || (mb_strtolower(trim($anak->nama_anak)) === mb_strtolower(trim($anggota->nama_lengkap))
                && $anak->tanggal_lahir?->isSameDay($anggota->tanggal_lahir)));
    }

    /**
     * @return array{nik: ?string, nama: string, tanggal_lahir: ?string, pendidikan: ?string, pekerjaan: ?string}|null
     */
    private function dataOrangTua(?AnggotaKeluarga $anggota): ?array
    {
        if ($anggota === null) {
            return null;
        }

        return [
            'nik' => $anggota->nik,
            'nama' => $anggota->nama_lengkap,
            'tanggal_lahir' => $anggota->tanggal_lahir?->format('Y-m-d'),
            'pendidikan' => $anggota->pendidikan_terakhir,
            // Pekerjaan di form stunting berupa teks bebas, jadi dikirim label-nya.
            'pekerjaan' => $anggota->status_pekerjaan ? config("referensi.status_pekerjaan.{$anggota->status_pekerjaan}", $anggota->status_pekerjaan) : null,
        ];
    }
}
