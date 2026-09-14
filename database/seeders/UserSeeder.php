<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'super-admin')->first();

        if (!$role) {
            $this->command->error('Role super-admin belum tersedia.');
            return;
        }

        User::updateOrCreate(
            [
                'username' => 'superadmin',
            ],
            [
                'role_id' => $role->id,
                'nama' => 'Super Admin',
                'email' => 'admin@example.com',
                'nomor_hp' => '081234567890',
                'password' => Hash::make('password'),
                'kecamatan_id' => null,
                'desa_kelurahan_id' => null,
                'status' => 'aktif',
            ]
        );

        $this->command->info('User Super Admin berhasil dibuat.');
    }
}