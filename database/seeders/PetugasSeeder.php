<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        self::petugas();
    }

    public static function petugas(): User
    {
        return User::firstOrCreate(
            ['username' => 'petugas'],
            [
                'role_id' => Role::where('slug', 'petugas-pendata')->value('id'),
                'nama' => 'Petugas Pendata',
                'email' => 'petugas@example.com',
                'nomor_hp' => '081234567891',
                'password' => Hash::make('password'),
                'status' => 'aktif',
            ]
        );
    }
}
