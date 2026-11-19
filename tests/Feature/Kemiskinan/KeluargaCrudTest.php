<?php

use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\User;

function payloadPendataan(Kecamatan $kecamatan, DesaKelurahan $desa, array $overrides = []): array
{
    return array_merge([
        'nomor_kk' => '1234567890123456',
        'nomor_hp' => '081234567890',
        'alamat' => 'Jl. Merdeka No. 1',
        'rt' => '01',
        'rw' => '02',
        'kecamatan_id' => $kecamatan->id,
        'desa_kelurahan_id' => $desa->id,
        'anggota' => [
            [
                'nik' => '1234567890123457',
                'nama_lengkap' => 'Abdul Rasyid',
                'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '1985-01-01',
                'hubungan_keluarga' => 'kepala_keluarga',
                'status_pekerjaan' => 'nelayan',
            ],
        ],
        'kondisi_ekonomi' => [
            'pekerjaan_utama' => 'nelayan',
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
            'pekerjaan_utama' => 'nelayan',
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

    $payload = payloadPendataan($kecamatan, $desa);
    $payload['anggota'][0]['nama_lengkap'] = 'Nama Baru';

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

it('menolak hubungan keluarga di luar pilihan dropdown', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, [
            'anggota' => [[
                'nama_lengkap' => 'Abdul Rasyid',
                'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '1985-01-01',
                'hubungan_keluarga' => 'Bapak Rumah',
            ]],
        ]))
        ->assertSessionHasErrors('anggota.0.hubungan_keluarga');

    expect(Keluarga::count())->toBe(0);
});

it('mengambil identitas kepala keluarga dan ringkasan sosial ekonomi dari data anggota', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, [
        'anggota' => [
            ['nik' => '8271011203880001', 'nama_lengkap' => 'Ahmad', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => '1988-03-12',
                'hubungan_keluarga' => 'kepala_keluarga', 'status_perkawinan' => 'kawin', 'status_pekerjaan' => 'nelayan'],
            ['nama_lengkap' => 'Fatma', 'jenis_kelamin' => 'perempuan', 'tanggal_lahir' => '1992-06-15',
                'hubungan_keluarga' => 'istri', 'status_pekerjaan' => 'tidak_bekerja', 'jenis_penyakit_kronis' => 'hipertensi'],
            ['nama_lengkap' => 'Rahmat', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => now()->subYears(10)->format('Y-m-d'),
                'hubungan_keluarga' => 'anak', 'status_pekerjaan' => 'pelajar', 'status_sekolah' => 'sedang_sekolah', 'disabilitas' => '1'],
            ['nama_lengkap' => 'Salma', 'jenis_kelamin' => 'perempuan', 'tanggal_lahir' => '1955-01-01',
                'hubungan_keluarga' => 'mertua', 'status_pekerjaan' => 'tidak_bekerja'],
        ],
    ]))->assertSessionHasNoErrors();

    $keluarga = Keluarga::with(['kondisiEkonomi', 'kondisiSosial'])->sole();

    expect($keluarga->nik_kepala_keluarga)->toBe('8271011203880001')
        ->and($keluarga->nama_kepala_keluarga)->toBe('Ahmad')
        ->and($keluarga->status_perkawinan)->toBe('kawin')
        ->and($keluarga->jumlah_anggota_keluarga)->toBe(4)
        ->and($keluarga->kondisiEkonomi->status_pekerjaan_kepala_keluarga)->toBe('nelayan')
        ->and($keluarga->kondisiEkonomi->jumlah_anggota_bekerja)->toBe(1)
        ->and($keluarga->kondisiEkonomi->jumlah_anggota_tidak_bekerja)->toBe(3)
        ->and($keluarga->kondisiSosial->jumlah_anak_usia_sekolah)->toBe(1)
        ->and($keluarga->kondisiSosial->jumlah_anak_bersekolah)->toBe(1)
        ->and($keluarga->kondisiSosial->jumlah_lansia)->toBe(1)
        ->and($keluarga->kondisiSosial->jumlah_penyandang_disabilitas)->toBe(1)
        ->and($keluarga->kondisiSosial->jumlah_anggota_sakit)->toBe(1);
});

it('mewajibkan tepat satu kepala keluarga dengan nik 16 digit', function (array $anggota, string $errorKey) {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, ['anggota' => $anggota]))
        ->assertSessionHasErrors($errorKey);

    expect(Keluarga::count())->toBe(0);
})->with([
    'tanpa kepala keluarga' => [
        [['nama_lengkap' => 'Fatma', 'jenis_kelamin' => 'perempuan', 'tanggal_lahir' => '1992-06-15', 'hubungan_keluarga' => 'istri']],
        'anggota',
    ],
    'dua kepala keluarga' => [
        [
            ['nik' => '1234567890123457', 'nama_lengkap' => 'A', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => '1980-01-01', 'hubungan_keluarga' => 'kepala_keluarga'],
            ['nik' => '1234567890123458', 'nama_lengkap' => 'B', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => '1981-01-01', 'hubungan_keluarga' => 'kepala_keluarga'],
        ],
        'anggota',
    ],
    'kepala keluarga tanpa nik' => [
        [['nama_lengkap' => 'A', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => '1980-01-01', 'hubungan_keluarga' => 'kepala_keluarga']],
        'anggota.0.nik',
    ],
]);

it('menyimpan isian kemiskinan sesuai catatan pengembangan dan menurunkan nilai otomatis', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, [
        'anggota' => [
            ['nik' => '8271011203880001', 'nama_lengkap' => 'Ahmad', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => '1988-03-12',
                'hubungan_keluarga' => 'kepala_keluarga', 'status_pekerjaan' => 'lainnya', 'pekerjaan_lainnya' => 'Penjaga Kebun'],
            ['nama_lengkap' => 'Rahmat', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => now()->subYears(12)->format('Y-m-d'),
                'hubungan_keluarga' => 'anak', 'status_pekerjaan' => 'pelajar', 'status_sekolah' => 'sedang_sekolah'],
            ['nama_lengkap' => 'Rina', 'jenis_kelamin' => 'perempuan', 'tanggal_lahir' => now()->subYears(15)->format('Y-m-d'),
                'hubungan_keluarga' => 'anak', 'status_pekerjaan' => 'tidak_bekerja', 'status_sekolah' => 'putus_sekolah'],
        ],
        'kondisi_ekonomi' => [
            'pekerjaan_utama' => 'lainnya', 'pekerjaan_utama_lainnya' => 'Penjaga Kebun',
            'perlindungan_ketenagakerjaan' => '1', 'bahan_bakar_memasak' => 'lpg_3kg',
            'pengeluaran_makanan' => 500000, 'pengeluaran_pakaian' => 100000,
        ],
        'kondisi_rumah' => [
            'status_kepemilikan' => 'milik_sendiri', 'kondisi_bangunan' => 'permanen', 'daya_listrik' => '900',
            'jenis_jamban' => 'leher_angsa', 'jenis_septic_tank' => 'tangki_septik',
        ],
        'aset' => [['jenis_aset' => 'lainnya', 'jenis_aset_lainnya' => 'Perahu Ketinting', 'jumlah' => 1]],
        'kondisi_sosial' => ['bantuan_pendidikan' => 'prasarana_sekolah', 'memiliki_pbi' => '1'],
    ]))->assertSessionHasNoErrors();

    $keluarga = Keluarga::with(['kondisiEkonomi', 'kondisiRumah', 'kondisiSosial', 'asetKeluarga', 'anggotaKeluarga'])->sole();

    expect($keluarga->kondisiEkonomi->pekerjaan_utama_label)->toBe('Penjaga Kebun')
        ->and($keluarga->kondisiEkonomi->perlindungan_ketenagakerjaan)->toBeTrue()
        ->and((float) $keluarga->kondisiEkonomi->total_pengeluaran)->toBe(600000.0)
        ->and($keluarga->anggotaKeluarga->firstWhere('nama_lengkap', 'Ahmad')->status_pekerjaan_label)->toBe('Penjaga Kebun')
        ->and($keluarga->kondisiRumah->daya_listrik)->toBe('900')
        ->and($keluarga->kondisiRumah->jamban)->toBeTrue()
        ->and($keluarga->kondisiRumah->septic_tank)->toBeTrue()
        ->and($keluarga->asetKeluarga->first()->jenis_aset_label)->toBe('Perahu Ketinting')
        ->and($keluarga->kondisiSosial->jumlah_anak_bersekolah)->toBe(1)
        ->and($keluarga->kondisiSosial->jumlah_anak_putus_sekolah)->toBe(1)
        ->and($keluarga->kondisiSosial->memiliki_pbi)->toBeTrue()
        ->and($keluarga->kondisiSosial->memiliki_jkn)->toBeTrue();
});

it('tidak menyimpan kapasitas daya listrik untuk rumah yang bukan milik sendiri', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, [
        'kondisi_rumah' => ['status_kepemilikan' => 'sewa', 'kondisi_bangunan' => 'permanen', 'daya_listrik' => '1300'],
    ]))->assertSessionHasNoErrors();

    expect(Keluarga::with('kondisiRumah')->sole()->kondisiRumah->daya_listrik)->toBeNull();
});

it('mewajibkan pekerjaan setiap anggota dan isian manual bila memilih lainnya', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

    $this->actingAs($user)->post(route('kemiskinan.store'), payloadPendataan($kecamatan, $desa, [
        'anggota' => [
            ['nik' => '1234567890123457', 'nama_lengkap' => 'A', 'jenis_kelamin' => 'laki-laki', 'tanggal_lahir' => '1980-01-01',
                'hubungan_keluarga' => 'kepala_keluarga', 'status_pekerjaan' => 'lainnya'],
            ['nama_lengkap' => 'B', 'jenis_kelamin' => 'perempuan', 'tanggal_lahir' => '1982-01-01', 'hubungan_keluarga' => 'istri'],
        ],
        'kondisi_ekonomi' => ['pekerjaan_utama' => ''],
        'aset' => [['jenis_aset' => 'lainnya']],
    ]))->assertSessionHasErrors([
        'anggota.0.pekerjaan_lainnya', 'anggota.1.status_pekerjaan', 'kondisi_ekonomi.pekerjaan_utama', 'aset.0.jenis_aset_lainnya',
    ]);

    expect(Keluarga::count())->toBe(0);
});
