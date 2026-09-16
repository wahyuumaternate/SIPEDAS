<?php

use App\Models\Anak;
use App\Models\User;

it('menampilkan antrian verifikasi hanya untuk data yang belum final', function () {
    $verifikator = User::factory()->create();
    Anak::factory()->create(['status_data' => 'dikirim']);
    Anak::factory()->create(['status_data' => 'valid']);

    $response = $this->actingAs($verifikator)->get(route('stunting.verifikasi.index'));

    $response->assertOk();
    expect($response->viewData('antrian')->total())->toBe(1);
});

it('mengubah status dikirim menjadi valid dan mencatat riwayat verifikasi', function () {
    $verifikator = User::factory()->create();
    $anak = Anak::factory()->create(['status_data' => 'dikirim']);

    $this->actingAs($verifikator)
        ->post(route('stunting.verifikasi.store', $anak), ['status' => 'valid'])
        ->assertRedirect(route('stunting.show', $anak));

    $anak->refresh();

    expect($anak->status_data)->toBe('valid');
    expect($anak->riwayatVerifikasi()->where('status', 'valid')->count())->toBe(1);
    expect($anak->riwayatVerifikasi()->first()->verifikator_id)->toBe($verifikator->id);
});

it('mewajibkan catatan ketika status diubah menjadi perlu perbaikan', function () {
    $verifikator = User::factory()->create();
    $anak = Anak::factory()->create(['status_data' => 'dalam_verifikasi']);

    $this->actingAs($verifikator)
        ->post(route('stunting.verifikasi.store', $anak), ['status' => 'perlu_perbaikan'])
        ->assertSessionHasErrors(['catatan']);

    expect($anak->fresh()->status_data)->toBe('dalam_verifikasi');
});

it('menolak transisi status yang tidak diizinkan', function () {
    $verifikator = User::factory()->create();
    $anak = Anak::factory()->create(['status_data' => 'valid']);

    $this->actingAs($verifikator)
        ->post(route('stunting.verifikasi.store', $anak), ['status' => 'valid'])
        ->assertSessionHas('error');

    expect($anak->fresh()->status_data)->toBe('valid');
});

it('petugas dapat mengirim ulang data berstatus perlu perbaikan', function () {
    $petugas = User::factory()->create();
    $anak = Anak::factory()->create(['status_data' => 'perlu_perbaikan', 'petugas_id' => $petugas->id]);

    $this->actingAs($petugas)
        ->post(route('stunting.kirim', $anak))
        ->assertRedirect();

    $anak->refresh();

    expect($anak->status_data)->toBe('dikirim');
    expect($anak->riwayatVerifikasi()->where('status', 'dikirim')->count())->toBe(1);
});
