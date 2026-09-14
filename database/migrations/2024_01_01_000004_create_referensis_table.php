<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel referensi generik untuk pilihan dropdown yang dapat
     * dikelola Super Admin, misalnya: status_perkawinan,
     * pendidikan_terakhir, jenis_pekerjaan, jenis_aset,
     * status_kepemilikan_rumah, jenis_atap, jenis_dinding, dst.
     */
    public function up(): void
    {
        Schema::create('referensis', function (Blueprint $table) {
            $table->id();
            $table->string('kategori')->index(); // contoh: 'jenis_aset'
            $table->string('kode');
            $table->string('nilai');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kategori', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referensis');
    }
};
