<?php

namespace Database\Factories;

use App\Models\DokumenKeluarga;
use App\Models\Keluarga;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DokumenKeluarga>
 */
class DokumenKeluargaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'nama_file' => fake()->uuid().'.jpg',
            'path_file' => 'dokumen-keluarga/'.fake()->uuid().'.jpg',
            'jenis_dokumentasi' => fake()->randomElement(['foto_rumah', 'foto_lingkungan', 'foto_dokumen_pendukung', 'lainnya']),
            'tanggal_upload' => now(),
            'user_id' => User::factory(),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
