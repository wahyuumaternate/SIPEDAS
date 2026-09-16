<?php

namespace Database\Factories;

use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Keluarga>
 */
class KeluargaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_pendataan' => 'KK-'.now()->format('Ymd').'-'.fake()->unique()->numerify('#####'),
            'nomor_kk' => fake()->unique()->numerify('################'),
            'nik_kepala_keluarga' => fake()->unique()->numerify('################'),
            'nama_kepala_keluarga' => fake()->name('male'),
            'nomor_hp' => fake()->numerify('08##########'),
            'jumlah_anggota_keluarga' => fake()->numberBetween(1, 8),
            'status_perkawinan_id' => null,
            'alamat' => fake()->streetAddress(),
            'rt' => fake()->numerify('##'),
            'rw' => fake()->numerify('##'),
            'kecamatan_id' => Kecamatan::factory(),
            'desa_kelurahan_id' => DesaKelurahan::factory(),
            'petugas_id' => User::factory(),
            'tanggal_input' => now(),
            'status_data' => 'draft',
            'tanggal_pendataan' => null,
        ];
    }

    public function dikirim(): static
    {
        return $this->state(fn () => [
            'status_data' => 'dikirim',
            'tanggal_pendataan' => now(),
        ]);
    }

    public function valid(): static
    {
        return $this->state(fn () => [
            'status_data' => 'valid',
            'tanggal_pendataan' => now(),
        ]);
    }
}
