<?php

use App\Models\Kecamatan;
use App\Models\Role;
use App\Models\SasaranStunting;
use App\Models\User;
use App\Permission;

function userSasaran(array $permissions): User
{
    $role = Role::factory()->create(['slug' => fake()->unique()->slug(2), 'hak_akses' => $permissions]);

    return User::factory()->create(['role_id' => $role->id]);
}

it('menampilkan formulir data sasaran per kecamatan untuk tahun terpilih', function () {
    $user = userSasaran([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create(['nama' => 'Pulau Hiri']);
    SasaranStunting::factory()->create(['kecamatan_id' => $kecamatan->id, 'tahun' => 2025, 'penduduk' => 2860]);

    $this->actingAs($user)
        ->get(route('stunting.sasaran.index', ['tahun' => 2025]))
        ->assertOk()
        ->assertSee('Pulau Hiri')
        ->assertSee('value="2860"', false);
});

it('menyimpan data sasaran per kecamatan dan mengosongkan kolom yang tidak diisi', function () {
    $user = userSasaran([Permission::StuntingView->value, Permission::StuntingEdit->value]);
    $kecamatan = Kecamatan::factory()->create();

    $this->actingAs($user)
        ->put(route('stunting.sasaran.update'), [
            'tahun' => 2026,
            'sasaran' => [
                ['kecamatan_id' => $kecamatan->id, 'penduduk' => '9119', 'remaja_putri' => '1578', 'ibu_hamil' => ''],
            ],
        ])
        ->assertRedirect(route('stunting.sasaran.index', ['tahun' => 2026]));

    $sasaran = SasaranStunting::where('kecamatan_id', $kecamatan->id)->where('tahun', 2026)->sole();

    expect($sasaran->penduduk)->toBe(9119)
        ->and($sasaran->remaja_putri)->toBe(1578)
        ->and($sasaran->ibu_hamil)->toBeNull();
});

it('memperbarui data sasaran yang sudah ada tanpa membuat duplikat', function () {
    $user = userSasaran([Permission::StuntingView->value, Permission::StuntingEdit->value]);
    $sasaran = SasaranStunting::factory()->create(['tahun' => 2026, 'penduduk' => 100]);

    $this->actingAs($user)->put(route('stunting.sasaran.update'), [
        'tahun' => 2026,
        'sasaran' => [['kecamatan_id' => $sasaran->kecamatan_id, 'penduduk' => '200']],
    ]);

    expect(SasaranStunting::count())->toBe(1)
        ->and($sasaran->fresh()->penduduk)->toBe(200);
});

it('menolak penyimpanan data sasaran tanpa hak ubah stunting', function () {
    $user = userSasaran([Permission::StuntingView->value]);
    $kecamatan = Kecamatan::factory()->create();

    $this->actingAs($user)
        ->put(route('stunting.sasaran.update'), [
            'tahun' => 2026,
            'sasaran' => [['kecamatan_id' => $kecamatan->id, 'penduduk' => '10']],
        ])
        ->assertForbidden();

    expect(SasaranStunting::count())->toBe(0);
});

it('menolak angka sasaran negatif', function () {
    $user = userSasaran([Permission::StuntingView->value, Permission::StuntingEdit->value]);
    $kecamatan = Kecamatan::factory()->create();

    $this->actingAs($user)
        ->put(route('stunting.sasaran.update'), [
            'tahun' => 2026,
            'sasaran' => [['kecamatan_id' => $kecamatan->id, 'penduduk' => '-5']],
        ])
        ->assertSessionHasErrors('sasaran.0.penduduk');

    expect(SasaranStunting::count())->toBe(0);
});
