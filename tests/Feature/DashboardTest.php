<?php

use App\Models\Anak;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\KepesertaanProgram;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userDashboard(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menampilkan dashboard tanpa data dummy ketika database kosong', function () {
    $user = userDashboard([Permission::KemiskinanView->value, Permission::StuntingView->value]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    expect($response->viewData('kemiskinanStats')['total_keluarga'])->toBe(0);
    expect($response->viewData('stuntingStats')['total_anak'])->toBe(0);
    $response->assertDontSee('Abdul Rasyid');
    $response->assertDontSee('Fatimah Az-Zahra');
});

it('menyembunyikan tab kemiskinan untuk user tanpa hak lihat kemiskinan', function () {
    $user = userDashboard([Permission::StuntingView->value]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    expect($response->viewData('bisaLihatKemiskinan'))->toBeFalse();
    $response->assertDontSee('Keluarga Berdasarkan Kecamatan');
    $response->assertSee('Anak Berdasarkan Kecamatan');
});

it('menampilkan pesan tidak ada akses jika user tidak punya hak lihat sama sekali', function () {
    $user = userDashboard([]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('belum memiliki hak akses');
});

it('menghitung statistik kemiskinan sesuai data asli di database', function () {
    $user = userDashboard([Permission::KemiskinanView->value, Permission::StuntingView->value]);
    Keluarga::factory()->create(['status_data' => 'valid', 'jumlah_anggota_keluarga' => 3]);
    Keluarga::factory()->dikirim()->create(['jumlah_anggota_keluarga' => 4]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $stats = $response->viewData('kemiskinanStats');
    expect($stats['total_keluarga'])->toBe(2);
    expect($stats['total_anggota'])->toBe(7);
    expect($stats['valid'])->toBe(1);
    expect($stats['belum_diverifikasi'])->toBe(1);
});

it('menerapkan filter kecamatan pada statistik dashboard', function () {
    $user = userDashboard([Permission::KemiskinanView->value, Permission::StuntingView->value]);
    $kecamatanA = Kecamatan::factory()->create();
    $kecamatanB = Kecamatan::factory()->create();
    Keluarga::factory()->create(['kecamatan_id' => $kecamatanA->id]);
    Keluarga::factory()->create(['kecamatan_id' => $kecamatanB->id]);

    $response = $this->actingAs($user)->get(route('dashboard', ['kecamatan_id' => $kecamatanA->id]));

    expect($response->viewData('kemiskinanStats')['total_keluarga'])->toBe(1);
});

it('merekap kepesertaan program hanya untuk status penerima di dashboard', function () {
    $user = userDashboard([Permission::KemiskinanView->value]);
    $keluarga = Keluarga::factory()->create();
    KepesertaanProgram::factory()->create(['keluarga_id' => $keluarga->id, 'nama_program' => 'PKH', 'status_penerima' => 'penerima']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('PKH');
});

it('merekap jenis kelamin dan kategori stunting di dashboard', function () {
    $user = userDashboard([Permission::StuntingView->value]);
    Anak::factory()->create(['jenis_kelamin' => 'laki-laki']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    expect($response->viewData('rekapJenisKelamin')->pluck('label'))->toContain('laki-laki');
});
