<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\AsiMpasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsiMpasi>
 */
class AsiMpasiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'imd' => fake()->boolean(60),
            'asi_eksklusif' => fake()->boolean(50),
            'lama_pemberian_asi_bulan' => fake()->numberBetween(0, 24),
            'kendala_pemberian_asi' => fake()->optional()->sentence(),
            'usia_mulai_mpasi_bulan' => fake()->numberBetween(4, 8),
            'frekuensi_makan' => fake()->randomElement(['1x sehari', '2x sehari', '3x sehari']),
            'jenis_makanan' => fake()->randomElement(['Bubur', 'Nasi Tim', 'Makanan Keluarga']),
            'sumber_protein' => fake()->randomElement(['Ikan', 'Telur', 'Daging', 'Tahu/Tempe']),
            'konsumsi_sayur' => fake()->boolean(70),
            'konsumsi_buah' => fake()->boolean(70),
            'keragaman_makanan' => fake()->randomElement(['Rendah', 'Sedang', 'Tinggi']),
        ];
    }
}
