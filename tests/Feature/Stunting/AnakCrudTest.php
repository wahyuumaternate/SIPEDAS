<?php

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\User;

function payloadPendataanAnak(Kecamatan $kecamatan, DesaKelurahan $desa, array $overrides = []): array
{
    return array_merge([
        'nik_anak' => '1234567890123456',
        'nomor_kk' => '1234567890123457',
        'nama_anak' => 'Fatimah Az-Zahra',
        'jenis_kelamin' => 'perempuan',
        'tempat_lahir' => 'Ternate',
        'tanggal_lahir' => now()->subMonths(18)->format('Y-m-d'),
        'nomor_hp_orang_tua' => '081234567890',
        'orang_tua' => [
            'nama_ayah_lengkap' => 'Abdul Rasyid',
            'nama_ibu_lengkap' => 'Siti Aminah',
        ],
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

it('menyimpan pendataan anak baru dengan status dalam verifikasi beserta data turunannya', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $response = $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa));

    $anak = Anak::first();

    $response->assertRedirect(route('stunting.show', $anak));
    expect($anak)->not->toBeNull();
    expect($anak->status_data)->toBe('dalam_verifikasi');
    expect($anak->petugas_id)->toBe($user->id);
    expect($anak->kode_pendataan)->not->toBeEmpty();
    expect($anak->usia_bulan)->toBe(18);
    expect($anak->riwayatKelahiran->tempat_lahir)->toBe('RSUD Ternate');
});

it('menolak NIK anak yang sudah terdaftar', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    Anak::factory()->create(['nik_anak' => '1234567890123456']);

    $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, ['nik_anak' => '1234567890123456']))
        ->assertSessionHasErrors(['nik_anak']);

    expect(Anak::count())->toBe(1);
});

it('mengizinkan NIK anak yang sama saat mengubah data anak itu sendiri', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $anak = Anak::factory()->create(['nik_anak' => '1234567890123456']);

    $this->actingAs($user)
        ->put(route('stunting.update', $anak), payloadPendataanAnak($kecamatan, $desa, ['nik_anak' => '1234567890123456']))
        ->assertSessionDoesntHaveErrors();
});

it('mengembalikan data perlu perbaikan ke dalam verifikasi setelah diubah', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $anak = Anak::factory()->create(['status_data' => 'perlu_perbaikan']);

    $this->actingAs($user)->put(route('stunting.update', $anak), payloadPendataanAnak($kecamatan, $desa));

    expect($anak->fresh()->status_data)->toBe('dalam_verifikasi');
});

it('mencatat riwayat verifikasi awal saat pendataan disimpan', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa));

    $anak = Anak::first();

    expect($anak->status_data)->toBe('dalam_verifikasi');
    expect($anak->riwayatVerifikasi)->toHaveCount(1);
    expect($anak->riwayatVerifikasi->first()->status)->toBe('dalam_verifikasi');
});

it('menolak penyimpanan tanpa riwayat kelahiran', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $payload = payloadPendataanAnak($kecamatan, $desa);
    unset($payload['riwayat_kelahiran']);

    $this->actingAs($user)
        ->post(route('stunting.store'), $payload)
        ->assertSessionHasErrors('riwayat_kelahiran.tempat_lahir');

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

it('menyimpan field layanan untuk indikator laporan analisis situasi', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, [
        'memiliki_akta_kelahiran' => '1',
        'riwayat_kehamilan' => ['jumlah_tablet_tambah_darah' => '180', 'status_kek' => '1', 'pmt_kek' => '1', 'kb_pascapersalinan' => '1'],
        'pengukuran' => ['tanggal_pengukuran' => now()->format('Y-m-d'), 'berat_badan' => '8.5', 'panjang_tinggi_badan' => '70', 'status_gizi' => 'gizi_kurang'],
        'riwayat_kesehatan' => ['tambahan_asupan_gizi' => '1', 'obat_cacing' => '1', 'memiliki_jkn' => '1'],
        'asi_mpasi' => ['masih_asi' => '1'],
    ]))->assertSessionHasNoErrors();

    $anak = Anak::with(['riwayatKehamilan', 'pengukurans', 'riwayatKesehatan', 'asiMpasi'])->sole();

    expect($anak->memiliki_akta_kelahiran)->toBeTrue()
        ->and($anak->riwayatKehamilan->jumlah_tablet_tambah_darah)->toBe(180)
        ->and($anak->riwayatKehamilan->pmt_kek)->toBeTrue()
        ->and($anak->riwayatKehamilan->kb_pascapersalinan)->toBeTrue()
        ->and($anak->pengukurans->first()->status_gizi)->toBe('gizi_kurang')
        ->and($anak->riwayatKesehatan->tambahan_asupan_gizi)->toBeTrue()
        ->and($anak->riwayatKesehatan->obat_cacing)->toBeTrue()
        ->and($anak->riwayatKesehatan->memiliki_jkn)->toBeTrue()
        ->and($anak->asiMpasi->masih_asi)->toBeTrue();
});

it('mengambil nama ayah dan ibu anak dari langkah data orang tua', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa))
        ->assertSessionHasNoErrors();

    $anak = Anak::with('orangTua')->sole();

    expect($anak->nama_ayah)->toBe('Abdul Rasyid')
        ->and($anak->nama_ibu)->toBe('Siti Aminah')
        ->and($anak->orangTua->nama_ibu_lengkap)->toBe('Siti Aminah');
});

it('mewajibkan nama ayah dan ibu pada data orang tua', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, ['orang_tua' => ['nik_ayah' => '1234567890123458']]))
        ->assertSessionHasErrors(['orang_tua.nama_ayah_lengkap', 'orang_tua.nama_ibu_lengkap']);

    expect(Anak::count())->toBe(0);
});

it('menurunkan nilai dari input tunggal sehingga tidak perlu diisi dua kali', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $tanggalLahir = now()->subMonths(18)->format('Y-m-d');

    $this->actingAs($user)->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, [
        'tanggal_lahir' => $tanggalLahir,
        'orang_tua' => [
            'nama_ayah_lengkap' => 'Abdul Rasyid',
            'nama_ibu_lengkap' => 'Siti Aminah',
            'tanggal_lahir_ibu' => now()->subMonths(18)->subYears(25)->format('Y-m-d'),
        ],
        'riwayat_kelahiran' => ['tempat_lahir' => 'Puskesmas', 'imd' => '1'],
        'riwayat_kehamilan' => ['jumlah_tablet_tambah_darah' => '90', 'lila' => '22.0'],
        'asi_mpasi' => ['asi_eksklusif' => '1'],
        'sanitasi' => ['jenis_jamban' => 'tidak_ada'],
    ]))->assertSessionHasNoErrors();

    $anak = Anak::with(['riwayatKelahiran', 'riwayatKehamilan', 'asiMpasi', 'sanitasi'])->sole();

    expect($anak->riwayatKelahiran->tanggal_lahir->format('Y-m-d'))->toBe($tanggalLahir)
        ->and($anak->riwayatKelahiran->tempat_lahir)->toBe('Puskesmas')
        ->and($anak->asiMpasi->imd)->toBeTrue()
        ->and($anak->riwayatKehamilan->konsumsi_tablet_tambah_darah)->toBe('kadang')
        ->and($anak->riwayatKehamilan->status_kek)->toBeTrue()
        ->and($anak->riwayatKehamilan->usia_ibu_saat_hamil)->toBe(25)
        ->and($anak->sanitasi->kepemilikan_jamban)->toBeFalse();
});

it('menyimpan anak tanpa NIK beserta anak ke- dan status berat badan menurut umur', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, [
        'nik_anak' => '',
        'anak_ke' => '3',
        'pengukuran' => ['tanggal_pengukuran' => now()->format('Y-m-d'), 'berat_badan' => '7.5', 'panjang_tinggi_badan' => '72', 'status_bb_u' => 'bb_kurang'],
    ]))->assertSessionHasNoErrors();

    $anak = Anak::with('pengukurans')->sole();

    expect($anak->nik_anak)->toBeNull()
        ->and($anak->anak_ke)->toBe(3)
        ->and($anak->status_pemantauan)->toBe('aktif')
        ->and($anak->pengukurans->first()->status_bb_u)->toBe('bb_kurang');
});

it('mewajibkan alasan saat status pemantauan anak dinonaktifkan', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, ['status_pemantauan' => 'nonaktif']))
        ->assertSessionHasErrors('alasan_nonaktif');

    $this->actingAs($user)
        ->post(route('stunting.store'), payloadPendataanAnak($kecamatan, $desa, ['status_pemantauan' => 'nonaktif', 'alasan_nonaktif' => 'pindah_domisili']))
        ->assertSessionHasNoErrors();

    $anak = Anak::sole();

    expect($anak->status_pemantauan)->toBe('nonaktif')
        ->and($anak->alasan_nonaktif)->toBe('pindah_domisili')
        ->and($anak->tanggal_nonaktif)->not->toBeNull();
});
