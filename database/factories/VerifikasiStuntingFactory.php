<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\User;
use App\Models\VerifikasiStunting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerifikasiStunting>
 */
class VerifikasiStuntingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'verifikator_id' => User::factory(),
            'status' => 'dalam_verifikasi',
            'catatan' => fake()->optional()->sentence(),
            'tanggal_verifikasi' => now(),
        ];
    }
}
