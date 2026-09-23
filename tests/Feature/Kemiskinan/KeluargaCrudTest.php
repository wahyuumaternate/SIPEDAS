<?php

use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\User;

function payloadPendataan(Kecamatan $kecamatan, DesaKelurahan $desa, array $overrides = []): array
{
    return array_merge([
        'nomor_kk' => '1234567890123456',
        'nik_kepala_keluarga' => '1234567890123457',
        'nama_kepala_keluarga' => 'Abdul Rasyid',
        'nomor_hp' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'rt' => '01',
        'rw' => '02',
        'kecamatan_id' => $kecamatan->id,
        'desa_kelurahan_id' => $desa->id,
        'anggota' => [
            [
                'nama_lengkap' => 'Abdul Rasyid',
                'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '1985-01-01',
                'hubungan_keluarga' => 'Kepala Keluarga',
            ],
        ],
        'kondisi_rumah' => [
            'status_kepemilikan' => 'milik_sendiri',
            'kondisi_bangunan' => 'permanen',
        ],
    ], $overrides);
}

it('menampilkan daftar data kemiskinan untuk pengguna yang login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('kemiskinan.index'))
        ->assertOk()
        ->assertViewIs('kemiskinan.index');
});

it('menolak akses untuk pengguna yang belum login', function () {
    $this->get(route('kemiskinan.index'))->assertRedirect(route('login'));
});

it('menyimpan pendataan keluarga baru dengan status dalam verifikasi beserta data turunannya', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $response = $this->actingAs($user)
        ->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa));

    $keluarga = Keluarga::first();

    $response->assertRedirect(route('kemiskinan.show', $keluarga));
    expect($keluarga)->not->toBeNull();
    expect($keluarga->status_data)->toBe('dalam_verifikasi');
    expect($keluarga->petugas_id)->toBe($user->id);
    expect($keluarga->kode_pendataan)->not->toBeEmpty();
    expect($keluarga->anggotaKeluarga)->toHaveCount(1);
    expect($keluarga->kondisiRumah->status_kepemilikan)->toBe('milik_sendiri');
});

it('mencatat riwayat verifikasi awal saat pendataan disimpan', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa));

    $keluarga = Keluarga::first();

    expect($keluarga->status_data)->toBe('dalam_verifikasi');
    expect($keluarga->riwayatVerifikasi)->toHaveCount(1);
    expect($keluarga->riwayatVerifikasi->first()->status)->toBe('dalam_verifikasi');
});

it('menghitung total pendapatan dan pengeluaran secara otomatis', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, [
        'kondisi_ekonomi' => [
            'pendapatan_kepala_keluarga' => 1000000,
            'pendapatan_pasangan' => 500000,
            'pengeluaran_makanan' => 300000,
            'pengeluaran_listrik' => 100000,
        ],
    ]));

    $keluarga = Keluarga::first();

    expect((float) $keluarga->kondisiEkonomi->total_pendapatan)->toBe(1500000.0);
    expect((float) $keluarga->kondisiEkonomi->total_pengeluaran)->toBe(400000.0);
});

it('menolak nomor KK yang sudah terdaftar', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    Keluarga::factory()->create(['nomor_kk' => '1234567890123456']);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, ['nomor_kk' => '1234567890123456']))
        ->assertSessionHasErrors(['nomor_kk']);

    expect(Keluarga::count())->toBe(1);
});

it('mengizinkan nomor KK yang sama saat mengubah data keluarga itu sendiri', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $keluarga = Keluarga::factory()->create(['nomor_kk' => '1234567890123456']);

    $this->actingAs($user)
        ->put(route('kemiskinan.update', $keluarga), payloadPendataan($kecamatan, $desa, ['nomor_kk' => '1234567890123456']))
        ->assertSessionDoesntHaveErrors();
});

it('mengembalikan data perlu perbaikan ke dalam verifikasi setelah diubah', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $keluarga = Keluarga::factory()->create(['status_data' => 'perlu_perbaikan']);

    $this->actingAs($user)->put(route('kemiskinan.update', $keluarga), payloadPendataan($kecamatan, $desa));

    expect($keluarga->fresh()->status_data)->toBe('dalam_verifikasi');
});

it('menolak penyimpanan tanpa anggota keluarga', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $payload = payloadPendataan($kecamatan, $desa);
    unset($payload['anggota']);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), $payload)
        ->assertSessionHasErrors(['anggota']);

    expect(Keluarga::count())->toBe(0);
});

it('menolak penyimpanan tanpa kondisi rumah', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $payload = payloadPendataan($kecamatan, $desa);
    unset($payload['kondisi_rumah']);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), $payload)
        ->assertSessionHasErrors(['kondisi_rumah.status_kepemilikan', 'kondisi_rumah.kondisi_bangunan']);

    expect(Keluarga::count())->toBe(0);
});

it('mengubah data keluarga yang sudah ada', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $keluarga = Keluarga::factory()->create(['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id]);

    $payload = payloadPendataan($kecamatan, $desa, ['nama_kepala_keluarga' => 'Nama Baru']);

    $this->actingAs($user)
        ->put(route('kemiskinan.update', $keluarga), $payload)
        ->assertRedirect(route('kemiskinan.show', $keluarga));

    expect($keluarga->fresh()->nama_kepala_keluarga)->toBe('Nama Baru');
});

it('menghapus data keluarga (soft delete)', function () {
    $user = User::factory()->create();
    $keluarga = Keluarga::factory()->create();

    $this->actingAs($user)
        ->delete(route('kemiskinan.destroy', $keluarga))
        ->assertRedirect(route('kemiskinan.index'));

    expect(Keluarga::find($keluarga->id))->toBeNull();
    expect(Keluarga::withTrashed()->find($keluarga->id))->not->toBeNull();
});
