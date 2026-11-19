<?php

namespace Database\Seeders;

use App\Models\DesaKelurahan;
use App\Models\Keluarga;
use App\Models\User;
use Illuminate\Database\Seeder;

class KemiskinanSeeder extends Seeder
{
    /**
     * Empat keluarga contoh: 2 sudah terverifikasi (valid) dan 2 masih dalam verifikasi.
     * Nomor KK & anggotanya dipakai ulang oleh StuntingSeeder (anak balita di keluarga ini).
     *
     * @var array<int, array<string, mixed>>
     */
    public const KELUARGA = [
        [
            'nomor_kk' => '8271010101240001',
            'desa' => 'Jambula',
            'status' => 'valid',
            'desil' => 1,
            'nomor_hp' => '081340010001',
            'alamat' => 'Jl. Pantai Jambula',
            'rt' => '002',
            'rw' => '001',
            'program' => 'PKH',
            'anggota' => [
                ['nik' => '8271011203880001', 'nama' => 'Ahmad Sangaji', 'jk' => 'laki-laki', 'lahir' => '1988-03-12', 'hubungan' => 'kepala_keluarga', 'pendidikan' => 'smp', 'pekerjaan' => 'nelayan'],
                ['nik' => '8271015506920002', 'nama' => 'Fatma Hasan', 'jk' => 'perempuan', 'lahir' => '1992-06-15', 'hubungan' => 'istri', 'pendidikan' => 'sma', 'pekerjaan' => 'tidak_bekerja'],
                ['nik' => '8271014109150003', 'nama' => 'Rahmat Sangaji', 'jk' => 'laki-laki', 'lahir' => '2015-09-01', 'hubungan' => 'anak', 'pendidikan' => 'sd', 'pekerjaan' => null],
                ['nik' => '8271016000000004', 'nama' => 'Aisyah Sangaji', 'jk' => 'perempuan', 'bulan' => 14, 'hubungan' => 'anak', 'pendidikan' => 'tidak_sekolah', 'pekerjaan' => null],
            ],
        ],
        [
            'nomor_kk' => '8271010101240002',
            'desa' => 'Dufa-Dufa',
            'status' => 'valid',
            'desil' => 2,
            'nomor_hp' => '081340010002',
            'alamat' => 'Jl. Dufa-Dufa Pantai',
            'rt' => '004',
            'rw' => '002',
            'program' => 'Sembako',
            'anggota' => [
                ['nik' => '8271020707850001', 'nama' => 'Rusdi Umar', 'jk' => 'laki-laki', 'lahir' => '1985-07-07', 'hubungan' => 'kepala_keluarga', 'pendidikan' => 'sma', 'pekerjaan' => 'buruh_harian'],
                ['nik' => '8271024402900002', 'nama' => 'Nurlela Abdullah', 'jk' => 'perempuan', 'lahir' => '1990-02-04', 'hubungan' => 'istri', 'pendidikan' => 'smp', 'pekerjaan' => 'pedagang'],
                ['nik' => '8271020000000003', 'nama' => 'Fikri Umar', 'jk' => 'laki-laki', 'bulan' => 30, 'hubungan' => 'anak', 'pendidikan' => 'tidak_sekolah', 'pekerjaan' => null],
            ],
        ],
        [
            'nomor_kk' => '8271010101240003',
            'desa' => 'Sulamadaha',
            'status' => 'dalam_verifikasi',
            'desil' => 3,
            'nomor_hp' => '081340010003',
            'alamat' => 'Jl. Raya Sulamadaha',
            'rt' => '001',
            'rw' => '001',
            'program' => null,
            'anggota' => [
                ['nik' => '8271031511900001', 'nama' => 'Hamid Taher', 'jk' => 'laki-laki', 'lahir' => '1990-11-15', 'hubungan' => 'kepala_keluarga', 'pendidikan' => 'sd', 'pekerjaan' => 'petani'],
                ['nik' => '8271036308950002', 'nama' => 'Rosmini Saleh', 'jk' => 'perempuan', 'lahir' => '1995-08-23', 'hubungan' => 'istri', 'pendidikan' => 'smp', 'pekerjaan' => 'tidak_bekerja'],
                ['nik' => '8271030000000003', 'nama' => 'Zahra Taher', 'jk' => 'perempuan', 'bulan' => 8, 'hubungan' => 'anak', 'pendidikan' => 'tidak_sekolah', 'pekerjaan' => null],
            ],
        ],
        [
            'nomor_kk' => '8271010101240004',
            'desa' => 'Moti Kota',
            'status' => 'dalam_verifikasi',
            'desil' => null,
            'nomor_hp' => '081340010004',
            'alamat' => 'Desa Moti Kota',
            'rt' => '003',
            'rw' => '001',
            'program' => null,
            'anggota' => [
                ['nik' => '8271042001870001', 'nama' => 'Jainal Abidin', 'jk' => 'laki-laki', 'lahir' => '1987-01-20', 'hubungan' => 'kepala_keluarga', 'pendidikan' => 'sma', 'pekerjaan' => 'nelayan'],
                ['nik' => '8271045204910002', 'nama' => 'Hasna Ibrahim', 'jk' => 'perempuan', 'lahir' => '1991-04-12', 'hubungan' => 'istri', 'pendidikan' => 'sma', 'pekerjaan' => 'tidak_bekerja'],
                ['nik' => '8271040000000003', 'nama' => 'Ilham Abidin', 'jk' => 'laki-laki', 'bulan' => 42, 'hubungan' => 'anak', 'pendidikan' => 'tidak_sekolah', 'pekerjaan' => null],
                ['nik' => '8271046503600004', 'nama' => 'Salma Ibrahim', 'jk' => 'perempuan', 'lahir' => '1960-03-25', 'hubungan' => 'mertua', 'pendidikan' => 'sd', 'pekerjaan' => 'tidak_bekerja'],
            ],
        ],
    ];

    public function run(): void
    {
        $petugas = PetugasSeeder::petugas();
        $verifikator = User::where('username', 'superadmin')->first() ?? $petugas;

        foreach (self::KELUARGA as $urutan => $data) {
            $desa = DesaKelurahan::where('nama', $data['desa'])->first();

            if ($desa === null) {
                $this->command?->error("Desa/kelurahan {$data['desa']} belum tersedia. Jalankan WilayahSeeder.");

                return;
            }

            if (Keluarga::where('nomor_kk', $data['nomor_kk'])->exists()) {
                continue;
            }

            $kepala = $data['anggota'][0];

            $keluarga = Keluarga::create([
                'kode_pendataan' => 'KK-'.now()->format('Ymd').'-'.str_pad((string) ($urutan + 1), 5, '0', STR_PAD_LEFT),
                'nomor_kk' => $data['nomor_kk'],
                'nik_kepala_keluarga' => $kepala['nik'],
                'nama_kepala_keluarga' => $kepala['nama'],
                'nomor_hp' => $data['nomor_hp'],
                'jumlah_anggota_keluarga' => count($data['anggota']),
                'status_perkawinan' => 'kawin',
                'alamat' => $data['alamat'],
                'rt' => $data['rt'],
                'rw' => $data['rw'],
                'kecamatan_id' => $desa->kecamatan_id,
                'desa_kelurahan_id' => $desa->id,
                'desil' => $data['desil'],
                'status_dtsen' => 'aktif',
                'petugas_id' => $petugas->id,
                'tanggal_input' => now()->subDays(10 - $urutan),
                'status_data' => $data['status'],
                'tanggal_pendataan' => now()->subDays(10 - $urutan),
            ]);

            foreach ($data['anggota'] as $anggota) {
                $tanggalLahir = isset($anggota['bulan']) ? now()->subMonths($anggota['bulan'])->startOfDay() : $anggota['lahir'];

                $keluarga->anggotaKeluarga()->create([
                    'nik' => $anggota['nik'],
                    'nama_lengkap' => $anggota['nama'],
                    'jenis_kelamin' => $anggota['jk'],
                    'tempat_lahir' => 'Ternate',
                    'tanggal_lahir' => $tanggalLahir,
                    'usia' => (int) now()->diffInYears($tanggalLahir, true),
                    'hubungan_keluarga' => $anggota['hubungan'],
                    'status_perkawinan' => in_array($anggota['hubungan'], ['kepala_keluarga', 'istri'], true) ? 'kawin' : null,
                    'pendidikan_terakhir' => $anggota['pendidikan'],
                    'status_sekolah' => $anggota['hubungan'] === 'anak'
                        ? (now()->diffInYears($tanggalLahir, true) >= 7 ? 'sedang_sekolah' : 'belum_sekolah')
                        : null,
                    'status_pekerjaan' => $anggota['pekerjaan'] ?? (now()->diffInYears($tanggalLahir, true) >= 7 ? 'pelajar' : 'tidak_bekerja'),
                ]);
            }

            $keluarga->kondisiEkonomi()->create([
                'status_pekerjaan_kepala_keluarga' => $kepala['pekerjaan'],
                'pekerjaan_utama' => $kepala['pekerjaan'],
                'perlindungan_ketenagakerjaan' => false,
                'bahan_bakar_memasak' => $data['desil'] === 1 ? 'kayu_bakar' : 'lpg_3kg',
                'jumlah_anggota_bekerja' => 1,
                'jumlah_anggota_tidak_bekerja' => count($data['anggota']) - 1,
                'pendapatan_kepala_keluarga' => 1_500_000,
                'pendapatan_pasangan' => 0,
                'pendapatan_anggota_lainnya' => 0,
                'pengeluaran_makanan' => 900_000,
                'pengeluaran_pendidikan' => 100_000,
                'pengeluaran_kesehatan' => 50_000,
                'pengeluaran_listrik' => 100_000,
                'pengeluaran_air' => 50_000,
                'pengeluaran_transportasi' => 100_000,
                'pengeluaran_pakaian' => 50_000,
                'pengeluaran_lainnya' => 0,
            ]);

            $keluarga->kondisiRumah()->create([
                'status_kepemilikan' => 'milik_sendiri',
                'kondisi_bangunan' => $data['desil'] === 1 ? 'tidak_layak_huni' : 'semi_permanen',
                'jamban' => true,
                'jenis_jamban' => $data['desil'] === 1 ? 'cemplung' : 'leher_angsa',
                'septic_tank' => $data['desil'] !== 1,
                'jenis_septic_tank' => $data['desil'] === 1 ? 'lubang_tanah' : 'tangki_septik',
                'daya_listrik' => null,
                'luas_tanah' => 100,
                'luas_bangunan' => 36,
                'jumlah_kamar' => 2,
                'jumlah_penghuni' => count($data['anggota']),
            ]);

            if ($data['program']) {
                $keluarga->kepesertaanProgram()->create([
                    'nama_program' => $data['program'],
                    'status_penerima' => 'penerima',
                    'tahun_menerima' => (int) now()->year,
                    'sumber_instansi' => 'Dinas Sosial Kota Ternate',
                ]);
            }

            if ($data['status'] === 'valid') {
                $keluarga->riwayatVerifikasi()->create([
                    'verifikator_id' => $verifikator->id,
                    'status' => 'valid',
                    'catatan' => 'Data sesuai dengan hasil kunjungan lapangan.',
                    'tanggal_verifikasi' => now()->subDays(3),
                ]);
            }
        }
    }
}
