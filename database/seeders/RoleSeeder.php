<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'nama' => 'Super Admin',
                'deskripsi' => 'Memiliki akses penuh ke seluruh fitur dan pengaturan sistem.',
                'hak_akses' => [
                    'dashboard',
                    'users',
                    'roles',
                    'keluarga',
                    'anak',
                    'dokumen_keluarga',
                    'dokumen_anak',
                    'pengukuran',
                    'verifikasi_kemiskinan',
                    'verifikasi_stunting',
                    'audit_logs',
                    'settings',
                ],
                'is_active' => true,
            ]
        );
    }
}