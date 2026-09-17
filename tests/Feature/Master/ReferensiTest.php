<?php

use App\Models\Referensi;
use App\Models\User;

it('menampilkan daftar referensi dengan filter kategori', function () {
    $user = User::factory()->create();
    Referensi::factory()->create(['kategori' => 'jenis_atap']);
    Referensi::factory()->create(['kategori' => 'jenis_dinding']);

    $response = $this->actingAs($user)
        ->get(route('master.referensi.index', ['kategori' => 'jenis_atap']));

    $response->assertOk();
    expect($response->viewData('referensis')->total())->toBe(1);
});

it('membuat referensi baru', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('master.referensi.store'), [
            'kategori' => 'Jenis Atap',
            'kode' => 'genteng',
            'nilai' => 'Genteng',
            'urutan' => 1,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('referensis', [
        'kategori' => 'jenis_atap',
        'kode' => 'genteng',
        'nilai' => 'Genteng',
    ]);
});

it('mengizinkan kode yang sama pada kategori berbeda tapi menolak duplikat dalam kategori sama', function () {
    $user = User::factory()->create();
    Referensi::factory()->create(['kategori' => 'jenis_atap', 'kode' => 'genteng']);

    $this->actingAs($user)
        ->post(route('master.referensi.store'), [
            'kategori' => 'jenis_dinding',
            'kode' => 'genteng',
            'nilai' => 'Genteng',
        ])
        ->assertSessionDoesntHaveErrors('kode');

    $this->actingAs($user)
        ->post(route('master.referensi.store'), [
            'kategori' => 'jenis_atap',
            'kode' => 'genteng',
            'nilai' => 'Genteng Duplikat',
        ])
        ->assertSessionHasErrors('kode');
});

it('memperbarui data referensi', function () {
    $user = User::factory()->create();
    $referensi = Referensi::factory()->create(['nilai' => 'Lama']);

    $this->actingAs($user)
        ->put(route('master.referensi.update', $referensi), [
            'kategori' => $referensi->kategori,
            'kode' => $referensi->kode,
            'nilai' => 'Baru',
            'urutan' => 5,
            'is_active' => '0',
        ])
        ->assertRedirect();

    expect($referensi->fresh())
        ->nilai->toBe('Baru')
        ->is_active->toBeFalse();
});

it('menghapus referensi', function () {
    $user = User::factory()->create();
    $referensi = Referensi::factory()->create();

    $this->actingAs($user)
        ->delete(route('master.referensi.destroy', $referensi))
        ->assertRedirect();

    $this->assertDatabaseMissing('referensis', ['id' => $referensi->id]);
});
