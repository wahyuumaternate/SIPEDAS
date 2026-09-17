<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Sanitasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sanitasi>
 */
class SanitasiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'sumber_air_minum' => null,
            'sumber_air_memasak' => null,
            'kepemilikan_jamban' => fake()->boolean(80),
            'jenis_jamban' => null,
            'septic_tank' => fake()->boolean(70),
            'saluran_pembuangan' => fake()->randomElement(['Selokan', 'Sungai', 'Tidak Ada']),
            'pengelolaan_sampah' => null,
            'kondisi_rumah' => fake()->randomElement(['permanen', 'semi_permanen', 'tidak_layak_huni']),
            'kepadatan_hunian' => fake()->randomFloat(2, 2, 15),
        ];
    }
}
