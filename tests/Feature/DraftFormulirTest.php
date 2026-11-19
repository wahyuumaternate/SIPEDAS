<?php

use App\Models\DesaKelurahan;
use App\Models\DraftFormulir;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Role;
use App\Models\User;
use App\Permission;

function userDraft(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menyimpan, mengambil, dan menghapus draft formulir milik pengguna', function () {
    $user = userDraft([Permission::KemiskinanView->value, Permission::KemiskinanCreate->value]);
    $isian = [['nomor_kk', '8271010101240009'], ['anggota[0][nama_lengkap]', 'Ahmad']];

    $this->actingAs($user)
        ->putJson(route('draft-formulir.update', 'kemiskinan.create'), ['isian' => $isian, 'disimpan_pada' => now()->toIso8601String()])
        ->assertOk();

    $this->actingAs($user)
        ->getJson(route('draft-formulir.show', 'kemiskinan.create'))
        ->assertOk()
        ->assertJsonPath('draft.isian', $isian);

    $this->actingAs($user)
        ->deleteJson(route('draft-formulir.destroy', 'kemiskinan.create'))
        ->assertOk();

    expect(DraftFormulir::count())->toBe(0);
});

it('memperbarui draft yang sama tanpa membuat duplikat dan tidak membocorkan draft pengguna lain', function () {
    $user = userDraft([Permission::StuntingCreate->value]);
    $lain = userDraft([Permission::StuntingCreate->value]);
    DraftFormulir::factory()->create(['user_id' => $lain->id, 'kunci' => 'stunting.create', 'isian' => [['nama_anak', 'Milik Orang Lain']]]);

    $this->actingAs($user)->putJson(route('draft-formulir.update', 'stunting.create'), ['isian' => [['nama_anak', 'A']]]);
    $this->actingAs($user)->putJson(route('draft-formulir.update', 'stunting.create'), ['isian' => [['nama_anak', 'B']]]);

    expect(DraftFormulir::where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)
        ->getJson(route('draft-formulir.show', 'stunting.create'))
        ->assertJsonPath('draft.isian', [['nama_anak', 'B']]);
});

it('menolak draft tanpa hak tambah atau ubah modul terkait', function (string $kunci) {
    $user = userDraft([Permission::KemiskinanView->value, Permission::StuntingView->value]);

    $this->actingAs($user)
        ->putJson(route('draft-formulir.update', $kunci), ['isian' => [['alamat', 'x']]])
        ->assertForbidden();
})->with(['kemiskinan.create', 'stunting.edit.5']);

it('menolak kunci draft yang tidak dikenal', function () {
    $user = userDraft([Permission::KemiskinanCreate->value]);

    $this->actingAs($user)
        ->putJson('/draft-formulir/pengguna.create', ['isian' => []])
        ->assertNotFound();
});

it('menghapus draft setelah data kemiskinan berhasil disimpan', function () {
    $user = User::factory()->create();
    $kecamatan = Kecamatan::factory()->create();
    $desa = DesaKelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);
    DraftFormulir::factory()->create(['user_id' => $user->id, 'kunci' => 'kemiskinan.create']);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), [
            'nomor_kk' => '8271010101240099',
            'alamat' => 'Jl. Merdeka No. 1',
            'rt' => '01',
            'rw' => '02',
            'kecamatan_id' => $kecamatan->id,
            'desa_kelurahan_id' => $desa->id,
            'anggota' => [[
                'nik' => '8271010101240098', 'nama_lengkap' => 'Ahmad', 'jenis_kelamin' => 'laki-laki',
                'tanggal_lahir' => '1985-01-01', 'hubungan_keluarga' => 'kepala_keluarga',
            ]],
            'kondisi_rumah' => ['status_kepemilikan' => 'milik_sendiri', 'kondisi_bangunan' => 'permanen'],
        ])
        ->assertSessionHas('draft_selesai', 'kemiskinan.create');

    expect(DraftFormulir::count())->toBe(0)
        ->and(Keluarga::count())->toBe(1);
});

it('mempertahankan draft bila penyimpanan gagal validasi', function () {
    $user = User::factory()->create();
    DraftFormulir::factory()->create(['user_id' => $user->id, 'kunci' => 'kemiskinan.create']);

    $this->actingAs($user)
        ->post(route('kemiskinan.store'), ['nomor_kk' => '123'])
        ->assertSessionHasErrors('nomor_kk');

    expect(DraftFormulir::count())->toBe(1);
});

it('mengingatkan draft yang belum disimpan pada daftar data', function () {
    $user = User::factory()->create();
    DraftFormulir::factory()->create(['user_id' => $user->id, 'kunci' => 'stunting.create']);

    $this->actingAs($user)
        ->get(route('stunting.index'))
        ->assertOk()
        ->assertSee('Anda memiliki draft pendataan yang belum disimpan');

    $this->actingAs($user)
        ->get(route('kemiskinan.index'))
        ->assertDontSee('Anda memiliki draft pendataan yang belum disimpan');
});

it('memasang skrip draft otomatis pada formulir pendataan', function (string $rute, string $kunci) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($rute))
        ->assertOk()
        ->assertSee('assets/js/draft-formulir.js', false)
        ->assertSee('kunci: "'.$kunci.'"', false);
})->with([['kemiskinan.create', 'kemiskinan.create'], ['stunting.create', 'stunting.create']]);
