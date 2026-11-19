<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Draft isian formulir pendataan (kemiskinan/stunting) yang tersimpan otomatis saat diketik,
     * agar isian tidak hilang bila koneksi terputus atau peramban tertutup.
     */
    public function up(): void
    {
        Schema::create('draft_formulirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kunci', 60); // mis. kemiskinan.create, stunting.edit.12
            $table->json('isian');
            $table->timestamp('disimpan_pada');
            $table->timestamps();

            $table->unique(['user_id', 'kunci']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_formulirs');
    }
};
