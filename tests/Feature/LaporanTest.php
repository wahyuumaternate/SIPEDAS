<?php

use App\Models\Anak;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\KepesertaanProgram;
use App\Models\Role;
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

it('menampilkan rekap kemiskinan per kecamatan dan status', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Ternate Tengah']);
    Keluarga::factory()->create(['kecamatan_id' => $kecamatan->id, 'status_data' => 'valid']);
    Keluarga::factory()->dalamVerifikasi()->create(['kecamatan_id' => $kecamatan->id]);

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'kemiskinan']));

    $response->assertOk();
    $response->assertSee('Ternate Tengah');
    expect($response->viewData('totalData'))->toBe(2);
});

it('menolak akun tanpa hak stunting membuka laporan modul stunting', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);

    $this->actingAs($user)
        ->get(route('laporan.index', ['modul' => 'stunting']))
        ->assertForbidden();
});

it('merekap kepesertaan program hanya untuk status penerima', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);
    $keluarga = Keluarga::factory()->create();
    KepesertaanProgram::factory()->create(['keluarga_id' => $keluarga->id, 'nama_program' => 'PKH', 'status_penerima' => 'penerima']);
    KepesertaanProgram::factory()->create(['keluarga_id' => $keluarga->id, 'nama_program' => 'Sembako', 'status_penerima' => 'bukan_penerima']);

    $response = $this->actingAs($user)->get(route('laporan.index', ['modul' => 'kemiskinan']));

    $response->assertSee('PKH');
    $response->assertDontSee('Sembako');
});

it('menolak export tanpa permission export walau punya hak lihat', function () {
    $user = userLaporan([Permission::KemiskinanView->value]);

    $this->actingAs($user)
        ->get(route('laporan.export', ['modul' => 'kemiskinan']))
        ->assertForbidden();
});

it('mengizinkan export csv untuk user dengan permission export dan hak lihat modul terkait', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    Keluarga::factory()->create(['nama_kepala_keluarga' => 'Budi Santoso']);

    $response = $this->actingAs($user)->get(route('laporan.export', ['modul' => 'kemiskinan']));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())->toContain('Budi Santoso');
});

it('menolak export modul stunting bagi user yang tidak punya hak lihat stunting', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    Anak::factory()->create();

    $this->actingAs($user)
        ->get(route('laporan.export', ['modul' => 'stunting']))
        ->assertForbidden();
});

it('mengizinkan export excel (xlsx) berisi data terfilter', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    Keluarga::factory()->create(['nama_kepala_keluarga' => 'Budi Santoso']);

    $response = $this->actingAs($user)
        ->get(route('laporan.export', ['modul' => 'kemiskinan', 'format' => 'xlsx']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('mengizinkan export pdf berisi rekap laporan', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    Keluarga::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('laporan.export', ['modul' => 'kemiskinan', 'format' => 'pdf']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

it('membatasi isi export csv sesuai filter kecamatan dan desa yang dikirim', function () {
    $user = userLaporan([Permission::KemiskinanView->value, Permission::Export->value]);
    $sesuai = Keluarga::factory()->create(['nama_kepala_keluarga' => 'Keluarga Sesuai']);
    Keluarga::factory()->create(['nama_kepala_keluarga' => 'Keluarga Lain']);

    $response = $this->actingAs($user)->get(route('laporan.export', [
        'modul' => 'kemiskinan',
        'format' => 'csv',
        'kecamatan_id' => $sesuai->kecamatan_id,
        'desa_kelurahan_id' => $sesuai->desa_kelurahan_id,
        'status_data' => '',
        'dari' => '',
        'sampai' => '',
    ]));

    $isi = $response->streamedContent();

    expect($isi)->toContain('Keluarga Sesuai')->not->toContain('Keluarga Lain');
});
