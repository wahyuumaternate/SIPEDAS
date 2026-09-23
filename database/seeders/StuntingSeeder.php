<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Pengukuran;
use Illuminate\Database\Seeder;

class StuntingSeeder extends Seeder
{
    /**
     * Data pendataan stunting yang berstatus dalam verifikasi (belum divalidasi).
     */
    public function run(int $jumlah = 20): void
    {
        $petugas = PetugasSeeder::petugas();
        $desas = DesaKelurahan::all();

        if ($desas->isEmpty()) {
            $this->command?->error('Data wilayah belum tersedia. Jalankan WilayahSeeder.');

            return;
        }

        for ($i = 0; $i < $jumlah; $i++) {
            $desa = $desas->random();

            $anak = Anak::factory()->create([
                'kecamatan_id' => $desa->kecamatan_id,
                'desa_kelurahan_id' => $desa->id,
                'petugas_id' => $petugas->id,
            ]);

            Pengukuran::factory()->create([
                'anak_id' => $anak->id,
                'petugas_pengukur_id' => $petugas->id,
                'usia_saat_pengukuran_bulan' => $anak->usia_bulan,
            ]);
        }
    }
}
