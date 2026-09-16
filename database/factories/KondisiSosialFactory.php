<?php

namespace Database\Factories;

use App\Models\Keluarga;
use App\Models\KondisiSosial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KondisiSosial>
 */
class KondisiSosialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'jumlah_anak_usia_sekolah' => fake()->numberBetween(0, 4),
            'jumlah_anak_bersekolah' => fake()->numberBetween(0, 4),
            'jumlah_anak_putus_sekolah' => 0,
            'jumlah_lansia' => fake()->numberBetween(0, 2),
            'jumlah_penyandang_disabilitas' => 0,
            'jumlah_anggota_sakit' => 0,
            'akses_fasilitas_kesehatan' => fake()->boolean(70),
            'jarak_fasilitas_kesehatan_km' => fake()->randomFloat(2, 0.1, 10),
            'akses_pendidikan' => fake()->boolean(70),
            'jarak_sekolah_km' => fake()->randomFloat(2, 0.1, 10),
            'kepemilikan_dokumen_kependudukan' => fake()->boolean(80),
        ];
    }
}
