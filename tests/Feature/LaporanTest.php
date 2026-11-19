<?php

use App\Models\Anak;
use App\Models\DesaKelurahan;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Pengukuran;
use App\Models\RiwayatKehamilan;
use App\Models\RiwayatKesehatan;
use App\Models\Role;
use App\Models\SasaranStunting;
use App\Models\User;
use App\Permission;

function userLaporan(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menolak akses laporan untuk user tanpa hak lihat kemiskinan maupun stunting', function () {
    $user = userLaporan([]);

    $this->actingAs($user)
        ->get(route('laporan.index'))
        ->assertForbidden();
});

it('menolak akun tanpa hak stunting membuka laporan modul stunting', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);

    $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'stunting']))
        ->assertForbidden();
});

it('menolak export tanpa permission export walau punya hak lihat', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);

    $this->actingAs($user)
        ->get(route('laporan.export', ['modul' => 'kemiskinan']))
        ->assertForbidden();
});

it('menolak export modul stunting bagi user yang tidak punya hak lihat stunting', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    Anak::factory()->create();

    $this->actingAs($user)
        ->get(route('laporan.export', ['modul' => 'stunting']))
        ->assertForbidden();
});

it('mentabulasi kk dan jiwa dtsen per desil dengan nonaktif dipisah dari jumlah', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Pulau Ternate']);
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id, 'nama' => 'Jambula']);
    $wilayah = ['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id];

    Keluarga::factory()->create([...$wilayah, 'desil' => 1, 'jumlah_anggota_keluarga' => 4]);
    Keluarga::factory()->create([...$wilayah, 'desil' => 1, 'jumlah_anggota_keluarga' => 3]);
    Keluarga::factory()->create([...$wilayah, 'desil' => 7, 'jumlah_anggota_keluarga' => 5]);
    Keluarga::factory()->create([...$wilayah, 'desil' => null, 'jumlah_anggota_keluarga' => 2]);
    Keluarga::factory()->create([...$wilayah, 'desil' => 2, 'status_dtsen' => 'nonaktif', 'jumlah_anggota_keluarga' => 6]);

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'kemiskinan']));

    $response->assertOk()->assertSee('Jambula');
    $tabulasi = $response->viewData('tabulasiDtsen');
    $baris = $tabulasi['kecamatans']->firstWhere('nama', 'Pulau Ternate')['desa']->firstWhere('nama', 'Jambula')['nilai'];

    expect($baris['jumlah'])->toBe(['kk' => 4, 'jiwa' => 14])
        ->and($baris['desil_1'])->toBe(['kk' => 2, 'jiwa' => 7])
        ->and($baris['desil_2'])->toBe(['kk' => 0, 'jiwa' => 0])
        ->and($baris['desil_6_10'])->toBe(['kk' => 1, 'jiwa' => 5])
        ->and($baris['belum'])->toBe(['kk' => 1, 'jiwa' => 2])
        ->and($baris['nonaktif'])->toBe(['kk' => 1, 'jiwa' => 6])
        ->and($tabulasi['total']['jumlah'])->toBe(['kk' => 4, 'jiwa' => 14]);
});

it('mengunduh tabulasi dtsen sebagai pdf', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    Keluarga::factory()->create(['desil' => 3]);

    $response = $this->actingAs($user)->get(route('laporan.export', [
        'modul' => 'kemiskinan',
        'periode' => 'Triwulan 3.2',
    ]));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('tabulasi-dtsen-');
});

it('menghitung prevalensi stunting dari pengukuran terakhir tiap anak per kelurahan', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Pulau Ternate']);
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id, 'nama' => 'Kastela']);
    $wilayah = ['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id];

    $sembuh = Anak::factory()->create($wilayah);
    Pengukuran::factory()->create(['anak_id' => $sembuh->id, 'tanggal_pengukuran' => now()->subMonths(3), 'hasil_kategori' => 'stunting_berat']);
    Pengukuran::factory()->create(['anak_id' => $sembuh->id, 'tanggal_pengukuran' => now(), 'hasil_kategori' => 'normal']);

    $kasus = Anak::factory()->create($wilayah);
    Pengukuran::factory()->create(['anak_id' => $kasus->id, 'hasil_kategori' => 'stunting_ringan']);

    Anak::factory()->create($wilayah);

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'stunting']));

    $response->assertOk()->assertSee('Kastela');
    $prevalensi = $response->viewData('tabulasiStunting')['prevalensi'];
    $baris = $prevalensi['desa']->firstWhere('nama', 'Kastela');

    expect($baris)->toMatchArray(['diukur' => 2, 'kasus' => 1, 'prevalensi' => 50.0])
        ->and($prevalensi['total'])->toMatchArray(['diukur' => 2, 'kasus' => 1, 'prevalensi' => 50.0]);
});

it('menghitung prevalensi gizi buruk, gizi kurang, dan berat badan kurang serta mengabaikan anak nonaktif', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Moti']);
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id, 'nama' => 'Tafaga']);
    $wilayah = ['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id];

    $giziBuruk = Anak::factory()->create($wilayah);
    Pengukuran::factory()->create(['anak_id' => $giziBuruk->id, 'hasil_kategori' => 'normal', 'status_gizi' => 'gizi_buruk', 'status_bb_u' => 'bb_sangat_kurang']);
    $giziKurang = Anak::factory()->create($wilayah);
    Pengukuran::factory()->create(['anak_id' => $giziKurang->id, 'hasil_kategori' => 'stunting_ringan', 'status_gizi' => 'gizi_kurang', 'status_bb_u' => 'bb_normal']);
    $sembuh = Anak::factory()->create([...$wilayah, 'status_pemantauan' => 'nonaktif', 'alasan_nonaktif' => 'sudah_tidak_stunting']);
    Pengukuran::factory()->create(['anak_id' => $sembuh->id, 'hasil_kategori' => 'normal', 'status_gizi' => 'gizi_baik', 'status_bb_u' => 'bb_normal']);
    $pindah = Anak::factory()->create([...$wilayah, 'status_pemantauan' => 'nonaktif', 'alasan_nonaktif' => 'pindah_domisili']);
    Pengukuran::factory()->create(['anak_id' => $pindah->id, 'hasil_kategori' => 'stunting_berat', 'status_gizi' => 'gizi_buruk']);

    $baris = $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'stunting']))
        ->assertOk()
        ->viewData('tabulasiStunting')['prevalensi']['desa']->firstWhere('nama', 'Tafaga');

    expect($baris['diukur'])->toBe(3)
        ->and($baris['kategori']['stunting'])->toBe(['kasus' => 1, 'persen' => 33.33])
        ->and($baris['kategori']['gizi_buruk'])->toBe(['kasus' => 1, 'persen' => 33.33])
        ->and($baris['kategori']['gizi_kurang'])->toBe(['kasus' => 1, 'persen' => 33.33])
        ->and($baris['kategori']['bb_kurang'])->toBe(['kasus' => 1, 'persen' => 33.33]);
});

it('menghitung capaian layanan sesuai kelompok usia dan mengosongkan indikator tanpa data', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Moti']);
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $wilayah = ['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id];

    $baduta = Anak::factory()->create([...$wilayah, 'tanggal_lahir' => now()->subMonths(10)]);
    RiwayatKesehatan::factory()->create(['anak_id' => $baduta->id, 'status_imunisasi' => 'lengkap']);
    Pengukuran::factory()->create(['anak_id' => $baduta->id]);
    Anak::factory()->create([...$wilayah, 'tanggal_lahir' => now()->subMonths(10)]);
    $balita = Anak::factory()->create([...$wilayah, 'tanggal_lahir' => now()->subMonths(40)]);
    RiwayatKesehatan::factory()->create(['anak_id' => $balita->id, 'status_imunisasi' => 'lengkap']);
    Pengukuran::factory()->create(['anak_id' => $balita->id]);

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'stunting']));

    $tabulasi = $response->viewData('tabulasiStunting');
    $nilai = $tabulasi['capaian']['kecamatans']->firstWhere('nama', 'Moti')['nilai'];
    $demografi = $tabulasi['demografi']->firstWhere('nama', 'Moti')['nilai'];

    expect($nilai['baduta']['imunisasi'])->toBe(['jml' => 1, 'sasaran' => 2, 'persen' => 50.0])
        ->and($nilai['balita']['dipantau'])->toBe(['jml' => 2, 'sasaran' => 3, 'persen' => 66.67])
        ->and($nilai['remaja_putri']['ttd'])->toBeNull()
        ->and($demografi['anak_6_23'])->toBe(2)
        ->and($demografi['anak_24_59'])->toBe(1)
        ->and($demografi['penduduk'])->toBeNull();
});

it('mengunduh tabulasi stunting sebagai pdf', function () {
    $user = userLaporan([Permission::StuntingView->value, Permission::Export->value]);
    Pengukuran::factory()->create(['hasil_kategori' => 'stunting_sedang']);

    $response = $this->actingAs($user)->get(route('laporan.export', [
        'modul' => 'stunting',
        'periode' => 'Tahun 2026',
    ]));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('tabulasi-stunting-');
});

it('membatasi tabulasi dtsen sesuai filter kecamatan', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);
    $sesuai = DesaKelurahan::factory()->create();
    $lain = DesaKelurahan::factory()->create();
    Keluarga::factory()->create(['kecamatan_id' => $sesuai->kecamatan_id, 'desa_kelurahan_id' => $sesuai->id]);
    Keluarga::factory()->create(['kecamatan_id' => $lain->kecamatan_id, 'desa_kelurahan_id' => $lain->id]);

    $response = $this->actingAs($user)->get(route('laporan.index', [
        'modul' => 'kemiskinan',
        'kecamatan_id' => $sesuai->kecamatan_id,
    ]));

    $tabulasi = $response->viewData('tabulasiDtsen');

    expect($tabulasi['kecamatans'])->toHaveCount(1)
        ->and($tabulasi['kecamatans']->first()['nama'])->toBe($sesuai->kecamatan->nama)
        ->and($tabulasi['total']['jumlah']['kk'])->toBe(1);
});

it('menampilkan grafik radar chart.js hanya untuk indikator yang memiliki data', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    Pengukuran::factory()->create();

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'stunting']));

    $response->assertOk()
        ->assertSee('id="grafikRadarStunting"', false)
        ->assertSee('assets/js/chart.umd.min.js', false);

    preg_match("/data-sumbu='([^']+)'/", $response->getContent(), $cocok);
    $label = collect(json_decode(html_entity_decode($cocok[1]), true))->pluck('label');

    expect($label)->toContain('Balita dipantau')->not->toContain('TTD Rematri');
});

it('menerima gambar grafik png dari browser saat export pdf stunting lewat post', function (string $gambar) {
    $user = userLaporan([Permission::StuntingView->value, Permission::Export->value]);
    Pengukuran::factory()->create();

    $response = $this->actingAs($user)->post(route('laporan.export'), [
        'modul' => 'stunting',
        'grafik_radar' => $gambar,
    ]);

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
})->with([
    'png valid' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'bukan png (jatuh ke svg cadangan)' => 'data:image/png;base64,'.base64_encode('<svg onload="alert(1)"></svg>'),
]);

it('tidak menampilkan panel rekap lama pada laporan kemiskinan', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);
    Keluarga::factory()->create();

    $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'kemiskinan']))
        ->assertOk()
        ->assertSee('Tabulasi Data Tunggal Sosial dan Ekonomi Nasional')
        ->assertDontSee('Rekap per Kecamatan')
        ->assertDontSee('Rekap Kepesertaan Program');
});

it('tidak menampilkan panel rekap lama pada laporan stunting', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    Anak::factory()->create();

    $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'stunting']))
        ->assertOk()
        ->assertSee('Tabulasi Data Stunting')
        ->assertDontSee('Rekap per Kecamatan')
        ->assertDontSee('Rekap Kategori Stunting')
        ->assertDontSee('Rekap Jenis Kelamin');
});

it('menghitung indikator layanan baru dengan penyebut sesuai kondisi sasaran', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Hiri']);
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    $wilayah = ['kecamatan_id' => $kecamatan->id, 'desa_kelurahan_id' => $desa->id];

    $giziBurukDitangani = Anak::factory()->create([...$wilayah, 'tanggal_lahir' => now()->subMonths(30)]);
    Pengukuran::factory()->create(['anak_id' => $giziBurukDitangani->id, 'status_gizi' => 'gizi_buruk']);
    RiwayatKesehatan::factory()->create(['anak_id' => $giziBurukDitangani->id, 'tata_laksana_gizi_buruk' => true]);

    $giziBurukBelum = Anak::factory()->create([...$wilayah, 'tanggal_lahir' => now()->subMonths(30)]);
    Pengukuran::factory()->create(['anak_id' => $giziBurukBelum->id, 'status_gizi' => 'gizi_buruk']);

    $giziBaik = Anak::factory()->create([...$wilayah, 'tanggal_lahir' => now()->subMonths(30)]);
    Pengukuran::factory()->create(['anak_id' => $giziBaik->id, 'status_gizi' => 'gizi_baik']);
    RiwayatKehamilan::factory()->create(['anak_id' => $giziBaik->id, 'status_kek' => true, 'pmt_kek' => true, 'jumlah_tablet_tambah_darah' => 180]);
    RiwayatKehamilan::factory()->create(['anak_id' => $giziBurukBelum->id, 'status_kek' => false, 'jumlah_tablet_tambah_darah' => 90]);

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'stunting']));
    $nilai = $response->viewData('tabulasiStunting')['capaian']['kecamatans']->firstWhere('nama', 'Hiri')['nilai'];

    expect($nilai['balita']['gizi_buruk'])->toBe(['jml' => 1, 'sasaran' => 2, 'persen' => 50.0])
        ->and($nilai['ibu_hamil']['pmt_kek'])->toBe(['jml' => 1, 'sasaran' => 1, 'persen' => 100.0])
        ->and($nilai['ibu_hamil']['ttd'])->toBe(['jml' => 1, 'sasaran' => 2, 'persen' => 50.0]);
});

it('memakai data sasaran tahun terpilih untuk demografi dan capaian remaja putri', function () {
    $user = userLaporan([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Moti']);
    DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    SasaranStunting::factory()->create([
        'kecamatan_id' => $kecamatan->id,
        'tahun' => 2025,
        'penduduk' => 5059,
        'remaja_putri' => 235,
        'remaja_putri_ttd' => 47,
        'remaja_putri_skrining_anemia' => null,
    ]);

    $tabulasi = $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'stunting', 'tahun' => 2025]))
        ->assertOk()
        ->viewData('tabulasiStunting');

    $nilai = $tabulasi['capaian']['kecamatans']->firstWhere('nama', 'Moti')['nilai'];

    expect($tabulasi['demografi']->firstWhere('nama', 'Moti')['nilai']['penduduk'])->toBe(5059)
        ->and($nilai['remaja_putri']['ttd'])->toBe(['jml' => 47, 'sasaran' => 235, 'persen' => 20.0])
        ->and($nilai['remaja_putri']['anemia'])->toBeNull();

    $tahunLain = $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'stunting', 'tahun' => 2024]))
        ->viewData('tabulasiStunting');

    expect($tahunLain['demografi']->firstWhere('nama', 'Moti')['nilai']['penduduk'])->toBeNull();
});
