<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('menampilkan daftar petugas untuk pengguna yang login', function () {
    $admin = User::factory()->create();
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('master.petugas.index'))
        ->assertOk()
        ->assertViewHas('petugas');
});

it('membuat petugas baru dengan kata sandi terenkripsi', function () {
    $admin = User::factory()->create();
    $role = Role::factory()->create();

    $this->actingAs($admin)
        ->post(route('master.petugas.store'), [
            'role_id' => $role->id,
            'nama' => 'Siti Aminah',
            'username' => 'siti.aminah',
            'email' => 'siti@example.com',
            'status' => 'aktif',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('master.petugas.index'));

    $petugas = User::where('username', 'siti.aminah')->firstOrFail();
    expect(Hash::check('password123', $petugas->password))->toBeTrue();
});

it('menolak email petugas yang duplikat', function () {
    $admin = User::factory()->create();
    $existing = User::factory()->create(['email' => 'dipakai@example.com']);
    $role = Role::factory()->create();

    $this->actingAs($admin)
        ->post(route('master.petugas.store'), [
            'role_id' => $role->id,
            'nama' => 'Duplikat',
            'username' => 'duplikat',
            'email' => 'dipakai@example.com',
            'status' => 'aktif',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('email');

    expect(User::where('email', 'dipakai@example.com')->count())->toBe(1);
});

it('memperbarui data petugas tanpa mengubah kata sandi', function () {
    $admin = User::factory()->create();
    $petugas = User::factory()->create(['nama' => 'Nama Lama']);
    $passwordSebelumnya = $petugas->password;

    $this->actingAs($admin)
        ->put(route('master.petugas.update', $petugas), [
            'role_id' => $petugas->role_id,
            'nama' => 'Nama Baru',
            'username' => $petugas->username,
            'email' => $petugas->email,
            'status' => 'aktif',
        ])
        ->assertRedirect(route('master.petugas.index'));

    $petugas->refresh();
    expect($petugas->nama)->toBe('Nama Baru');
    expect($petugas->password)->toBe($passwordSebelumnya);
});

it('menonaktifkan dan mengaktifkan kembali petugas', function () {
    $admin = User::factory()->create();
    $petugas = User::factory()->create(['status' => 'aktif']);

    $this->actingAs($admin)
        ->patch(route('master.petugas.toggle-status', $petugas))
        ->assertRedirect();

    expect($petugas->fresh()->status)->toBe('nonaktif');

    $this->actingAs($admin)
        ->patch(route('master.petugas.toggle-status', $petugas))
        ->assertRedirect();

    expect($petugas->fresh()->status)->toBe('aktif');
});

it('mereset kata sandi petugas', function () {
    $admin = User::factory()->create();
    $petugas = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('master.petugas.reset-password', $petugas), [
            'password' => 'sandibaru123',
            'password_confirmation' => 'sandibaru123',
        ])
        ->assertRedirect();

    expect(Hash::check('sandibaru123', $petugas->fresh()->password))->toBeTrue();
});

it('menolak reset kata sandi tanpa konfirmasi yang cocok', function () {
    $admin = User::factory()->create();
    $petugas = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('master.petugas.reset-password', $petugas), [
            'password' => 'sandibaru123',
            'password_confirmation' => 'tidak-cocok',
        ])
        ->assertSessionHasErrors('password');
});
