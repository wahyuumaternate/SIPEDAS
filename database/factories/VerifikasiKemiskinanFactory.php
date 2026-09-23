<?php

namespace Database\Factories;

use App\Models\Keluarga;
use App\Models\User;
use App\Models\VerifikasiKemiskinan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerifikasiKemiskinan>
 */
class VerifikasiKemiskinanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'verifikator_id' => User::factory(),
            'status' => 'dalam_verifikasi',
            'catatan' => fake()->optional()->sentence(),
            'tanggal_verifikasi' => now(),
        ];
    }
}
