<?php

namespace Database\Factories;

use App\Models\DraftFormulir;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DraftFormulir>
 */
class DraftFormulirFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kunci' => 'kemiskinan.create',
            'isian' => [['nomor_kk', '8271010101240009'], ['alamat', 'Jl. Contoh']],
            'disimpan_pada' => now(),
        ];
    }
}
