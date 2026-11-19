<?php

use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\User;

it('menampilkan pesan validasi formulir dalam bahasa indonesia', function () {
    app()->setLocale('id');
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), [
            'nomor_kk' => '123',
            'kecamatan_id' => $kecamatan->id,
            'desa_kelurahan_id' => $desa->id,
        ])
        ->assertSessionHasErrors([
            'nomor_kk' => 'Nomor KK harus terdiri dari 16 digit angka.',
            'alamat' => 'Alamat wajib diisi.',
            'rt' => 'RT wajib diisi.',
        ]);
});

it('menampilkan pesan gagal masuk dalam bahasa indonesia', function () {
    app()->setLocale('id');
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'kata-sandi-salah',
        'form_rendered_at' => now()->subMinute()->timestamp,
    ])->assertSessionHasErrors(['email' => 'Email atau kata sandi yang Anda masukkan salah.']);
});
