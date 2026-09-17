<?php

use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\User;

it('menampilkan halaman wilayah untuk pengguna yang login', function () {
    $user = User::factory()->create();
    Kecamatan::factory()->create();

    $this->actingAs($user)
        ->get(route('master.wilayah.index'))
        ->assertOk()
        ->assertViewHas('kecamatans')
        ->assertViewHas('desaKelurahans');
});

it('membuat kecamatan baru', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('master.wilayah.kecamatan.store'), [
            'kode' => 'KEC-01',
            'nama' => 'Ternate Selatan',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('kecamatans', ['kode' => 'KEC-01', 'nama' => 'Ternate Selatan']);
});

it('menolak kode kecamatan yang duplikat', function () {
    $user = User::factory()->create();
    Kecamatan::factory()->create(['kode' => 'KEC-01']);

    $this->actingAs($user)
        ->post(route('master.wilayah.kecamatan.store'), [
            'kode' => 'KEC-01',
            'nama' => 'Ternate Utara',
        ])
        ->assertSessionHasErrors('kode');

    expect(Kecamatan::where('kode', 'KEC-01')->count())->toBe(1);
});

it('memperbarui data kecamatan', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();

    $this->actingAs($user)
        ->put(route('master.wilayah.kecamatan.update', $kecamatan), [
            'kode' => $kecamatan->kode,
            'nama' => 'Kecamatan Baru',
            'is_active' => '0',
        ])
        ->assertRedirect();

    expect($kecamatan->fresh())
        ->nama->toBe('Kecamatan Baru')
        ->is_active->toBeFalse();
});

it('menolak hapus kecamatan yang masih memiliki data pendataan keluarga', function () {
    $user = User::factory()->create();
    $keluarga = Keluarga::factory()->create();

    $this->actingAs($user)
        ->delete(route('master.wilayah.kecamatan.destroy', $keluarga->kecamatan))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('kecamatans', ['id' => $keluarga->kecamatan_id]);
});

it('menghapus kecamatan yang tidak memiliki data terkait', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();

    $this->actingAs($user)
        ->delete(route('master.wilayah.kecamatan.destroy', $kecamatan))
        ->assertRedirect();

    $this->assertDatabaseMissing('kecamatans', ['id' => $kecamatan->id]);
});

it('membuat desa/kelurahan baru di bawah kecamatan tertentu', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();

    $this->actingAs($user)
        ->post(route('master.wilayah.desa-kelurahan.store'), [
            'kecamatan_id' => $kecamatan->id,
            'kode' => 'DESA-01',
            'nama' => 'Kelurahan Maliaro',
            'jenis' => 'kelurahan',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('desa_kelurahans', [
        'kecamatan_id' => $kecamatan->id,
        'kode' => 'DESA-01',
    ]);
});

it('menolak desa/kelurahan tanpa kecamatan yang valid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('master.wilayah.desa-kelurahan.store'), [
            'kecamatan_id' => 9999,
            'kode' => 'DESA-02',
            'nama' => 'Kelurahan Fiktif',
            'jenis' => 'kelurahan',
        ])
        ->assertSessionHasErrors('kecamatan_id');
});
