<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengukuran>
 */
class PengukuranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'tanggal_pengukuran' => now(),
            'berat_badan' => fake()->randomFloat(2, 3, 18),
            'panjang_tinggi_badan' => fake()->randomFloat(2, 45, 100),
            'lingkar_kepala' => fake()->randomFloat(2, 33, 48),
            'lingkar_lengan_atas' => fake()->randomFloat(2, 10, 18),
            'petugas_pengukur_id' => User::factory(),
            'tempat_pengukuran' => fake()->randomElement(['Posyandu', 'Puskesmas', 'Rumah']),
            'indikator_antropometri' => 'BB/U, TB/U, BB/TB',
            'hasil_kategori' => fake()->randomElement(['normal', 'stunting_ringan', 'stunting_sedang', 'stunting_berat']),
        ];
    }
}
