<?php

namespace Database\Factories;

use App\Models\Keluarga;
use App\Models\KepesertaanProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KepesertaanProgram>
 */
class KepesertaanProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'nama_program' => fake()->randomElement(['PKH', 'Sembako', 'PBI JKN', 'BLT']),
            'status_penerima' => fake()->randomElement(['penerima', 'bukan_penerima', 'pernah_menerima']),
            'tahun_menerima' => fake()->optional()->numberBetween(2020, 2026),
            'sumber_instansi' => fake()->optional()->company(),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
