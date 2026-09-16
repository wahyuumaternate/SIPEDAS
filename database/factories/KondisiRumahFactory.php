<?php

namespace Database\Factories;

use App\Models\Keluarga;
use App\Models\KondisiRumah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KondisiRumah>
 */
class KondisiRumahFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'status_kepemilikan' => fake()->randomElement(['milik_sendiri', 'sewa', 'menumpang', 'rumah_dinas', 'lainnya']),
            'kondisi_bangunan' => fake()->randomElement(['permanen', 'semi_permanen', 'tidak_layak_huni']),
            'jenis_atap_id' => null,
            'jenis_dinding_id' => null,
            'jenis_lantai_id' => null,
            'sumber_listrik_id' => null,
            'sumber_air_id' => null,
            'jamban' => fake()->boolean(80),
            'septic_tank' => fake()->boolean(70),
            'drainase' => fake()->boolean(60),
            'pengelolaan_sampah_id' => null,
            'luas_tanah' => fake()->numberBetween(30, 300),
            'luas_bangunan' => fake()->numberBetween(20, 150),
            'jumlah_kamar' => fake()->numberBetween(1, 5),
            'jumlah_penghuni' => fake()->numberBetween(1, 8),
        ];
    }
}
