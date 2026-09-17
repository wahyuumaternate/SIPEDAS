<?php

namespace Database\Factories;

use App\Models\AnggotaKeluarga;
use App\Models\Keluarga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnggotaKeluarga>
 */
class AnggotaKeluargaFactory extends Factory
{
    public function definition(): array
    {
        $tanggalLahir = fake()->dateTimeBetween('-70 years', '-1 years');

        return [
            'keluarga_id' => Keluarga::factory(),
            'nik' => fake()->unique()->numerify('################'),
            'nama_lengkap' => fake()->name(),
            'jenis_kelamin' => fake()->randomElement(['laki-laki', 'perempuan']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => $tanggalLahir,
            'usia' => now()->diffInYears($tanggalLahir),
            'hubungan_keluarga' => fake()->randomElement(['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Lainnya']),
            'status_perkawinan' => null,
            'pendidikan_terakhir' => null,
            'status_pekerjaan' => null,
            'disabilitas' => false,
            'jenis_disabilitas' => null,
            'penyakit_kronis' => false,
            'jenis_penyakit_kronis' => null,
        ];
    }
}
