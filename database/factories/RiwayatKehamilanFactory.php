<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\RiwayatKehamilan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatKehamilan>
 */
class RiwayatKehamilanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'usia_ibu_saat_hamil' => fake()->numberBetween(18, 40),
            'kehamilan_ke' => fake()->numberBetween(1, 4),
            'jumlah_kehamilan' => fake()->numberBetween(1, 4),
            'jumlah_pemeriksaan_kehamilan' => fake()->numberBetween(1, 8),
            'tempat_pemeriksaan' => fake()->randomElement(['Puskesmas', 'Posyandu', 'Klinik Bersalin', 'Rumah Sakit']),
            'kondisi_kehamilan' => fake()->randomElement(['Normal', 'Berisiko']),
            'risiko_kehamilan' => fake()->boolean(20),
            'konsumsi_tablet_tambah_darah' => fake()->randomElement(['tidak_pernah', 'kadang', 'rutin']),
            'status_kek' => fake()->boolean(15),
            'lila' => fake()->randomFloat(2, 18, 30),
            'komplikasi_kehamilan' => fake()->optional()->sentence(),
        ];
    }
}
