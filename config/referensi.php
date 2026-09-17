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
        'tidak_bekerja' => 'Tidak Bekerja',
        'buruh_harian' => 'Buruh Harian',
        'nelayan' => 'Nelayan',
        'petani' => 'Petani/Pekebun',
        'pedagang' => 'Pedagang/Wiraswasta',
        'karyawan_swasta' => 'Karyawan Swasta',
        'pns_tni_polri' => 'PNS/TNI/POLRI',
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

];
