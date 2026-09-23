<?php

use App\Models\Keluarga;
use App\Models\User;

it('menampilkan antrian verifikasi hanya untuk data yang belum final', function () {
    $verifikator = User::factory()->create();
    Keluarga::factory()->create(['status_data' => 'dalam_verifikasi']);
    Keluarga::factory()->create(['status_data' => 'valid']);

    $response = $this->actingAs($verifikator)->get(route('kemiskinan.verifikasi.index'));

    $response->assertOk();
    expect($response->viewData('antrian')->total())->toBe(1);
});

it('mengubah status dalam verifikasi menjadi valid dan mencatat riwayat verifikasi', function () {
    $verifikator = User::factory()->create();
    $keluarga = Keluarga::factory()->create(['status_data' => 'dalam_verifikasi']);

    $this->actingAs($verifikator)
        ->post(route('kemiskinan.verifikasi.store', $keluarga), ['status' => 'valid'])
        ->assertRedirect(route('kemiskinan.show', $keluarga));

    $keluarga->refresh();

    expect($keluarga->status_data)->toBe('valid');
    expect($keluarga->riwayatVerifikasi()->where('status', 'valid')->count())->toBe(1);
    expect($keluarga->riwayatVerifikasi()->first()->verifikator_id)->toBe($verifikator->id);
});

it('mewajibkan catatan ketika status diubah menjadi perlu perbaikan', function () {
    $verifikator = User::factory()->create();
    $keluarga = Keluarga::factory()->create(['status_data' => 'dalam_verifikasi']);

    $this->actingAs($verifikator)
        ->post(route('kemiskinan.verifikasi.store', $keluarga), ['status' => 'perlu_perbaikan'])
        ->assertSessionHasErrors(['catatan']);

    expect($keluarga->fresh()->status_data)->toBe('dalam_verifikasi');
});

it('menolak transisi status yang tidak diizinkan', function () {
    $verifikator = User::factory()->create();
    $keluarga = Keluarga::factory()->create(['status_data' => 'valid']);

    $this->actingAs($verifikator)
        ->post(route('kemiskinan.verifikasi.store', $keluarga), ['status' => 'valid'])
        ->assertSessionHas('error');

    expect($keluarga->fresh()->status_data)->toBe('valid');
});
