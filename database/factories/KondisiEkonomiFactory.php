<?php

namespace Database\Factories;

use App\Models\Keluarga;
use App\Models\KondisiEkonomi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KondisiEkonomi>
 */
class KondisiEkonomiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'keluarga_id' => Keluarga::factory(),
            'status_pekerjaan_kepala_keluarga_id' => null,
            'pekerjaan_utama' => fake()->jobTitle(),
            'pekerjaan_tambahan' => null,
            'jumlah_anggota_bekerja' => fake()->numberBetween(0, 3),
            'jumlah_anggota_tidak_bekerja' => fake()->numberBetween(0, 3),
            'pendapatan_kepala_keluarga' => fake()->numberBetween(500000, 3000000),
            'pendapatan_pasangan' => 0,
            'pendapatan_anggota_lainnya' => 0,
            'pengeluaran_makanan' => fake()->numberBetween(300000, 1500000),
            'pengeluaran_pendidikan' => fake()->numberBetween(0, 500000),
            'pengeluaran_kesehatan' => fake()->numberBetween(0, 300000),
            'pengeluaran_listrik' => fake()->numberBetween(0, 200000),
            'pengeluaran_air' => fake()->numberBetween(0, 150000),
            'pengeluaran_transportasi' => fake()->numberBetween(0, 300000),
            'pengeluaran_lainnya' => 0,
        ];
    }
}
