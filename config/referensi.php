<?php

/**
 * Daftar nilai referensi (dropdown) untuk form pendataan Kemiskinan Ekstrem
 * dan Stunting (PRD Bagian 9-24). Sebelumnya dikelola lewat tabel `referensis`
 * yang bisa diubah admin lewat UI; sekarang jadi daftar tetap di kode karena
 * fitur Master Data > Referensi sudah dihapus.
 *
 * Format: 'kategori' => ['kode' => 'Label tampilan', ...]
 */
return [

    'status_perkawinan' => [
        'belum_kawin' => 'Belum Kawin',
        'kawin' => 'Kawin',
        'cerai_hidup' => 'Cerai Hidup',
        'cerai_mati' => 'Cerai Mati',
    ],

    // Hubungan dalam keluarga mengikuti isian Kartu Keluarga (Dukcapil).
    'hubungan_keluarga' => [
        'kepala_keluarga' => 'Kepala Keluarga',
        'suami' => 'Suami',
        'istri' => 'Istri',
        'anak' => 'Anak',
        'menantu' => 'Menantu',
        'cucu' => 'Cucu',
        'orang_tua' => 'Orang Tua',
        'mertua' => 'Mertua',
        'famili_lain' => 'Famili Lain',
        'pembantu' => 'Pembantu',
        'lainnya' => 'Lainnya',
    ],

    'pendidikan_terakhir' => [
        'tidak_sekolah' => 'Tidak/Belum Sekolah',
        'tidak_tamat_sd' => 'Tidak Tamat SD',
        'sd' => 'SD/Sederajat',
        'smp' => 'SMP/Sederajat',
        'sma' => 'SMA/Sederajat',
        'd1_d3' => 'Diploma I-III',
        's1' => 'Diploma IV/S1',
        's2_s3' => 'S2/S3',
    ],

    'status_pekerjaan' => [
        'tidak_bekerja' => 'Belum/Tidak Bekerja',
        'pelajar' => 'Pelajar/Mahasiswa',
        'mengurus_rumah_tangga' => 'Mengurus Rumah Tangga',
        'buruh_harian' => 'Buruh Harian',
        'nelayan' => 'Nelayan',
        'petani' => 'Petani/Pekebun',
        'pedagang' => 'Pedagang/Wiraswasta',
        'tukang' => 'Tukang/Pekerja Bangunan',
        'sopir_ojek' => 'Sopir/Ojek',
        'karyawan_swasta' => 'Karyawan Swasta',
        'pns_tni_polri' => 'PNS/TNI/POLRI',
        'pensiunan' => 'Pensiunan',
        'lainnya' => 'Lainnya',
    ],

    'jenis_atap' => [
        'seng' => 'Seng',
        'genteng' => 'Genteng',
        'asbes' => 'Asbes',
        'rumbia' => 'Rumbia/Daun',
        'lainnya' => 'Lainnya',
    ],

    'jenis_dinding' => [
        'tembok' => 'Tembok/Beton',
        'kayu' => 'Kayu/Papan',
        'bambu' => 'Bambu',
        'setengah_tembok' => 'Setengah Tembok',
        'lainnya' => 'Lainnya',
    ],

    'jenis_lantai' => [
        'keramik' => 'Keramik/Ubin',
        'semen' => 'Semen/Plester',
        'tanah' => 'Tanah',
        'kayu' => 'Kayu/Papan',
        'lainnya' => 'Lainnya',
    ],

    'sumber_listrik' => [
        'pln_meteran' => 'PLN Meteran',
        'pln_non_meteran' => 'PLN Non-Meteran',
        'genset' => 'Genset/Swadaya',
        'tidak_ada' => 'Tidak Ada Listrik',
    ],

    'sumber_air' => [
        'pdam' => 'PDAM/Perpipaan',
        'sumur_bor' => 'Sumur Bor/Pompa',
        'sumur_gali' => 'Sumur Gali',
        'mata_air' => 'Mata Air',
        'air_hujan' => 'Air Hujan',
        'beli' => 'Beli/Isi Ulang',
    ],

    'pengelolaan_sampah' => [
        'diangkut_petugas' => 'Diangkut Petugas',
        'dibakar' => 'Dibakar',
        'ditimbun' => 'Ditimbun',
        'dibuang_sembarangan' => 'Dibuang Sembarangan',
        'lainnya' => 'Lainnya',
    ],

    'jenis_aset' => [
        'rumah' => 'Rumah',
        'tanah' => 'Tanah',
        'sawah' => 'Sawah',
        'kebun' => 'Kebun',
        'sepeda_motor' => 'Sepeda Motor',
        'mobil' => 'Mobil',
        'ternak' => 'Ternak',
        'usaha' => 'Usaha',
        'peralatan_usaha' => 'Peralatan Usaha',
        'tabungan' => 'Tabungan',
        'lainnya' => 'Aset Lainnya',
    ],

    'jenis_jamban' => [
        'leher_angsa' => 'Leher Angsa',
        'cemplung' => 'Cemplung',
        'jamban_umum' => 'Jamban Umum/Bersama',
        'tidak_ada' => 'Tidak Ada',
    ],

    // ===== Kemiskinan: anggota keluarga =====
    'status_sekolah' => [
        'belum_sekolah' => 'Belum Sekolah',
        'sedang_sekolah' => 'Sedang Sekolah',
        'putus_sekolah' => 'Putus Sekolah',
        'tamat_sekolah' => 'Tamat Sekolah',
        'tidak_pernah_sekolah' => 'Tidak Pernah Sekolah',
    ],

    'penyakit_kronis' => [
        'hipertensi' => 'Hipertensi (Darah Tinggi)',
        'diabetes' => 'Diabetes Melitus',
        'jantung' => 'Penyakit Jantung',
        'stroke' => 'Stroke',
        'tbc' => 'TBC (Tuberkulosis)',
        'asma_ppok' => 'Asma/PPOK',
        'ginjal' => 'Gagal Ginjal',
        'kanker' => 'Kanker',
        'hiv' => 'HIV/AIDS',
        'gangguan_jiwa' => 'Gangguan Jiwa',
        'lainnya' => 'Lainnya',
    ],

    // ===== Kemiskinan: ekonomi & rumah =====
    'bahan_bakar_memasak' => [
        'listrik' => 'Kompor Listrik',
        'lpg_3kg' => 'Gas LPG 3 kg',
        'lpg_besar' => 'Gas LPG 5,5 kg/12 kg',
        'minyak_tanah' => 'Kompor Minyak Tanah',
        'kayu_bakar' => 'Tungku Kayu Bakar',
        'arang' => 'Arang/Briket',
        'tidak_memasak' => 'Tidak Memasak di Rumah',
        'lainnya' => 'Lainnya',
    ],

    'jenis_septic_tank' => [
        'tangki_septik' => 'Tangki Septik Pribadi',
        'ipal_komunal' => 'IPAL/Septik Tank Komunal',
        'lubang_tanah' => 'Lubang Tanah/Cubluk',
        'langsung_dibuang' => 'Langsung ke Sungai/Laut/Kolam',
        'tidak_ada' => 'Tidak Ada',
    ],

    'daya_listrik' => [
        '450' => '450 VA',
        '900' => '900 VA',
        '1300' => '1.300 VA',
        '2200' => '2.200 VA',
        '3500' => '3.500 VA ke atas',
        'tanpa_meteran' => 'Tanpa Meteran (menyalur)',
    ],

    // ===== Kemiskinan: pendidikan & kesehatan =====
    'bantuan_pendidikan' => [
        'prasarana_sekolah' => 'Dukungan Prasarana Anak Sekolah (seragam, buku, tas, sepatu)',
        'pip_kip' => 'Program Indonesia Pintar (PIP/KIP)',
        'beasiswa' => 'Beasiswa',
        'lainnya' => 'Lainnya',
    ],

    // ===== Stunting =====
    'status_bb_u' => [
        'bb_sangat_kurang' => 'Berat Badan Sangat Kurang',
        'bb_kurang' => 'Berat Badan Kurang',
        'bb_normal' => 'Berat Badan Normal',
        'risiko_bb_lebih' => 'Risiko Berat Badan Lebih',
    ],

    'alasan_nonaktif_stunting' => [
        'sudah_tidak_stunting' => 'Sudah Tidak Stunting',
        'usia_lewat' => 'Usia Lewat 59 Bulan',
        'pindah_domisili' => 'Pindah Domisili',
        'meninggal' => 'Meninggal Dunia',
    ],

];
