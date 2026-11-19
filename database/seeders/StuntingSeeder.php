<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\User;
use Illuminate\Database\Seeder;

class StuntingSeeder extends Seeder
{
    /**
     * Empat anak contoh: 2 sudah terverifikasi (valid) dan 2 masih dalam verifikasi.
     * Setiap anak berasal dari keluarga di KemiskinanSeeder (Nomor KK & orang tua sama),
     * sehingga pencarian Nomor KK di form stunting dapat langsung dicoba.
     *
     * @var array<int, array<string, mixed>>
     */
    public const ANAK = [
        [
            'nomor_kk' => '8271010101240001',
            'status' => 'valid',
            'pengukuran' => [
                ['bulan_lalu' => 2, 'bb' => 7.4, 'tb' => 69.5, 'kategori' => 'stunting_sedang', 'gizi' => 'gizi_kurang'],
                ['bulan_lalu' => 0, 'bb' => 7.9, 'tb' => 70.8, 'kategori' => 'stunting_sedang', 'gizi' => 'gizi_kurang'],
            ],
            'kehamilan' => ['anc' => 4, 'ttd' => 90, 'kek' => true, 'pmt_kek' => false],
            'kesehatan' => ['imunisasi' => 'tidak_lengkap', 'diare' => true, 'oralit_zinc' => true, 'tambahan_asupan_gizi' => true, 'obat_cacing' => false, 'jkn' => true],
            'asi' => ['imd' => false, 'eksklusif' => false, 'masih_asi' => true, 'mpasi' => 6],
            'sanitasi' => ['air' => 'sumur_gali', 'jamban' => 'cemplung', 'septic_tank' => false],
            'akta' => true,
        ],
        [
            'nomor_kk' => '8271010101240002',
            'status' => 'valid',
            'pengukuran' => [
                ['bulan_lalu' => 0, 'bb' => 12.6, 'tb' => 89.0, 'kategori' => 'normal', 'gizi' => 'gizi_baik'],
            ],
            'kehamilan' => ['anc' => 6, 'ttd' => 180, 'kek' => false, 'pmt_kek' => false],
            'kesehatan' => ['imunisasi' => 'lengkap', 'diare' => false, 'oralit_zinc' => false, 'tambahan_asupan_gizi' => false, 'obat_cacing' => true, 'jkn' => true],
            'asi' => ['imd' => true, 'eksklusif' => true, 'masih_asi' => false, 'mpasi' => 6],
            'sanitasi' => ['air' => 'pdam', 'jamban' => 'leher_angsa', 'septic_tank' => true],
            'akta' => true,
        ],
        [
            'nomor_kk' => '8271010101240003',
            'status' => 'dalam_verifikasi',
            'pengukuran' => [
                ['bulan_lalu' => 0, 'bb' => 6.8, 'tb' => 64.0, 'kategori' => 'stunting_ringan', 'gizi' => 'gizi_baik'],
            ],
            'kehamilan' => ['anc' => 5, 'ttd' => 120, 'kek' => false, 'pmt_kek' => false],
            'kesehatan' => ['imunisasi' => 'tidak_lengkap', 'diare' => false, 'oralit_zinc' => false, 'tambahan_asupan_gizi' => false, 'obat_cacing' => false, 'jkn' => false],
            'asi' => ['imd' => true, 'eksklusif' => true, 'masih_asi' => true, 'mpasi' => 6],
            'sanitasi' => ['air' => 'sumur_bor', 'jamban' => 'leher_angsa', 'septic_tank' => false],
            'akta' => false,
        ],
        [
            'nomor_kk' => '8271010101240004',
            'status' => 'dalam_verifikasi',
            'pengukuran' => [
                ['bulan_lalu' => 0, 'bb' => 11.2, 'tb' => 92.5, 'kategori' => 'stunting_berat', 'gizi' => 'gizi_buruk'],
            ],
            'kehamilan' => ['anc' => 2, 'ttd' => null, 'kek' => true, 'pmt_kek' => true],
            'kesehatan' => ['imunisasi' => 'tidak_lengkap', 'diare' => true, 'oralit_zinc' => false, 'tambahan_asupan_gizi' => true, 'obat_cacing' => true, 'jkn' => false],
            'asi' => ['imd' => false, 'eksklusif' => false, 'masih_asi' => false, 'mpasi' => 4],
            'sanitasi' => ['air' => 'air_hujan', 'jamban' => 'tidak_ada', 'septic_tank' => false],
            'akta' => false,
        ],
    ];

    public function run(): void
    {
        $petugas = PetugasSeeder::petugas();
        $verifikator = User::where('username', 'superadmin')->first() ?? $petugas;
        $keluargaPerKk = collect(KemiskinanSeeder::KELUARGA)->keyBy('nomor_kk');

        foreach (self::ANAK as $urutan => $data) {
            $keluarga = $keluargaPerKk[$data['nomor_kk']];
            $desa = DesaKelurahan::where('nama', $keluarga['desa'])->first();

            if ($desa === null) {
                $this->command?->error("Desa/kelurahan {$keluarga['desa']} belum tersedia. Jalankan WilayahSeeder.");

                return;
            }

            $ayah = collect($keluarga['anggota'])->firstWhere('hubungan', 'kepala_keluarga');
            $ibu = collect($keluarga['anggota'])->firstWhere('hubungan', 'istri');
            $balita = collect($keluarga['anggota'])->first(fn (array $a) => isset($a['bulan']));
            $tanggalLahir = now()->subMonths($balita['bulan'])->startOfDay();

            if (Anak::where('nik_anak', $balita['nik'])->exists()) {
                continue;
            }

            $anak = Anak::create([
                'kode_pendataan' => 'AN-'.now()->format('Ymd').'-'.str_pad((string) ($urutan + 1), 5, '0', STR_PAD_LEFT),
                'nik_anak' => $balita['nik'],
                'memiliki_akta_kelahiran' => $data['akta'],
                'nomor_kk' => $data['nomor_kk'],
                'nama_anak' => $balita['nama'],
                'jenis_kelamin' => $balita['jk'],
                'anak_ke' => $balita['nama'] === 'Aisyah Sangaji' ? 2 : 1,
                'tempat_lahir' => 'Ternate',
                'tanggal_lahir' => $tanggalLahir,
                'usia_bulan' => $balita['bulan'],
                'nama_ayah' => $ayah['nama'],
                'nama_ibu' => $ibu['nama'],
                'nomor_hp_orang_tua' => $keluarga['nomor_hp'],
                'alamat' => $keluarga['alamat'],
                'kecamatan_id' => $desa->kecamatan_id,
                'desa_kelurahan_id' => $desa->id,
                'petugas_id' => $petugas->id,
                'tanggal_input' => now()->subDays(8 - $urutan),
                'status_data' => $data['status'],
                'tanggal_pendataan' => now()->subDays(8 - $urutan),
            ]);

            $anak->orangTua()->create([
                'nik_ayah' => $ayah['nik'],
                'nama_ayah_lengkap' => $ayah['nama'],
                'tanggal_lahir_ayah' => $ayah['lahir'],
                'pendidikan_ayah' => $ayah['pendidikan'],
                'pekerjaan_ayah' => config("referensi.status_pekerjaan.{$ayah['pekerjaan']}"),
                'penghasilan_ayah' => 1_500_000,
                'nik_ibu' => $ibu['nik'],
                'nama_ibu_lengkap' => $ibu['nama'],
                'tanggal_lahir_ibu' => $ibu['lahir'],
                'pendidikan_ibu' => $ibu['pendidikan'],
                'pekerjaan_ibu' => config("referensi.status_pekerjaan.{$ibu['pekerjaan']}"),
                'penghasilan_ibu' => 0,
            ]);

            $anak->riwayatKehamilan()->create([
                'usia_ibu_saat_hamil' => (int) now()->subMonths($balita['bulan'])->diffInYears($ibu['lahir'], true),
                'kehamilan_ke' => 2,
                'jumlah_kehamilan' => 2,
                'jumlah_pemeriksaan_kehamilan' => $data['kehamilan']['anc'],
                'tempat_pemeriksaan' => 'Puskesmas',
                'konsumsi_tablet_tambah_darah' => match (true) {
                    $data['kehamilan']['ttd'] === null => 'tidak_pernah',
                    $data['kehamilan']['ttd'] >= 180 => 'rutin',
                    default => 'kadang',
                },
                'jumlah_tablet_tambah_darah' => $data['kehamilan']['ttd'],
                'status_kek' => $data['kehamilan']['kek'],
                'pmt_kek' => $data['kehamilan']['pmt_kek'],
                'makanan_bergizi_seimbang' => ! $data['kehamilan']['kek'],
                'kb_pascapersalinan' => $data['kehamilan']['anc'] >= 6,
                'lila' => $data['kehamilan']['kek'] ? 22.5 : 25.0,
            ]);

            $anak->riwayatKelahiran()->create([
                'tanggal_lahir' => $tanggalLahir,
                'tempat_lahir' => 'Puskesmas',
                'penolong_persalinan' => 'bidan',
                'cara_persalinan' => 'normal',
                'berat_badan_lahir' => $data['kehamilan']['kek'] ? 2.40 : 3.10,
                'panjang_badan_lahir' => $data['kehamilan']['kek'] ? 46.0 : 49.0,
                'status_bblr' => $data['kehamilan']['kek'],
                'imd' => $data['asi']['imd'],
                'kondisi_bayi_saat_lahir' => 'Sehat',
            ]);

            foreach ($data['pengukuran'] as $ukur) {
                $anak->pengukurans()->create([
                    'tanggal_pengukuran' => now()->subMonths($ukur['bulan_lalu'])->startOfDay(),
                    'berat_badan' => $ukur['bb'],
                    'panjang_tinggi_badan' => $ukur['tb'],
                    'petugas_pengukur_id' => $petugas->id,
                    'tempat_pengukuran' => 'Posyandu',
                    'indikator_antropometri' => 'BB/U, TB/U, BB/TB',
                    'hasil_kategori' => $ukur['kategori'],
                    'status_gizi' => $ukur['gizi'],
                    'status_bb_u' => match ($ukur['gizi']) {
                        'gizi_buruk' => 'bb_sangat_kurang',
                        'gizi_kurang' => 'bb_kurang',
                        default => 'bb_normal',
                    },
                ]);
            }

            $anak->riwayatKesehatan()->create([
                'status_imunisasi' => $data['kesehatan']['imunisasi'],
                'riwayat_diare' => $data['kesehatan']['diare'],
                'akses_pelayanan_kesehatan' => true,
                'tata_laksana_gizi_buruk' => $data['pengukuran'][0]['gizi'] === 'gizi_buruk',
                'tambahan_asupan_gizi' => $data['kesehatan']['tambahan_asupan_gizi'],
                'oralit_zinc' => $data['kesehatan']['oralit_zinc'],
                'obat_cacing' => $data['kesehatan']['obat_cacing'],
                'memiliki_jkn' => $data['kesehatan']['jkn'],
            ]);

            $anak->asiMpasi()->create([
                'imd' => $data['asi']['imd'],
                'asi_eksklusif' => $data['asi']['eksklusif'],
                'masih_asi' => $data['asi']['masih_asi'],
                'usia_mulai_mpasi_bulan' => $balita['bulan'] >= $data['asi']['mpasi'] ? $data['asi']['mpasi'] : null,
                'frekuensi_makan' => '3 kali sehari',
                'jenis_makanan' => 'Nasi, sayur, ikan',
                'sumber_protein' => 'Ikan',
                'konsumsi_sayur' => true,
            ]);

            $anak->sanitasi()->create([
                'sumber_air_minum' => $data['sanitasi']['air'],
                'sumber_air_memasak' => $data['sanitasi']['air'],
                'kepemilikan_jamban' => $data['sanitasi']['jamban'] !== 'tidak_ada',
                'jenis_jamban' => $data['sanitasi']['jamban'],
                'septic_tank' => $data['sanitasi']['septic_tank'],
                'pengelolaan_sampah' => 'diangkut_petugas',
                'kondisi_rumah' => 'semi_permanen',
            ]);

            if ($data['status'] === 'valid') {
                $anak->riwayatVerifikasi()->create([
                    'verifikator_id' => $verifikator->id,
                    'status' => 'valid',
                    'catatan' => 'Data dan hasil pengukuran sudah dicek di posyandu.',
                    'tanggal_verifikasi' => now()->subDays(2),
                ]);
            }
        }
    }
}
