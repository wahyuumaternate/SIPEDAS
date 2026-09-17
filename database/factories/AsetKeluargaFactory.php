<?php

namespace Database\Factories;

use App\Models\AsetKeluarga;
use App\Models\Keluarga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsetKeluarga>
 */
class AsetKeluargaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'jenis_aset' => fake()->randomElement(array_keys(config('referensi.jenis_aset'))),
            'jumlah' => fake()->numberBetween(1, 3),
            'status_kepemilikan' => fake()->randomElement(['milik_sendiri', 'sewa', 'lainnya']),
            'perkiraan_nilai' => fake()->numberBetween(500000, 50000000),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
