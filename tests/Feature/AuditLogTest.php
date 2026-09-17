<?php

use App\Models\AuditLog;
use App\Models\Keluarga;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userAuditLog(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menolak akses audit log untuk user tanpa hak audit_log.view', function () {
    $petugas = userAuditLog([Permission::KemiskinanView->value]);

    $this->actingAs($petugas)
        ->get(route('audit-log.index'))
        ->assertForbidden();
});

it('menampilkan daftar audit log dan bisa difilter per modul', function () {
    $admin = userAuditLog([Permission::AuditLogView->value]);
    AuditLog::factory()->create(['modul' => 'petugas', 'aksi' => 'create']);
    AuditLog::factory()->create(['modul' => 'role', 'aksi' => 'update']);

    $response = $this->actingAs($admin)->get(route('audit-log.index', ['modul' => 'role']));

    $response->assertOk();
    expect($response->viewData('logs')->total())->toBe(1);
});

it('mencatat penghapusan data keluarga ke audit log', function () {
    $admin = userAuditLog([Permission::KemiskinanDelete->value, Permission::AuditLogView->value]);
    $keluarga = Keluarga::factory()->create(['nama_kepala_keluarga' => 'Siti Rahma']);

    $this->actingAs($admin)->delete(route('kemiskinan.destroy', $keluarga));

    $log = AuditLog::where('modul', 'keluarga')->where('aksi', 'delete')->firstOrFail();
    expect($log->user_id)->toBe($admin->id);
    expect($log->data_lama['nama_kepala_keluarga'])->toBe('Siti Rahma');
    expect($log->data_baru)->toBeNull();
});

it('mencatat perubahan status verifikasi kemiskinan ke audit log', function () {
    $verifikator = userAuditLog([Permission::KemiskinanView->value, Permission::KemiskinanVerify->value, Permission::AuditLogView->value]);
    $keluarga = Keluarga::factory()->create(['status_data' => 'dikirim']);

    $this->actingAs($verifikator)
        ->post(route('kemiskinan.verifikasi.store', $keluarga), ['status' => 'valid']);

    $log = AuditLog::where('modul', 'verifikasi_kemiskinan')->firstOrFail();
    expect($log->data_lama['status_data'])->toBe('dikirim');
    expect($log->data_baru['status_data'])->toBe('valid');
});

it('menampilkan detail satu entri audit log', function () {
    $admin = userAuditLog([Permission::AuditLogView->value]);
    $log = AuditLog::factory()->create(['deskripsi' => 'Contoh aktivitas']);

    $this->actingAs($admin)
        ->get(route('audit-log.show', $log))
        ->assertOk()
        ->assertSee('Contoh aktivitas');
});
