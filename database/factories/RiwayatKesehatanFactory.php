<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\RiwayatKesehatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatKesehatan>
 */
class RiwayatKesehatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'status_imunisasi' => fake()->randomElement(['lengkap', 'tidak_lengkap', 'tidak_imunisasi']),
            'imunisasi_terakhir' => fake()->optional()->word(),
            'riwayat_penyakit' => fake()->optional()->sentence(),
            'riwayat_diare' => fake()->boolean(20),
            'riwayat_ispa' => fake()->boolean(20),
            'penyakit_kronis' => fake()->boolean(5),
            'jenis_penyakit_kronis' => null,
            'penyakit_bawaan' => fake()->boolean(5),
            'jenis_penyakit_bawaan' => null,
            'riwayat_rawat_inap' => fake()->boolean(10),
            'akses_pelayanan_kesehatan' => fake()->boolean(80),
        ];
    }
}
