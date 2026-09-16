<?php

namespace Database\Factories;

use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kecamatan>
 */
class KecamatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->bothify('???-####')),
            'nama' => 'Ternate '.fake()->unique()->numberBetween(1, 1000000),
            'is_active' => true,
        ];
    }
}
