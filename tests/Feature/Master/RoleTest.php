<?php

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userRoleTest(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menolak akses role untuk user tanpa hak user.manage', function () {
    $petugas = userRoleTest([Permission::KemiskinanView->value]);

    $this->actingAs($petugas)
        ->get(route('master.role.index'))
        ->assertForbidden();
});

it('membuat role baru beserta hak aksesnya dan mencatat audit log', function () {
    $admin = userRoleTest([Permission::UserManage->value]);

    $this->actingAs($admin)
        ->post(route('master.role.store'), [
            'nama' => 'Verifikator Kecamatan',
            'deskripsi' => 'Memverifikasi data di tingkat kecamatan.',
            'hak_akses' => [Permission::KemiskinanView->value, Permission::KemiskinanVerify->value],
            'is_active' => '1',
        ])
        ->assertRedirect(route('master.role.index'));

    $role = Role::where('slug', 'verifikator-kecamatan')->firstOrFail();
    expect($role->hak_akses)->toBe([Permission::KemiskinanView->value, Permission::KemiskinanVerify->value]);

    expect(AuditLog::where('modul', 'role')->where('aksi', 'create')->count())->toBe(1);
});

it('memperbarui hak akses role dan mencatat data lama serta baru di audit log', function () {
    $admin = userRoleTest([Permission::UserManage->value]);
    $role = Role::factory()->create(['hak_akses' => [Permission::KemiskinanView->value]]);

    $this->actingAs($admin)
        ->put(route('master.role.update', $role), [
            'nama' => $role->nama,
            'slug' => $role->slug,
            'hak_akses' => [Permission::KemiskinanView->value, Permission::KemiskinanCreate->value],
        ])
        ->assertRedirect(route('master.role.index'));

    expect($role->fresh()->hak_akses)->toBe([Permission::KemiskinanView->value, Permission::KemiskinanCreate->value]);

    $log = AuditLog::where('modul', 'role')->where('aksi', 'update')->firstOrFail();
    expect($log->data_lama['hak_akses'])->toBe([Permission::KemiskinanView->value]);
    expect($log->data_baru['hak_akses'])->toBe([Permission::KemiskinanView->value, Permission::KemiskinanCreate->value]);
});

it('menolak penghapusan role super-admin', function () {
    $admin = userRoleTest([Permission::UserManage->value]);
    $superAdmin = Role::factory()->create(['slug' => 'super-admin']);

    $this->actingAs($admin)
        ->delete(route('master.role.destroy', $superAdmin))
        ->assertRedirect();

    $this->assertDatabaseHas('roles', ['id' => $superAdmin->id]);
});

it('menolak penghapusan role yang masih dipakai user', function () {
    $admin = userRoleTest([Permission::UserManage->value]);
    $role = Role::factory()->create();
    User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($admin)
        ->delete(route('master.role.destroy', $role))
        ->assertRedirect();

    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});

it('menghapus role yang tidak dipakai siapapun', function () {
    $admin = userRoleTest([Permission::UserManage->value]);
    $role = Role::factory()->create();

    $this->actingAs($admin)
        ->delete(route('master.role.destroy', $role))
        ->assertRedirect();

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});
