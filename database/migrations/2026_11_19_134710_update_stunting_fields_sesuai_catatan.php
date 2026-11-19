<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penyesuaian modul stunting sesuai catatan pengembangan: NIK anak tidak wajib, anak ke-,
     * status pemantauan aktif/nonaktif, dan status berat badan menurut umur (BB/U).
     */
    public function up(): void
    {
        Schema::table('anaks', function (Blueprint $table) {
            $table->string('nik_anak', 16)->nullable()->change();
            $table->unsignedTinyInteger('anak_ke')->nullable()->after('jenis_kelamin');
            $table->enum('status_pemantauan', ['aktif', 'nonaktif'])->default('aktif')->after('status_data');
            $table->string('alasan_nonaktif')->nullable()->after('status_pemantauan');
            $table->date('tanggal_nonaktif')->nullable()->after('alasan_nonaktif');

            $table->index('status_pemantauan');
        });

        Schema::table('pengukurans', function (Blueprint $table) {
            // Berat badan menurut umur: bb_sangat_kurang, bb_kurang, bb_normal, risiko_bb_lebih.
            $table->string('status_bb_u')->nullable()->after('status_gizi');
        });
    }

    public function down(): void
    {
        Schema::table('pengukurans', function (Blueprint $table) {
            $table->dropColumn('status_bb_u');
        });

        Schema::table('anaks', function (Blueprint $table) {
            $table->dropIndex(['status_pemantauan']);
            $table->dropColumn(['anak_ke', 'status_pemantauan', 'alasan_nonaktif', 'tanggal_nonaktif']);
            $table->string('nik_anak', 16)->nullable(false)->change();
        });
    }
};
