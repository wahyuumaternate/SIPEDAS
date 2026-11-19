<?php

use App\Models\Anak;
use App\Models\AnggotaKeluarga;
use App\Models\Keluarga;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userCariKeluarga(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('mengembalikan data ayah, ibu, dan anak balita dari keluarga dengan nomor kk tersebut', function () {
    $user = userCariKeluarga([Permission::StuntingView->value, Permission::StuntingCreate->value]);
    $keluarga = Keluarga::factory()->create(['nomor_kk' => '8271010101010001', 'alamat' => 'Jl. Pahlawan']);
    AnggotaKeluarga::factory()->for($keluarga)->create([
        'nama_lengkap' => 'Ahmad', 'jenis_kelamin' => 'laki-laki', 'hubungan_keluarga' => 'kepala_keluarga',
        'pendidikan_terakhir' => 'sma', 'status_pekerjaan' => 'nelayan',
    ]);
    AnggotaKeluarga::factory()->for($keluarga)->create([
        'nama_lengkap' => 'Siti', 'jenis_kelamin' => 'perempuan', 'hubungan_keluarga' => 'istri',
    ]);
    AnggotaKeluarga::factory()->for($keluarga)->create([
        'nama_lengkap' => 'Balita', 'hubungan_keluarga' => 'anak', 'tanggal_lahir' => now()->subMonths(14),
    ]);
    AnggotaKeluarga::factory()->for($keluarga)->create([
        'nama_lengkap' => 'Kakak Remaja', 'hubungan_keluarga' => 'anak', 'tanggal_lahir' => now()->subYears(12),
    ]);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => '8271010101010001']))
        ->assertOk()
        ->assertJsonPath('ditemukan', true)
        ->assertJsonPath('keluarga.alamat', 'Jl. Pahlawan')
        ->assertJsonPath('ayah.nama', 'Ahmad')
        ->assertJsonPath('ayah.pendidikan', 'sma')
        ->assertJsonPath('ayah.pekerjaan', 'Nelayan')
        ->assertJsonPath('ibu.nama', 'Siti')
        ->assertJsonCount(1, 'anak')
        ->assertJsonPath('anak.0.nama', 'Balita');
});

it('menyatakan tidak ditemukan untuk nomor kk yang belum terdata atau tidak valid', function (string $nomorKk) {
    $user = userCariKeluarga([Permission::StuntingCreate->value]);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => $nomorKk]))
        ->assertOk()
        ->assertExactJson(['ditemukan' => false]);
})->with(['belum terdata' => '9999999999999999', 'format salah' => '12345']);

it('menolak pencarian keluarga tanpa hak input atau ubah stunting', function () {
    $user = userCariKeluarga([Permission::StuntingView->value]);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => '8271010101010001']))
        ->assertForbidden();
});

it('mengutamakan hubungan istri sebagai ibu walau jenis kelaminnya salah input', function () {
    $user = userCariKeluarga([Permission::StuntingCreate->value]);
    $keluarga = Keluarga::factory()->create(['nomor_kk' => '8271010101010002']);
    AnggotaKeluarga::factory()->for($keluarga)->create(['nama_lengkap' => 'Leo', 'jenis_kelamin' => 'laki-laki', 'hubungan_keluarga' => 'kepala_keluarga']);
    AnggotaKeluarga::factory()->for($keluarga)->create(['nama_lengkap' => 'Karma', 'jenis_kelamin' => 'laki-laki', 'hubungan_keluarga' => 'istri']);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => '8271010101010002']))
        ->assertJsonPath('ayah.nama', 'Leo')
        ->assertJsonPath('ibu.nama', 'Karma');
});

it('menjadikan kepala keluarga perempuan sebagai ibu dan mengosongkan ayah bila tidak ada suami', function () {
    $user = userCariKeluarga([Permission::StuntingCreate->value]);
    $keluarga = Keluarga::factory()->create(['nomor_kk' => '8271010101010003']);
    AnggotaKeluarga::factory()->for($keluarga)->create(['nama_lengkap' => 'Hana', 'jenis_kelamin' => 'perempuan', 'hubungan_keluarga' => 'kepala_keluarga']);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => '8271010101010003']))
        ->assertJsonPath('ibu.nama', 'Hana')
        ->assertJsonPath('ayah', null);
});

it('tidak menampilkan balita yang sudah terdata di modul stunting', function () {
    $user = userCariKeluarga([Permission::StuntingCreate->value]);
    $keluarga = Keluarga::factory()->create(['nomor_kk' => '8271010101010004']);
    $tanggalLahir = now()->subMonths(10)->startOfDay();
    AnggotaKeluarga::factory()->for($keluarga)->create(['nik' => '8271010000000001', 'nama_lengkap' => 'Sudah Terdata NIK', 'hubungan_keluarga' => 'anak', 'tanggal_lahir' => now()->subMonths(20)]);
    AnggotaKeluarga::factory()->for($keluarga)->create(['nik' => null, 'nama_lengkap' => 'Tanpa NIK', 'hubungan_keluarga' => 'anak', 'tanggal_lahir' => $tanggalLahir]);
    AnggotaKeluarga::factory()->for($keluarga)->create(['nik' => '8271010000000003', 'nama_lengkap' => 'Belum Terdata', 'hubungan_keluarga' => 'anak', 'tanggal_lahir' => now()->subMonths(30)]);

    Anak::factory()->create(['nomor_kk' => '8271010101010004', 'nik_anak' => '8271010000000001']);
    Anak::factory()->create(['nomor_kk' => '8271010101010004', 'nama_anak' => 'tanpa nik', 'tanggal_lahir' => $tanggalLahir]);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => '8271010101010004']))
        ->assertJsonCount(1, 'anak')
        ->assertJsonPath('anak.0.nama', 'Belum Terdata')
        ->assertJsonPath('jumlah_balita_terdata', 2);
});

it('tetap menampilkan anak yang sedang diedit', function () {
    $user = userCariKeluarga([Permission::StuntingEdit->value]);
    $keluarga = Keluarga::factory()->create(['nomor_kk' => '8271010101010005']);
    AnggotaKeluarga::factory()->for($keluarga)->create(['nik' => '8271010000000005', 'nama_lengkap' => 'Sedang Diedit', 'hubungan_keluarga' => 'anak', 'tanggal_lahir' => now()->subMonths(12)]);
    $anak = Anak::factory()->create(['nomor_kk' => '8271010101010005', 'nik_anak' => '8271010000000005']);

    $this->actingAs($user)
        ->getJson(route('stunting.cari-keluarga', ['nomor_kk' => '8271010101010005', 'kecuali_anak_id' => $anak->id]))
        ->assertJsonCount(1, 'anak')
        ->assertJsonPath('anak.0.nama', 'Sedang Diedit');
});
