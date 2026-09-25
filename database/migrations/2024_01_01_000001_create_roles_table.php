<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master role pengguna sistem.
     * Role: Super Admin, Admin Bappelitbangda, Admin Kecamatan,
     * Admin Desa/Kelurahan, Petugas Pendata, Viewer/Pimpinan.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->json('hak_akses')->nullable(); // daftar permission granular
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
