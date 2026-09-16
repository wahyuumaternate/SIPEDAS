<?php

namespace Database\Seeders;

use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    /**
     * Data wilayah administratif Kota Ternate (8 kecamatan) beserta contoh desa/kelurahan
     * secukupnya agar dropdown wilayah pada form pendataan dapat digunakan.
     */
    public function run(): void
    {
        $wilayah = [
            'TTE' => [
                'nama' => 'Ternate Tengah',
                'desa' => ['Kalumpang' => 'kelurahan', 'Gamalama' => 'kelurahan', 'Stadion' => 'kelurahan', 'Muhajirin' => 'kelurahan'],
            ],
            'TSL' => [
                'nama' => 'Ternate Selatan',
                'desa' => ['Bastiong Karance' => 'kelurahan', 'Bastiong Talangame' => 'kelurahan', 'Mangga Dua' => 'kelurahan', 'Jambula' => 'kelurahan'],
            ],
            'TUT' => [
                'nama' => 'Ternate Utara',
                'desa' => ['Sango' => 'kelurahan', 'Tarau' => 'kelurahan', 'Dufa-Dufa' => 'kelurahan', 'Salero' => 'kelurahan'],
            ],
            'TBR' => [
                'nama' => 'Ternate Barat',
                'desa' => ['Loto' => 'kelurahan', 'Sulamadaha' => 'kelurahan', 'Takome' => 'desa', 'Togafo' => 'desa'],
            ],
            'PTE' => [
                'nama' => 'Pulau Ternate',
                'desa' => ['Takome Barat' => 'desa', 'Jambula Pulau Ternate' => 'desa', 'Foramadiahi' => 'desa'],
            ],
            'MTI' => [
                'nama' => 'Moti',
                'desa' => ['Moti Kota' => 'desa', 'Tafamutu' => 'desa', 'Tafraka' => 'desa'],
            ],
            'PBD' => [
                'nama' => 'Pulau Batang Dua',
                'desa' => ['Mayau' => 'desa', 'Bere-Bere' => 'desa'],
            ],
            'HRI' => [
                'nama' => 'Hiri',
                'desa' => ['Dorari Isa' => 'desa', 'Faudu' => 'desa'],
            ],
        ];

        foreach ($wilayah as $kode => $data) {
            $kecamatan = Kecamatan::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $data['nama'], 'is_active' => true],
            );

            $urutan = 1;
            foreach ($data['desa'] as $namaDesa => $jenis) {
                DesaKelurahan::updateOrCreate(
                    ['kode' => $kode.'-'.str_pad((string) $urutan, 2, '0', STR_PAD_LEFT)],
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'nama' => $namaDesa,
                        'jenis' => $jenis,
                        'is_active' => true,
                    ],
                );
                $urutan++;
            }
        }
    }
}
