<?php

use App\Models\Keluarga;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userDenganPermission(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menolak petugas pendata mengakses master data wilayah', function () {
    $petugas = userDenganPermission([
        Permission::KemiskinanView->value,
        Permission::KemiskinanCreate->value,
        Permission::KemiskinanEdit->value,
    ]);

    $this->actingAs($petugas)
        ->get(route('master.wilayah.index'))
        ->assertForbidden();
});

it('menolak petugas pendata mengelola akun petugas lain', function () {
    $petugas = userDenganPermission([Permission::KemiskinanView->value]);

    $this->actingAs($petugas)
        ->get(route('master.petugas.index'))
        ->assertForbidden();
});

it('menolak petugas pendata menghapus data keluarga', function () {
    $petugas = userDenganPermission([
        Permission::KemiskinanView->value,
        Permission::KemiskinanCreate->value,
        Permission::KemiskinanEdit->value,
    ]);
    $keluarga = Keluarga::factory()->create();

    $this->actingAs($petugas)
        ->delete(route('kemiskinan.destroy', $keluarga))
        ->assertForbidden();
});

it('menolak petugas pendata memverifikasi data keluarga', function () {
    $petugas = userDenganPermission([
        Permission::KemiskinanView->value,
        Permission::KemiskinanCreate->value,
        Permission::KemiskinanEdit->value,
    ]);
    $keluarga = Keluarga::factory()->create(['status_data' => 'dikirim']);

    $this->actingAs($petugas)
        ->post(route('kemiskinan.verifikasi.store', $keluarga), ['status' => 'valid'])
        ->assertForbidden();
});

it('mengizinkan verifikator memverifikasi tapi menolaknya membuat data baru', function () {
    $verifikator = userDenganPermission([
        Permission::KemiskinanView->value,
        Permission::KemiskinanVerify->value,
    ]);
    $keluarga = Keluarga::factory()->create(['status_data' => 'dikirim']);

    $this->actingAs($verifikator)
        ->post(route('kemiskinan.verifikasi.store', $keluarga), ['status' => 'valid'])
        ->assertRedirect();

    $this->actingAs($verifikator)
        ->get(route('kemiskinan.create'))
        ->assertForbidden();
});

it('menolak viewer membuat, mengubah, atau menghapus data', function () {
    $viewer = userDenganPermission([Permission::KemiskinanView->value, Permission::StuntingView->value]);
    $keluarga = Keluarga::factory()->create();

    $this->actingAs($viewer)->get(route('kemiskinan.create'))->assertForbidden();
    $this->actingAs($viewer)->get(route('kemiskinan.edit', $keluarga))->assertForbidden();
    $this->actingAs($viewer)->delete(route('kemiskinan.destroy', $keluarga))->assertForbidden();

    $this->actingAs($viewer)->get(route('kemiskinan.index'))->assertOk();
});

it('mengizinkan super admin mengakses seluruh fitur walau role-nya tidak diberi permission eksplisit', function () {
    $role = Role::factory()->create(['slug' => 'super-admin', 'hak_akses' => []]);
    $superAdmin = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($superAdmin)->get(route('master.wilayah.index'))->assertOk();
    $this->actingAs($superAdmin)->get(route('master.petugas.index'))->assertOk();
    $this->actingAs($superAdmin)->get(route('kemiskinan.create'))->assertOk();
});

it('menolak login untuk user berstatus nonaktif', function () {
    $user = User::factory()->create(['status' => 'nonaktif', 'password' => bcrypt('password')]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
