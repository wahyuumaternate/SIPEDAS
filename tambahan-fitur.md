# Rekomendasi Tambahan Fitur — BINTANG TERNATE

Analisis fitur yang sudah ada dan rekomendasi pengembangan agar aplikasi lebih **compact, efisien, modern, dan mudah digunakan**, tanpa menambah fitur yang tidak diperlukan.

---

## A. Kondisi Saat Ini

| Area | Yang tersedia | Catatan |
|---|---|---|
| **Kemiskinan** | Form 8 langkah (identitas, anggota, ekonomi, rumah, aset, sosial, program, dokumen), desil DTSEN, verifikasi | Kepala keluarga & ringkasan sosial-ekonomi sudah otomatis dari data anggota |
| **Stunting** | Form 9 langkah, pengukuran berulang, isi otomatis dari No. KK, Data Sasaran per tahun | **Kategori stunting masih dipilih manual** |
| **Verifikasi** | Ada di **3 menu**: Kemiskinan › Verifikasi, Stunting › Verifikasi, Verifikasi & Validasi | Duplikasi |
| **Laporan** | Tabulasi DTSEN, tabulasi Analisis Situasi + grafik radar, export PDF | Perhitungan stunting memuat semua data anak ke memori |
| **Master** | Wilayah, Petugas, Role & hak akses (14 permission) | User punya kolom `kecamatan_id`/`desa_kelurahan_id`, **tapi belum dipakai** untuk membatasi data |
| **Pendukung** | Dashboard (grafik batang CSS), Audit Log, draft otomatis, pesan Bahasa Indonesia | Paket `maatwebsite/excel` terpasang tapi tidak dipakai |

### Tiga temuan terpenting

1. **Data belum dibatasi per wilayah.** Admin Kecamatan, Admin Desa, dan Petugas dapat melihat **seluruh data kota**.
2. **Status stunting ditentukan manual**, sehingga rawan tidak konsisten dengan standar antropometri.
3. **Tidak ada jalur impor**, padahal data DTSEN berisi ±66.000 KK. Input manual satu per satu tidak realistis.

---

## B. Rekomendasi

### 1. Keamanan & Akses Data

#### 1.1 Pembatasan Data per Wilayah
| | |
|---|---|
| **Prioritas** | 🔴 High |
| **Kompleksitas** | Sedang |
| **Deskripsi** | Setiap query (daftar, dashboard, laporan, verifikasi, pencarian KK) otomatis dibatasi ke kecamatan/desa milik user. Super Admin, Bappelitbangda, dan Pimpinan tetap melihat semua. |
| **Manfaat** | Admin wilayah hanya melihat datanya sendiri, dan daftar data jadi lebih ringkas. |
| **Alasan** | Data berisi NIK dan kondisi ekonomi warga (data pribadi menurut UU PDP). Kolomnya sudah ada di tabel user, jadi cukup ditambahkan satu *global scope*/*trait* yang dipakai ulang di semua modul. |

#### 1.2 Hapus "Hapus Akun" di Profil & Bersihkan Sisa Registrasi
| | |
|---|---|
| **Prioritas** | 🔴 High |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Hapus form hapus-akun mandiri (bawaan Breeze) beserta route/test registrasi yang sudah dimatikan. |
| **Manfaat** | Petugas tidak bisa menghapus akunnya sendiri, yang dapat memutus jejak audit atas data yang pernah diinput. |
| **Alasan** | Akun dikelola admin lewat Master Petugas (aktif/nonaktif). Sekaligus menghilangkan 3 test yang selalu gagal. |

---

### 2. Otomatisasi & Efisiensi

#### 2.1 Perhitungan Status Gizi Otomatis (Z-Score WHO)
| | |
|---|---|
| **Prioritas** | 🔴 High |
| **Kompleksitas** | Sedang |
| **Deskripsi** | *Hasil Kategori* (TB/U → stunting) dan *Status Gizi* (BB/TB) dihitung otomatis dari umur, jenis kelamin, berat, dan tinggi, mengikuti tabel standar antropometri anak (Permenkes No. 2/2020). Dua dropdown manual diganti dengan hasil yang tampil langsung. |
| **Manfaat** | Petugas cukup menimbang dan mengukur, hasilnya konsisten, dan prevalensi bisa dipertanggungjawabkan. |
| **Alasan** | Prevalensi adalah angka utama laporan. Kategori yang dipilih manual menjadi titik lemah terbesar validitas data. |

#### 2.2 Impor Data dari Excel (DTSEN & ePPGBM)
| | |
|---|---|
| **Prioritas** | 🔴 High |
| **Kompleksitas** | Sedang |
| **Deskripsi** | Unggah file Excel, lalu tampil pratinjau dengan validasi per baris (NIK, KK, wilayah, duplikat). Baris yang valid langsung disimpan; baris yang bermasalah diunduh kembali beserta keterangan kesalahannya. File besar diproses lewat *queue*. |
| **Manfaat** | Puluhan ribu KK masuk dalam hitungan menit, dan petugas lapangan cukup melengkapi/memperbarui data. |
| **Alasan** | Tanpa impor, tabulasi DTSEN tidak akan mendekati angka resmi. `maatwebsite/excel` sudah terpasang. |

#### 2.3 Satukan Menu Verifikasi
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Satu halaman **Verifikasi** dengan tab Kemiskinan \| Stunting, filter wilayah/status, dan aksi massal (setujui beberapa data sekaligus). Submenu Verifikasi di masing-masing modul dihapus. |
| **Manfaat** | Sidebar berkurang dua item, dan verifikator punya satu antrean kerja. |
| **Alasan** | Tiga menu saat ini membuka antrean data yang sama. |

#### 2.4 Tautan Permanen Anak ↔ Keluarga
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Tambahkan kolom `keluarga_id` (dan `anggota_keluarga_id`) pada data anak saat dipilih dari KK. Detail keluarga menampilkan anak stunting di dalamnya, dan sebaliknya. |
| **Manfaat** | Bisa dibuat tabulasi silang, mis. "anak stunting di keluarga desil 1–2", yang sangat relevan untuk penentuan sasaran intervensi. |
| **Alasan** | Saat ini data hanya disalin sekali, jadi kedua modul tidak saling terhubung. |

---

### 3. Dashboard & Analitik

#### 3.1 Dashboard per Peran (Fokus pada Tindakan)
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Sedang |
| **Deskripsi** | Isi dashboard menyesuaikan peran:<br>• **Petugas:** data saya bulan ini, data "Perlu Perbaikan", draft tertunda, anak yang jatuh tempo pengukuran ulang.<br>• **Verifikator:** jumlah antrean, umur antrean (hari), tombol "mulai verifikasi".<br>• **Pimpinan:** 4 KPI (prevalensi, KK desil 1–2, % data valid, capaian layanan), tren bulanan, 5 kelurahan dengan prevalensi tertinggi. |
| **Manfaat** | Setiap user langsung tahu apa yang perlu dikerjakan. |
| **Alasan** | Dashboard saat ini (623 baris view) menampilkan semua hal kepada semua orang. Chart.js sudah tersedia di proyek. |

#### 3.2 Grafik Pertumbuhan Anak (KMS Digital)
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Di halaman detail anak, tampilkan grafik TB/U dan BB/U dari riwayat pengukuran beserta garis standar WHO (−3, −2, median). |
| **Manfaat** | Petugas dan nakes bisa melihat apakah intervensi berhasil. |
| **Alasan** | Riwayat pengukuran sudah tersimpan tetapi hanya tampil sebagai tabel. Bergantung pada rekomendasi 2.1. |

#### 3.3 Peta Sebaran per Kelurahan
| | |
|---|---|
| **Prioritas** | 🟢 Low |
| **Kompleksitas** | Sedang |
| **Deskripsi** | Peta choropleth Kota Ternate yang diwarnai menurut prevalensi stunting atau jumlah KK desil 1. Klik kelurahan untuk membuka tabulasinya. |
| **Manfaat** | Mudah dipahami pimpinan dan berguna untuk rembuk stunting. |
| **Alasan** | Bernilai untuk presentasi, tetapi butuh GeoJSON batas kelurahan. Kerjakan setelah data cukup lengkap. |

---

### 4. UI/UX & Pengalaman Pengguna

#### 4.1 Indikator Kelengkapan per Langkah Form
| | |
|---|---|
| **Prioritas** | 🔴 High |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Setiap tab form menampilkan tanda ✓ / ⚠ / jumlah isian wajib yang belum terisi, ditambah progress bar "Kelengkapan data 70%". Daftar Data mendapat filter "data belum lengkap". |
| **Manfaat** | Form 8–9 langkah terasa lebih ringan, dan petugas tahu persis apa yang kurang. |
| **Alasan** | Banyak field indikator baru belum terisi pada data lama. Indikator ini sekaligus mengarahkan pemutakhiran data. |

#### 4.2 Pencarian Cepat Global (NIK / No. KK / Nama)
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Satu kotak pencarian di topbar (shortcut `/`) yang mencari di kedua modul sekaligus dan langsung membuka halaman detail. |
| **Manfaat** | Verifikator dan admin tidak perlu berpindah menu dan filter. |
| **Alasan** | NIK dan No. KK adalah kunci kerja sehari-hari, sedangkan pencarian saat ini terpisah per modul. |

#### 4.3 Mode Lapangan (Mobile-First untuk Petugas)
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Sedang |
| **Deskripsi** | • Tombol navigasi besar menempel di bawah layar (Sebelumnya / Simpan / Selanjutnya).<br>• Keyboard angka untuk NIK/KK, input foto langsung dari kamera.<br>• Opsional: PWA (dapat dipasang di HP). Kondisi offline sudah ditangani draft otomatis. |
| **Manfaat** | Input di lapangan lebih cepat dan minim salah ketik. |
| **Alasan** | Petugas pendata adalah pengguna terbanyak dan umumnya memakai HP. |

#### 4.4 Konsistensi Komponen UI
| | |
|---|---|
| **Prioritas** | 🟢 Low |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Jadikan komponen Blade yang dipakai ulang: tabel data, badge status, filter wilayah (kecamatan → desa), dan pesan kosong. Komponen ini saat ini ditulis berulang di setiap view. |
| **Manfaat** | Tampilan seragam dan halaman baru lebih cepat dibuat. |
| **Alasan** | Script filter kecamatan→desa saja sudah tersalin di ±5 view. |

---

### 5. Alur Kerja & Notifikasi

#### 5.1 Notifikasi Dalam Aplikasi
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Ikon lonceng di topbar yang memberi tahu:<br>• petugas bila datanya **Perlu Perbaikan** (beserta catatan verifikator);<br>• verifikator bila ada data baru di wilayahnya.<br>Memakai notifikasi database bawaan Laravel, tanpa layanan eksternal. |
| **Manfaat** | Perbaikan data tidak tertunda karena petugas tidak tahu. |
| **Alasan** | Status "Perlu Perbaikan" saat ini hanya terlihat bila petugas membuka daftar data. |

#### 5.2 Jadwal Pengukuran Ulang
| | |
|---|---|
| **Prioritas** | 🟢 Low |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Anak yang pengukuran terakhirnya lebih dari 30 hari (balita stunting) atau lebih dari 90 hari (lainnya) masuk daftar "Perlu Diukur" di dashboard petugas. |
| **Manfaat** | Pemantauan pertumbuhan berjalan teratur. |
| **Alasan** | Indikator "dipantau pertumbuhan" di laporan bergantung pada pengukuran rutin. |

---

### 6. Skalabilitas & Performa

#### 6.1 Agregasi Laporan di Database + Cache
| | |
|---|---|
| **Prioritas** | 🟠 Medium |
| **Kompleksitas** | Sedang |
| **Deskripsi** | • Tabulasi stunting dihitung dengan query agregat (`SUM CASE … GROUP BY`), seperti yang sudah diterapkan pada DTSEN, tidak lagi memuat semua anak ke memori.<br>• Hasil di-*cache* per filter dan dihapus otomatis saat data berubah.<br>• Export PDF besar dijalankan lewat *queue*. |
| **Manfaat** | Laporan tetap cepat saat data mencapai puluhan ribu. |
| **Alasan** | Pendekatan saat ini cukup untuk ratusan data, tetapi akan melambat setelah impor massal (2.2). |

#### 6.2 Riwayat Periode Desil DTSEN
| | |
|---|---|
| **Prioritas** | 🟢 Low |
| **Kompleksitas** | Mudah |
| **Deskripsi** | Setiap impor/pemutakhiran desil disimpan per periode (mis. "Triwulan 3.2"), sehingga tabulasi periode lalu tetap bisa dicetak ulang dan dibandingkan. |
| **Manfaat** | Tren kemiskinan antar-triwulan dapat dilihat. |
| **Alasan** | Saat ini desil hanya menyimpan nilai terakhir. |

---

## C. Tidak Direkomendasikan

Fitur berikut menambah kompleksitas tanpa nilai yang sepadan:

- **Chat/forum internal** — koordinasi sudah berjalan lewat kanal yang ada.
- **Aplikasi mobile native** — cukup web responsif + PWA.
- **Login multi-faktor untuk semua user** — cukup batas percobaan login dan pembatasan wilayah.
- **Builder laporan kustom** — dua format tabulasi resmi sudah mencukupi.
- **Integrasi API Dukcapil** — butuh perjanjian kerja sama; impor Excel sudah cukup.

---

## D. Urutan Pengerjaan yang Disarankan

| Tahap | Item | Alasan urutan |
|---|---|---|
| **1 — Fondasi** (±1–2 minggu) | 1.1 Pembatasan wilayah, 1.2 Hapus akun & bersih-bersih, 2.3 Satukan verifikasi | Keamanan data, menu lebih ringkas, semuanya cepat dikerjakan |
| **2 — Kualitas data** | 2.1 Z-Score otomatis, 4.1 Indikator kelengkapan, 2.4 Tautan anak↔keluarga | Angka laporan menjadi sahih |
| **3 — Volume data** | 2.2 Impor Excel, 6.1 Agregasi + cache | Impor membawa ribuan data, jadi laporan harus siap menanganinya |
| **4 — Produktivitas** | 3.1 Dashboard per peran, 5.1 Notifikasi, 4.2 Pencarian global, 3.2 Grafik pertumbuhan | Memudahkan pekerjaan harian |
| **5 — Penyempurnaan** | 4.3 Mode lapangan, 4.4 Komponen UI, 5.2 Jadwal pengukuran, 6.2 Riwayat desil, 3.3 Peta | Sesuai kebutuhan |

### Ringkasan Prioritas

| Prioritas | Rekomendasi |
|---|---|
| 🔴 **High** | 1.1 Pembatasan wilayah · 1.2 Hapus akun & bersih-bersih · 2.1 Z-Score otomatis · 2.2 Impor Excel · 4.1 Indikator kelengkapan |
| 🟠 **Medium** | 2.3 Satukan verifikasi · 2.4 Tautan anak↔keluarga · 3.1 Dashboard per peran · 3.2 Grafik pertumbuhan · 4.2 Pencarian global · 4.3 Mode lapangan · 5.1 Notifikasi · 6.1 Agregasi + cache |
| 🟢 **Low** | 3.3 Peta sebaran · 4.4 Komponen UI · 5.2 Jadwal pengukuran ulang · 6.2 Riwayat desil |
