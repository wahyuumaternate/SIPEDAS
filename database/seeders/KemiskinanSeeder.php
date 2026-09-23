<?php

namespace Database\Seeders;

use App\Models\AnggotaKeluarga;
use App\Models\DesaKelurahan;
use App\Models\Keluarga;
use App\Models\KondisiEkonomi;
use Illuminate\Database\Seeder;

class KemiskinanSeeder extends Seeder
{
    /**
     * Data pendataan kemiskinan yang berstatus dalam verifikasi (belum divalidasi).
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

            $keluarga = Keluarga::factory()->create([
                'kecamatan_id' => $desa->kecamatan_id,
                'desa_kelurahan_id' => $desa->id,
                'petugas_id' => $petugas->id,
            ]);

            KondisiEkonomi::factory()->create(['keluarga_id' => $keluarga->id]);
            AnggotaKeluarga::factory()->count(min($keluarga->jumlah_anggota_keluarga, 3))->create(['keluarga_id' => $keluarga->id]);
        }
    }
}
