<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\DokumenAnak;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DokumenAnak>
 */
class DokumenAnakFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'nama_file' => fake()->uuid().'.jpg',
            'path_file' => 'dokumen-anak/'.fake()->uuid().'.jpg',
            'jenis_dokumentasi' => fake()->randomElement(['foto_anak', 'foto_pengukuran', 'foto_dokumen_pendukung', 'lainnya']),
            'tanggal_upload' => now(),
            'user_id' => User::factory(),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
