<?php

use App\Models\Anak;
use App\Models\Keluarga;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userVerifikasi(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menolak akses untuk user tanpa hak verifikasi kemiskinan maupun stunting', function () {
    $petugas = userVerifikasi([Permission::KemiskinanCreate->value]);

    $this->actingAs($petugas)
        ->get(route('verifikasi-validasi.index'))
        ->assertForbidden();
});

it('menampilkan kedua antrian untuk user dengan hak verifikasi kemiskinan dan stunting', function () {
    $verifikator = userVerifikasi([Permission::KemiskinanVerify->value, Permission::StuntingVerify->value]);
    Keluarga::factory()->dalamVerifikasi()->create();
    Anak::factory()->dalamVerifikasi()->create();

    $response = $this->actingAs($verifikator)->get(route('verifikasi-validasi.index'));

    $response->assertOk();
    expect($response->viewData('lihatKemiskinan'))->toBeTrue();
    expect($response->viewData('lihatStunting'))->toBeTrue();
    expect($response->viewData('antrianKemiskinan')->total())->toBe(1);
    expect($response->viewData('antrianStunting')->total())->toBe(1);
});

it('hanya menampilkan antrian kemiskinan untuk user yang cuma punya hak verifikasi kemiskinan', function () {
    $verifikator = userVerifikasi([Permission::KemiskinanVerify->value]);
    Keluarga::factory()->dalamVerifikasi()->create();
    Anak::factory()->dalamVerifikasi()->create();

    $response = $this->actingAs($verifikator)->get(route('verifikasi-validasi.index'));

    $response->assertOk();
    expect($response->viewData('lihatKemiskinan'))->toBeTrue();
    expect($response->viewData('lihatStunting'))->toBeFalse();
    expect($response->viewData('antrianStunting'))->toBeNull();
});

it('menerapkan filter status dan kecamatan pada kedua antrian', function () {
    $verifikator = userVerifikasi([Permission::KemiskinanVerify->value, Permission::StuntingVerify->value]);
    $keluargaDikirim = Keluarga::factory()->dalamVerifikasi()->create();
    Keluarga::factory()->create(['status_data' => 'valid']);

    $response = $this->actingAs($verifikator)
        ->get(route('verifikasi-validasi.index', ['status_data' => 'dalam_verifikasi']));

    expect($response->viewData('antrianKemiskinan')->total())->toBe(1);
    expect($response->viewData('antrianKemiskinan')->first()->id)->toBe($keluargaDikirim->id);
});
