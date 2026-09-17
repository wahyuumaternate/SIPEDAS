<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\OrangTua;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrangTua>
 */
class OrangTuaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'nik_ayah' => fake()->unique()->numerify('################'),
            'nama_ayah_lengkap' => fake()->name('male'),
            'tanggal_lahir_ayah' => fake()->dateTimeBetween('-50 years', '-20 years'),
            'pendidikan_ayah' => null,
            'pekerjaan_ayah' => fake()->jobTitle(),
            'penghasilan_ayah' => fake()->numberBetween(500000, 3000000),
            'nik_ibu' => fake()->unique()->numerify('################'),
            'nama_ibu_lengkap' => fake()->name('female'),
            'tanggal_lahir_ibu' => fake()->dateTimeBetween('-45 years', '-18 years'),
            'pendidikan_ibu' => null,
            'pekerjaan_ibu' => fake()->jobTitle(),
            'penghasilan_ibu' => fake()->numberBetween(0, 2000000),
        ];
    }
}
