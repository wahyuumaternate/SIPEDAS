<?php

namespace App;

enum Permission: string
{
    case KemiskinanView = 'kemiskinan.view';
    case KemiskinanCreate = 'kemiskinan.create';
    case KemiskinanEdit = 'kemiskinan.edit';
    case KemiskinanDelete = 'kemiskinan.delete';
    case KemiskinanVerify = 'kemiskinan.verify';

    case StuntingView = 'stunting.view';
    case StuntingCreate = 'stunting.create';
    case StuntingEdit = 'stunting.edit';
    case StuntingDelete = 'stunting.delete';
    case StuntingVerify = 'stunting.verify';

    case MasterManage = 'master.manage';
    case UserManage = 'user.manage';
    case AuditLogView = 'audit_log.view';
    case Export = 'export';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::KemiskinanView => 'Lihat data Kemiskinan',
            self::KemiskinanCreate => 'Membuat data Kemiskinan',
            self::KemiskinanEdit => 'Mengubah data Kemiskinan',
            self::KemiskinanDelete => 'Menghapus data Kemiskinan',
            self::KemiskinanVerify => 'Memverifikasi data Kemiskinan',
            self::StuntingView => 'Lihat data Stunting',
            self::StuntingCreate => 'Membuat data Stunting',
            self::StuntingEdit => 'Mengubah data Stunting',
            self::StuntingDelete => 'Menghapus data Stunting',
            self::StuntingVerify => 'Memverifikasi data Stunting',
            self::MasterManage => 'Mengelola Master Data (Wilayah)',
            self::UserManage => 'Mengelola Pengguna, Role & Audit Log',
            self::AuditLogView => 'Melihat Audit Log',
            self::Export => 'Export Laporan',
        };
    }

    /**
     * Kelompok permission untuk tampilan form, supaya tidak jadi daftar checkbox datar.
     *
     * @return array<string, array<int, self>>
     */
    public static function groups(): array
    {
        return [
            'Kemiskinan Ekstrem' => [
                self::KemiskinanView, self::KemiskinanCreate, self::KemiskinanEdit,
                self::KemiskinanDelete, self::KemiskinanVerify,
            ],
            'Stunting' => [
                self::StuntingView, self::StuntingCreate, self::StuntingEdit,
                self::StuntingDelete, self::StuntingVerify,
            ],
            'Administrasi & Lainnya' => [
                self::MasterManage, self::UserManage, self::AuditLogView, self::Export,
            ],
        ];
    }
}
