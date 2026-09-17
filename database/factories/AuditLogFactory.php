<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'modul' => fake()->randomElement(['keluarga', 'anak', 'petugas', 'role']),
            'aksi' => fake()->randomElement(['create', 'update', 'delete', 'verify']),
            'deskripsi' => fake()->sentence(),
            'data_lama' => null,
            'data_baru' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
