<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi (Bahasa Indonesia)
|--------------------------------------------------------------------------
|
| Pesan bawaan aturan validasi Laravel. Nama field ditampilkan lewat
| :attribute; label yang lebih ramah dapat ditambahkan di bagian 'attributes'.
|
*/

return [

    'accepted' => ':Attribute harus disetujui.',
    'accepted_if' => ':Attribute harus disetujui jika :other bernilai :value.',
    'active_url' => ':Attribute harus berupa URL yang valid.',
    'after' => ':Attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':Attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':Attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':Attribute tidak valid.',
    'array' => ':Attribute harus berupa daftar.',
    'ascii' => ':Attribute hanya boleh berisi karakter alfanumerik dan simbol satu byte.',
    'before' => ':Attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':Attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':Attribute harus berisi antara :min sampai :max item.',
        'file' => 'Ukuran :attribute harus antara :min sampai :max kilobyte.',
        'numeric' => ':Attribute harus bernilai antara :min sampai :max.',
        'string' => ':Attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'can' => ':Attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => ':Attribute tidak memuat nilai yang diwajibkan.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':Attribute harus berupa tanggal yang valid.',
    'date_equals' => ':Attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':Attribute harus sesuai format :format.',
    'decimal' => ':Attribute harus memiliki :decimal angka desimal.',
    'declined' => ':Attribute harus ditolak.',
    'declined_if' => ':Attribute harus ditolak jika :other bernilai :value.',
    'different' => ':Attribute dan :other harus berbeda.',
    'digits' => ':Attribute harus terdiri dari :digits digit angka.',
    'digits_between' => ':Attribute harus terdiri dari :min sampai :max digit angka.',
    'dimensions' => 'Dimensi gambar :attribute tidak valid.',
    'distinct' => ':Attribute memiliki nilai yang duplikat.',
    'doesnt_contain' => ':Attribute tidak boleh memuat salah satu dari: :values.',
    'doesnt_end_with' => ':Attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => ':Attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'encoding' => ':Attribute harus menggunakan encoding :encoding.',
    'ends_with' => ':Attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'extensions' => ':Attribute harus berekstensi salah satu dari: :values.',
    'file' => ':Attribute harus berupa file.',
    'filled' => ':Attribute wajib diisi.',
    'gt' => [
        'array' => ':Attribute harus berisi lebih dari :value item.',
        'file' => 'Ukuran :attribute harus lebih besar dari :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari :value.',
        'string' => ':Attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':Attribute harus berisi :value item atau lebih.',
        'file' => 'Ukuran :attribute harus lebih besar dari atau sama dengan :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':Attribute harus terdiri dari :value karakter atau lebih.',
    ],
    'hex_color' => ':Attribute harus berupa warna heksadesimal yang valid.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'in_array' => ':Attribute harus ada di dalam :other.',
    'in_array_keys' => ':Attribute harus memuat setidaknya salah satu kunci berikut: :values.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'ip' => ':Attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':Attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':Attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':Attribute harus berupa teks JSON yang valid.',
    'list' => ':Attribute harus berupa daftar.',
    'lowercase' => ':Attribute harus berupa huruf kecil.',
    'lt' => [
        'array' => ':Attribute harus berisi kurang dari :value item.',
        'file' => 'Ukuran :attribute harus kurang dari :value kilobyte.',
        'numeric' => ':Attribute harus kurang dari :value.',
        'string' => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':Attribute tidak boleh berisi lebih dari :value item.',
        'file' => 'Ukuran :attribute harus kurang dari atau sama dengan :value kilobyte.',
        'numeric' => ':Attribute harus kurang dari atau sama dengan :value.',
        'string' => ':Attribute harus terdiri dari :value karakter atau kurang.',
    ],
    'mac_address' => ':Attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':Attribute tidak boleh berisi lebih dari :max item.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobyte.',
        'numeric' => ':Attribute tidak boleh lebih dari :max.',
        'string' => ':Attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => ':Attribute tidak boleh lebih dari :max digit.',
    'mimes' => ':Attribute harus berupa file bertipe: :values.',
    'mimetypes' => ':Attribute harus berupa file bertipe: :values.',
    'min' => [
        'array' => ':Attribute harus berisi minimal :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal terdiri dari :min karakter.',
    ],
    'min_digits' => ':Attribute minimal terdiri dari :min digit.',
    'missing' => ':Attribute tidak boleh ada.',
    'missing_if' => ':Attribute tidak boleh ada jika :other bernilai :value.',
    'missing_unless' => ':Attribute tidak boleh ada kecuali :other bernilai :value.',
    'missing_with' => ':Attribute tidak boleh ada jika :values diisi.',
    'missing_with_all' => ':Attribute tidak boleh ada jika :values diisi.',
    'multiple_of' => ':Attribute harus kelipatan dari :value.',
    'not_in' => ':Attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus memuat minimal satu huruf.',
        'mixed' => ':Attribute harus memuat minimal satu huruf besar dan satu huruf kecil.',
        'numbers' => ':Attribute harus memuat minimal satu angka.',
        'symbols' => ':Attribute harus memuat minimal satu simbol.',
        'uncompromised' => ':Attribute yang diberikan pernah muncul dalam kebocoran data. Silakan gunakan :attribute lain.',
    ],
    'present' => ':Attribute wajib ada.',
    'present_if' => ':Attribute wajib ada jika :other bernilai :value.',
    'present_unless' => ':Attribute wajib ada kecuali :other bernilai :value.',
    'present_with' => ':Attribute wajib ada jika :values ada.',
    'present_with_all' => ':Attribute wajib ada jika :values ada.',
    'prohibited' => ':Attribute tidak boleh diisi.',
    'prohibited_if' => ':Attribute tidak boleh diisi jika :other bernilai :value.',
    'prohibited_if_accepted' => ':Attribute tidak boleh diisi jika :other disetujui.',
    'prohibited_if_declined' => ':Attribute tidak boleh diisi jika :other ditolak.',
    'prohibited_unless' => ':Attribute tidak boleh diisi kecuali :other ada di :values.',
    'prohibits' => ':Attribute melarang :other untuk diisi.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'required_array_keys' => ':Attribute harus memuat isian untuk: :values.',
    'required_if' => ':Attribute wajib diisi jika :other bernilai :value.',
    'required_if_accepted' => ':Attribute wajib diisi jika :other disetujui.',
    'required_if_declined' => ':Attribute wajib diisi jika :other ditolak.',
    'required_unless' => ':Attribute wajib diisi kecuali :other ada di :values.',
    'required_with' => ':Attribute wajib diisi jika :values diisi.',
    'required_with_all' => ':Attribute wajib diisi jika :values diisi.',
    'required_without' => ':Attribute wajib diisi jika :values tidak diisi.',
    'required_without_all' => ':Attribute wajib diisi jika tidak ada satu pun dari :values yang diisi.',
    'same' => ':Attribute harus sama dengan :other.',
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'numeric' => ':Attribute harus bernilai :size.',
        'string' => ':Attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => ':Attribute harus diawali dengan salah satu dari: :values.',
    'string' => ':Attribute harus berupa teks.',
    'timezone' => ':Attribute harus berupa zona waktu yang valid.',
    'unique' => ':Attribute sudah digunakan.',
    'uploaded' => ':Attribute gagal diunggah.',
    'uppercase' => ':Attribute harus berupa huruf besar.',
    'url' => ':Attribute harus berupa URL yang valid.',
    'ulid' => ':Attribute harus berupa ULID yang valid.',
    'uuid' => ':Attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Khusus
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'pesan-khusus',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut
    |--------------------------------------------------------------------------
    |
    | Label field yang dipakai menggantikan :attribute, mis. "nomor_kk"
    | menjadi "Nomor KK". Form request dapat menimpanya lewat attributes().
    |
    */

    'attributes' => [
        // Umum & akun
        'nama' => 'nama',
        'name' => 'nama',
        'username' => 'nama pengguna',
        'email' => 'email',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'current_password' => 'kata sandi saat ini',
        'nomor_hp' => 'nomor HP',
        'role_id' => 'peran',
        'status' => 'status',
        'kode' => 'kode',
        'is_active' => 'status aktif',
        'hak_akses' => 'hak akses',
        'slug' => 'slug',

        // Wilayah
        'kecamatan_id' => 'kecamatan',
        'desa_kelurahan_id' => 'desa/kelurahan',
        'jenis' => 'jenis',

        // Filter & periode
        'dari' => 'tanggal awal',
        'sampai' => 'tanggal akhir',
        'tahun' => 'tahun',
        'periode' => 'periode',
        'status_data' => 'status data',
        'catatan' => 'catatan',

        // Kemiskinan
        'nomor_kk' => 'Nomor KK',
        'alamat' => 'alamat',
        'rt' => 'RT',
        'rw' => 'RW',
        'desil' => 'desil DTSEN',
        'status_dtsen' => 'status DTSEN',
        'anggota' => 'anggota keluarga',
        'anggota.*.nik' => 'NIK anggota',
        'anggota.*.nama_lengkap' => 'nama lengkap anggota',
        'anggota.*.jenis_kelamin' => 'jenis kelamin anggota',
        'anggota.*.tempat_lahir' => 'tempat lahir anggota',
        'anggota.*.tanggal_lahir' => 'tanggal lahir anggota',
        'anggota.*.hubungan_keluarga' => 'hubungan keluarga',
        'anggota.*.status_perkawinan' => 'status perkawinan anggota',
        'anggota.*.pendidikan_terakhir' => 'pendidikan terakhir anggota',
        'anggota.*.status_pekerjaan' => 'status pekerjaan anggota',
        'aset.*.jenis_aset' => 'jenis aset',
        'aset.*.jumlah' => 'jumlah aset',
        'aset.*.status_kepemilikan' => 'status kepemilikan aset',
        'aset.*.perkiraan_nilai' => 'perkiraan nilai aset',
        'program.*.nama_program' => 'nama program',
        'program.*.status_penerima' => 'status penerima program',
        'program.*.tahun_menerima' => 'tahun menerima program',
        'kondisi_rumah.status_kepemilikan' => 'status kepemilikan rumah',
        'kondisi_rumah.kondisi_bangunan' => 'kondisi bangunan',

        // Stunting
        'nik_anak' => 'NIK anak',
        'nama_anak' => 'nama anak',
        'jenis_kelamin' => 'jenis kelamin',
        'tempat_lahir' => 'tempat lahir',
        'tanggal_lahir' => 'tanggal lahir',
        'nomor_hp_orang_tua' => 'nomor HP orang tua',
        'orang_tua.nik_ayah' => 'NIK ayah',
        'orang_tua.nik_ibu' => 'NIK ibu',
        'orang_tua.tanggal_lahir_ayah' => 'tanggal lahir ayah',
        'orang_tua.tanggal_lahir_ibu' => 'tanggal lahir ibu',
        'orang_tua.penghasilan_ayah' => 'penghasilan ayah',
        'orang_tua.penghasilan_ibu' => 'penghasilan ibu',
        'pengukuran.tanggal_pengukuran' => 'tanggal pengukuran',
        'pengukuran.berat_badan' => 'berat badan',
        'pengukuran.panjang_tinggi_badan' => 'panjang/tinggi badan',
        'tanggal_pengukuran' => 'tanggal pengukuran',
        'berat_badan' => 'berat badan',
        'panjang_tinggi_badan' => 'panjang/tinggi badan',
        'lingkar_kepala' => 'lingkar kepala',
        'lingkar_lengan_atas' => 'lingkar lengan atas',
        'hasil_kategori' => 'hasil kategori',
        'status_gizi' => 'status gizi',
        'riwayat_kehamilan.jumlah_tablet_tambah_darah' => 'jumlah tablet tambah darah',
        'riwayat_kehamilan.lila' => 'LILA ibu',
        'riwayat_kelahiran.berat_badan_lahir' => 'berat badan lahir',
        'riwayat_kelahiran.panjang_badan_lahir' => 'panjang badan lahir',

        // Dokumen
        'dokumen' => 'dokumen',
        'dokumen.*.file' => 'file dokumen',
        'dokumen.*.jenis_dokumentasi' => 'jenis dokumentasi',
        'file' => 'file',
        'jenis_dokumentasi' => 'jenis dokumentasi',
        'keterangan' => 'keterangan',
    ],

];
