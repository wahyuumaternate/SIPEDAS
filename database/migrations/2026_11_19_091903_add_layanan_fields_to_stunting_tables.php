<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Field layanan yang dibutuhkan indikator capaian Laporan Analisis Situasi stunting.
     */
    public function up(): void
    {
        Schema::table('anaks', function (Blueprint $table) {
            $table->boolean('memiliki_akta_kelahiran')->default(false)->after('nik_anak');
        });

        Schema::table('pengukurans', function (Blueprint $table) {
            // Status gizi berdasarkan BB/TB: gizi_buruk, gizi_kurang, gizi_baik, gizi_lebih.
            $table->string('status_gizi')->nullable()->after('hasil_kategori');
        });

        Schema::table('riwayat_kehamilans', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah_tablet_tambah_darah')->nullable()->after('konsumsi_tablet_tambah_darah');
            $table->boolean('pmt_kek')->default(false)->after('status_kek');
            $table->boolean('makanan_bergizi_seimbang')->default(false)->after('pmt_kek');
            $table->boolean('kb_pascapersalinan')->default(false)->after('makanan_bergizi_seimbang');
        });

        Schema::table('riwayat_kesehatans', function (Blueprint $table) {
            $table->boolean('tata_laksana_gizi_buruk')->default(false)->after('akses_pelayanan_kesehatan');
            $table->boolean('tambahan_asupan_gizi')->default(false)->after('tata_laksana_gizi_buruk');
            $table->boolean('oralit_zinc')->default(false)->after('tambahan_asupan_gizi');
            $table->boolean('pneumonia')->default(false)->after('oralit_zinc');
            $table->boolean('obat_cacing')->default(false)->after('pneumonia');
            $table->boolean('memiliki_jkn')->default(false)->after('obat_cacing');
        });

        Schema::table('asi_mpasis', function (Blueprint $table) {
            $table->boolean('masih_asi')->default(false)->after('asi_eksklusif');
        });
    }

    public function down(): void
    {
        Schema::table('asi_mpasis', function (Blueprint $table) {
            $table->dropColumn('masih_asi');
        });

        Schema::table('riwayat_kesehatans', function (Blueprint $table) {
            $table->dropColumn(['tata_laksana_gizi_buruk', 'tambahan_asupan_gizi', 'oralit_zinc', 'pneumonia', 'obat_cacing', 'memiliki_jkn']);
        });

        Schema::table('riwayat_kehamilans', function (Blueprint $table) {
            $table->dropColumn(['jumlah_tablet_tambah_darah', 'pmt_kek', 'makanan_bergizi_seimbang', 'kb_pascapersalinan']);
        });

        Schema::table('pengukurans', function (Blueprint $table) {
            $table->dropColumn('status_gizi');
        });

        Schema::table('anaks', function (Blueprint $table) {
            $table->dropColumn('memiliki_akta_kelahiran');
        });
    }
};
