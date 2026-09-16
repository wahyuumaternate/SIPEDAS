<?php

namespace Database\Factories;

use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DesaKelurahan>
 */
class DesaKelurahanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kecamatan_id' => Kecamatan::factory(),
            'kode' => strtoupper(fake()->unique()->bothify('???-####')),
            'nama' => 'Kelurahan '.fake()->unique()->numberBetween(1, 1000000),
            'jenis' => fake()->randomElement(['desa', 'kelurahan']),
            'is_active' => true,
        ];
    }
}
