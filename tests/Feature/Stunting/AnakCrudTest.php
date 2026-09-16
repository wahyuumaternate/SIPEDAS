<?php

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\User;

function payloadPendataanAnak(Kecamatan $kecamatan, DesaKelurahan $desa, array $overrides = []): array
{
    return array_merge([
        'action' => 'draft',
        'nik_anak' => '1234567890123456',
        'nomor_kk' => '1234567890123457',
        'nama_anak' => 'Fatimah Az-Zahra',
        'jenis_kelamin' => 'perempuan',
        'tempat_lahir' => 'Ternate',
        'tanggal_lahir' => now()->subMonths(18)->format('Y-m-d'),
        'nama_ayah' => 'Abdul Rasyid',
        'nama_ibu' => 'Siti Aminah',
        'nomor_hp_orang_tua' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'kecamatan_id' => $kecamatan->id,
        'desa_kelurahan_id' => $desa->id,
        'riwayat_kelahiran' => [
            'tanggal_lahir' => now()->subMonths(18)->format('Y-m-d'),
            'tempat_lahir' => 'RSUD Ternate',
        ],
    ], $overrides);
}

it('menampilkan daftar data stunting untuk pengguna yang login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('stunting.index'))
        ->assertOk()
        ->assertViewIs('stunting.index');
});

it('menolak akses untuk pengguna yang belum login', function () {
    $this->get(route('stunting.index'))->assertRedirect(route('login'));
});

it('menyimpan pendataan anak baru sebagai draft beserta data turunannya', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $response = $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa));

    $anak = Anak::first();

    $response->assertRedirect(route('stunting.show', $anak));
    expect($anak)->not->toBeNull();
    expect($anak->status_data)->toBe('draft');
    expect($anak->petugas_id)->toBe($user->id);
    expect($anak->kode_pendataan)->not->toBeEmpty();
    expect($anak->usia_bulan)->toBe(18);
    expect($anak->riwayatKelahiran->tempat_lahir)->toBe('RSUD Ternate');
});

it('menyimpan draft hanya dengan identitas anak tanpa riwayat kelahiran', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $payload = payloadPendataanAnak($kecamatan, $desa);
    unset($payload['riwayat_kelahiran']);

    $this->actingAs($user)
        ->post(route('stunting.store'), $payload)
        ->assertSessionDoesntHaveErrors();

    $anak = Anak::first();

    expect($anak)->not->toBeNull();
    expect($anak->status_data)->toBe('draft');
    expect($anak->riwayatKelahiran)->toBeNull();
});

it('mengirim pendataan langsung untuk verifikasi dan mencatat riwayat', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, ['action' => 'kirim']));

    $anak = Anak::first();

    expect($anak->status_data)->toBe('dikirim');
    expect($anak->riwayatVerifikasi)->toHaveCount(1);
    expect($anak->riwayatVerifikasi->first()->status)->toBe('dikirim');
});

it('menolak pengiriman verifikasi tanpa riwayat kelahiran', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $payload = payloadPendataanAnak($kecamatan, $desa, ['action' => 'kirim']);
    unset($payload['riwayat_kelahiran']);

    $this->actingAs($user)
        ->post(route('stunting.store'), $payload)
        ->assertSessionHasErrors(['riwayat_kelahiran.tanggal_lahir', 'riwayat_kelahiran.tempat_lahir']);

    expect(Anak::count())->toBe(0);
});

it('menyimpan pengukuran awal saat pendataan baru', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, [
        'pengukuran' => [
            'tanggal_pengukuran' => now()->format('Y-m-d'),
            'berat_badan' => 9.5,
            'panjang_tinggi_badan' => 78,
        ],
    ]));

    $anak = Anak::first();

    expect($anak->pengukurans)->toHaveCount(1);
    expect((float) $anak->pengukurans->first()->berat_badan)->toBe(9.5);
    expect($anak->pengukurans->first()->usia_saat_pengukuran_bulan)->toBe(18);
});

it('mengubah data anak yang sudah ada', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $anak = Anak::factory()->create(['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id]);

    $payload = payloadPendataanAnak($kecamatan, $desa, ['nama_anak' => 'Nama Baru']);

    $this->actingAs($user)
        ->put(route('stunting.update', $anak), $payload)
        ->assertRedirect(route('stunting.show', $anak));

    expect($anak->fresh()->nama_anak)->toBe('Nama Baru');
});

it('menghapus data anak (soft delete)', function () {
    $user = User::factory()->create();
    $anak = Anak::factory()->create();

    $this->actingAs($user)
        ->delete(route('stunting.destroy', $anak))
        ->assertRedirect(route('stunting.index'));

    expect(Anak::find($anak->id))->toBeNull();
    expect(Anak::withTrashed()->find($anak->id))->not->toBeNull();
});
