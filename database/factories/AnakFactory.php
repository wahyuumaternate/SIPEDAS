<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anak>
 */
class AnakFactory extends Factory
{
    public function definition(): array
    {
        $tanggalLahir = fake()->dateTimeBetween('-59 months', '-1 months');

        return [
            'kode_pendataan' => 'AN-'.now()->format('Ymd').'-'.fake()->unique()->numerify('#####'),
            'nik_anak' => fake()->unique()->numerify('################'),
            'nomor_kk' => fake()->unique()->numerify('################'),
            'nama_anak' => fake()->name(),
            'jenis_kelamin' => fake()->randomElement(['laki-laki', 'perempuan']),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => $tanggalLahir,
            'usia_bulan' => (int) Carbon::parse($tanggalLahir)->diffInMonths(now()),
            'nama_ayah' => fake()->name('male'),
            'nama_ibu' => fake()->name('female'),
            'nomor_hp_orang_tua' => fake()->numerify('08##########'),
            'alamat' => fake()->streetAddress(),
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
}
