<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Role & hak akses sesuai PRD Bagian 7-8.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'super-admin',
                'nama' => 'Super Admin',
                'deskripsi' => 'Memiliki akses penuh ke seluruh fitur dan pengaturan sistem.',
                'hak_akses' => Permission::all(),
            ],
            [
                'slug' => 'admin-bappelitbangda',
                'nama' => 'Admin Bappelitbangda',
                'deskripsi' => 'Melihat seluruh data, melakukan verifikasi/validasi, dan membuat laporan.',
                'hak_akses' => [
                    Permission::KemiskinanView->value,
                    Permission::KemiskinanVerify->value,
                    Permission::StuntingView->value,
                    Permission::StuntingVerify->value,
                    Permission::AuditLogView->value,
                    Permission::Export->value,
                ],
            ],
            [
                'slug' => 'admin-kecamatan',
                'nama' => 'Admin Kecamatan',
                'deskripsi' => 'Memeriksa dan memverifikasi data di kecamatannya.',
                'hak_akses' => [
                    Permission::KemiskinanView->value,
                    Permission::KemiskinanVerify->value,
                    Permission::StuntingView->value,
                    Permission::StuntingVerify->value,
                ],
            ],
            [
                'slug' => 'admin-desa-kelurahan',
                'nama' => 'Admin Desa/Kelurahan',
                'deskripsi' => 'Memeriksa dan melakukan verifikasi awal data di desa/kelurahannya.',
                'hak_akses' => [
                    Permission::KemiskinanView->value,
                    Permission::KemiskinanVerify->value,
                    Permission::StuntingView->value,
                    Permission::StuntingVerify->value,
                ],
            ],
            [
                'slug' => 'petugas-pendata',
                'nama' => 'Petugas Pendata',
                'deskripsi' => 'Membuat, mengisi, dan mengirim data pendataan untuk diverifikasi.',
                'hak_akses' => [
                    Permission::KemiskinanView->value,
                    Permission::KemiskinanCreate->value,
                    Permission::KemiskinanEdit->value,
                    Permission::StuntingView->value,
                    Permission::StuntingCreate->value,
                    Permission::StuntingEdit->value,
                ],
            ],
            [
                'slug' => 'viewer-pimpinan',
                'nama' => 'Viewer/Pimpinan',
                'deskripsi' => 'Melihat dashboard, rekapitulasi, laporan, dan melakukan export sesuai hak akses. Tidak dapat membuat, mengubah, menghapus, atau memverifikasi data.',
                'hak_akses' => [
                    Permission::KemiskinanView->value,
                    Permission::StuntingView->value,
                    Permission::Export->value,
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'nama' => $role['nama'],
                    'deskripsi' => $role['deskripsi'],
                    'hak_akses' => $role['hak_akses'],
                    'is_active' => true,
                ]
            );
        }
    }
}
