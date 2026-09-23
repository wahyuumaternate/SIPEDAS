<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_BARU = ['dalam_verifikasi', 'valid', 'tidak_valid', 'perlu_perbaikan'];

    /**
     * Status disederhanakan menjadi: dalam_verifikasi (default), valid, tidak_valid, perlu_perbaikan.
     */
    public function up(): void
    {
        foreach (['keluargas' => 'status_data', 'anaks' => 'status_data', 'verifikasi_kemiskinans' => 'status', 'verifikasi_stuntings' => 'status'] as $tabel => $kolom) {
            DB::table($tabel)->whereIn($kolom, ['draft', 'dikirim'])->update([$kolom => 'dalam_verifikasi']);
            DB::table($tabel)->where($kolom, 'duplikat')->update([$kolom => 'tidak_valid']);
        }

        foreach (['keluargas', 'anaks'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->enum('status_data', self::STATUS_BARU)->default('dalam_verifikasi')->change();
            });
        }

        foreach (['verifikasi_kemiskinans', 'verifikasi_stuntings'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->enum('status', self::STATUS_BARU)->change();
            });
        }
    }

    public function down(): void
    {
        $lama = ['draft', 'dikirim', 'dalam_verifikasi', 'perlu_perbaikan', 'valid', 'tidak_valid', 'duplikat'];

        foreach (['keluargas', 'anaks'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) use ($lama) {
                $table->enum('status_data', $lama)->default('draft')->change();
            });
        }

        foreach (['verifikasi_kemiskinans', 'verifikasi_stuntings'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) use ($lama) {
                $table->enum('status', $lama)->change();
            });
        }
    }
};
