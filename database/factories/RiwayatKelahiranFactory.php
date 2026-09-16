<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\RiwayatKelahiran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatKelahiran>
 */
class RiwayatKelahiranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'tanggal_lahir' => fake()->dateTimeBetween('-59 months', '-1 months'),
            'tempat_lahir' => fake()->randomElement(['Rumah Sakit', 'Puskesmas', 'Rumah Bersalin', 'Rumah']),
            'penolong_persalinan' => fake()->randomElement(['dokter', 'bidan', 'perawat', 'dukun', 'lainnya']),
            'cara_persalinan' => fake()->randomElement(['normal', 'caesar', 'lainnya']),
            'berat_badan_lahir' => fake()->randomFloat(2, 2, 4),
            'panjang_badan_lahir' => fake()->randomFloat(2, 40, 52),
            'status_prematur' => fake()->boolean(10),
            'status_bblr' => fake()->boolean(10),
            'imd' => fake()->boolean(60),
            'kondisi_bayi_saat_lahir' => fake()->randomElement(['Sehat', 'Perlu Perawatan Khusus']),
        ];
    }
}
