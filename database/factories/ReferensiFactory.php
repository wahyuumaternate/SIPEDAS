<?php

namespace Database\Factories;

use App\Models\Referensi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referensi>
 */
class ReferensiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kategori' => fake()->word(),
            'kode' => fake()->unique()->word(),
            'nilai' => fake()->words(2, true),
            'urutan' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
