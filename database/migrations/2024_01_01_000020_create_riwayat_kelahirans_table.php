<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Form Riwayat Kelahiran (PRD Bagian 20). */
    public function up(): void
    {
        Schema::create('riwayat_kelahirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->unique()->constrained('anaks')->cascadeOnDelete();

            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');
            $table->enum('penolong_persalinan', ['dokter', 'bidan', 'perawat', 'dukun', 'lainnya'])->nullable();
            $table->enum('cara_persalinan', ['normal', 'caesar', 'lainnya'])->nullable();
            $table->decimal('berat_badan_lahir', 5, 2)->nullable(); // kg
            $table->decimal('panjang_badan_lahir', 5, 2)->nullable(); // cm
            $table->boolean('status_prematur')->default(false);
            $table->boolean('status_bblr')->default(false); // berat badan lahir rendah
            $table->boolean('imd')->default(false); // inisiasi menyusu dini
            $table->string('kondisi_bayi_saat_lahir')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelahirans');
    }
};
