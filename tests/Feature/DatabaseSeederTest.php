<?php

use App\Models\Anak;
use App\Models\Keluarga;
use App\Models\User;
use App\Models\VerifikasiKemiskinan;
use App\Models\VerifikasiStunting;
use Database\Seeders\DatabaseSeeder;

it('menyemai 4 data kemiskinan dan 4 data stunting dengan 2 terverifikasi dan 2 belum', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Keluarga::count())->toBe(4)
        ->and(Keluarga::where('status_data', 'valid')->count())->toBe(2)
        ->and(Keluarga::where('status_data', 'dalam_verifikasi')->count())->toBe(2)
        ->and(VerifikasiKemiskinan::where('status', 'valid')->count())->toBe(2)
        ->and(Anak::count())->toBe(4)
        ->and(Anak::where('status_data', 'valid')->count())->toBe(2)
        ->and(Anak::where('status_data', 'dalam_verifikasi')->count())->toBe(2)
        ->and(VerifikasiStunting::where('status', 'valid')->count())->toBe(2);
});

it('menghubungkan setiap anak stunting dengan keluarga kemiskinan bernomor kk sama', function () {
    $this->seed(DatabaseSeeder::class);

    Anak::with('orangTua')->get()->each(function (Anak $anak) {
        $keluarga = Keluarga::with('anggotaKeluarga')->where('nomor_kk', $anak->nomor_kk)->sole();

        expect($keluarga->anggotaKeluarga->pluck('nik'))->toContain($anak->nik_anak)
            ->and($anak->orangTua->nama_ibu_lengkap)->toBe($keluarga->anggotaKeluarga->firstWhere('hubungan_keluarga', 'istri')->nama_lengkap);
    });
});

it('tidak menggandakan data saat seeder dijalankan ulang', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Keluarga::count())->toBe(4)
        ->and(Anak::count())->toBe(4)
        ->and(User::where('username', 'superadmin')->count())->toBe(1);
});
