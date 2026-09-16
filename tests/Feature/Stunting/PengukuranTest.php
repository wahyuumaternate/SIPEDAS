<?php

use App\Models\Anak;
use App\Models\User;

it('menambahkan record pengukuran baru tanpa menimpa data lama', function () {
    $user = User::factory()->create();
    $anak = Anak::factory()->create(['tanggal_lahir' => now()->subMonths(12)]);
    $anak->pengukurans()->create([
        'tanggal_pengukuran' => now()->subMonths(1),
        'berat_badan' => 8.0,
        'panjang_tinggi_badan' => 70,
        'petugas_pengukur_id' => $user->id,
    ]);

    $this->actingAs($user)->post(route('stunting.pengukuran.store', $anak), [
        'tanggal_pengukuran' => now()->format('Y-m-d'),
        'berat_badan' => 8.5,
        'panjang_tinggi_badan' => 72,
    ])->assertRedirect();

    expect($anak->pengukurans()->count())->toBe(2);
    expect((float) $anak->pengukurans()->orderBy('tanggal_pengukuran')->first()->berat_badan)->toBe(8.0);
});

it('menghitung usia saat pengukuran secara otomatis', function () {
    $user = User::factory()->create();
    $anak = Anak::factory()->create(['tanggal_lahir' => now()->subMonths(20)]);

    $this->actingAs($user)->post(route('stunting.pengukuran.store', $anak), [
        'tanggal_pengukuran' => now()->format('Y-m-d'),
        'berat_badan' => 10,
        'panjang_tinggi_badan' => 80,
    ]);

    expect($anak->pengukurans()->latest()->first()->usia_saat_pengukuran_bulan)->toBe(20);
});

it('menghapus record pengukuran', function () {
    $user = User::factory()->create();
    $anak = Anak::factory()->create();
    $pengukuran = $anak->pengukurans()->create([
        'tanggal_pengukuran' => now(),
        'berat_badan' => 9,
        'panjang_tinggi_badan' => 75,
        'petugas_pengukur_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->delete(route('stunting.pengukuran.destroy', [$anak, $pengukuran]))
        ->assertRedirect();

    expect($anak->pengukurans()->count())->toBe(0);
});
