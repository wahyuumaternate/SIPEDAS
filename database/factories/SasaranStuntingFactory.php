<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use App\Models\SasaranStunting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SasaranStunting>
 */
class SasaranStuntingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kecamatan_id' => Kecamatan::factory(),
            'tahun' => (int) now()->year,
            'penduduk' => fake()->numberBetween(2000, 80000),
            'rumah_tangga' => fake()->numberBetween(500, 25000),
            'anak_0_5' => fake()->numberBetween(5, 300),
            'anak_6_23' => fake()->numberBetween(30, 800),
            'anak_24_59' => fake()->numberBetween(80, 1500),
            'remaja_putri' => fake()->numberBetween(80, 2000),
            'calon_pengantin' => fake()->numberBetween(3, 200),
            'ibu_hamil' => fake()->numberBetween(20, 900),
            'ibu_nifas' => fake()->numberBetween(10, 700),
            'remaja_putri_ttd' => fake()->numberBetween(0, 80),
            'remaja_putri_skrining_anemia' => fake()->numberBetween(0, 80),
        ];
    }
}
