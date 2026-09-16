<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->jobTitle(),
            'slug' => fake()->unique()->slug(2),
            'deskripsi' => fake()->sentence(),
            'hak_akses' => ['keluarga', 'verifikasi_kemiskinan'],
            'is_active' => true,
        ];
    }
}
