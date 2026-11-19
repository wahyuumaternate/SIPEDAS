<?php

namespace App\Http\Requests\Stunting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Identitas anak (PRD Bagian 17.1) - wajib selalu, sesuai kolom NOT NULL di database.
            'nik_anak' => ['required', 'digits:16', Rule::unique('anaks', 'nik_anak')->ignore($this->route('anak'))->whereNull('deleted_at')],
            'memiliki_akta_kelahiran' => ['nullable', 'boolean'],
            'nomor_kk' => ['required', 'digits:16'],
            'nama_anak' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:laki-laki,perempuan'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'nomor_hp_orang_tua' => ['nullable', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'desa_kelurahan_id' => ['required', 'exists:desa_kelurahans,id'],

            // Data orang tua (PRD Bagian 18) - opsional untuk draft.
            'orang_tua.nik_ayah' => ['nullable', 'digits:16'],
            'orang_tua.nama_ayah_lengkap' => ['required', 'string', 'max:255'],
            'orang_tua.tanggal_lahir_ayah' => ['nullable', 'date'],
            'orang_tua.pendidikan_ayah' => ['nullable', Rule::in($this->kodeReferensi('pendidikan_terakhir'))],
            'orang_tua.pekerjaan_ayah' => ['nullable', 'string', 'max:255'],
            'orang_tua.penghasilan_ayah' => ['nullable', 'numeric', 'min:0'],
            'orang_tua.nik_ibu' => ['nullable', 'digits:16'],
            'orang_tua.nama_ibu_lengkap' => ['required', 'string', 'max:255'],
            'orang_tua.tanggal_lahir_ibu' => ['nullable', 'date'],
            'orang_tua.pendidikan_ibu' => ['nullable', Rule::in($this->kodeReferensi('pendidikan_terakhir'))],
            'orang_tua.pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'orang_tua.penghasilan_ibu' => ['nullable', 'numeric', 'min:0'],

            // Riwayat kehamilan (PRD Bagian 19) - opsional untuk draft.
            'riwayat_kehamilan.usia_ibu_saat_hamil' => ['nullable', 'integer', 'min:0'],
            'riwayat_kehamilan.kehamilan_ke' => ['nullable', 'integer', 'min:1'],
            'riwayat_kehamilan.jumlah_kehamilan' => ['nullable', 'integer', 'min:1'],
            'riwayat_kehamilan.jumlah_pemeriksaan_kehamilan' => ['nullable', 'integer', 'min:0'],
            'riwayat_kehamilan.tempat_pemeriksaan' => ['nullable', 'string', 'max:255'],
            'riwayat_kehamilan.kondisi_kehamilan' => ['nullable', 'string', 'max:255'],
            'riwayat_kehamilan.risiko_kehamilan' => ['nullable', 'boolean'],
            'riwayat_kehamilan.jumlah_tablet_tambah_darah' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'riwayat_kehamilan.status_kek' => ['nullable', 'boolean'],
            'riwayat_kehamilan.pmt_kek' => ['nullable', 'boolean'],
            'riwayat_kehamilan.makanan_bergizi_seimbang' => ['nullable', 'boolean'],
            'riwayat_kehamilan.kb_pascapersalinan' => ['nullable', 'boolean'],
            'riwayat_kehamilan.lila' => ['nullable', 'numeric', 'min:0'],
            'riwayat_kehamilan.komplikasi_kehamilan' => ['nullable', 'string'],

            // Riwayat kelahiran (PRD Bagian 20) - wajib diisi.
            'riwayat_kelahiran.tempat_lahir' => ['required', 'string', 'max:255'],
            'riwayat_kelahiran.penolong_persalinan' => ['nullable', 'in:dokter,bidan,perawat,dukun,lainnya'],
            'riwayat_kelahiran.cara_persalinan' => ['nullable', 'in:normal,caesar,lainnya'],
            'riwayat_kelahiran.berat_badan_lahir' => ['nullable', 'numeric', 'min:0'],
            'riwayat_kelahiran.panjang_badan_lahir' => ['nullable', 'numeric', 'min:0'],
            'riwayat_kelahiran.status_prematur' => ['nullable', 'boolean'],
            'riwayat_kelahiran.status_bblr' => ['nullable', 'boolean'],
            'riwayat_kelahiran.imd' => ['nullable', 'boolean'],
            'riwayat_kelahiran.kondisi_bayi_saat_lahir' => ['nullable', 'string', 'max:255'],

            // Pengukuran awal (PRD Bagian 21) - opsional, dapat ditambah kembali dari halaman detail.
            'pengukuran.tanggal_pengukuran' => ['nullable', 'date', 'before_or_equal:today'],
            'pengukuran.berat_badan' => ['nullable', 'required_with:pengukuran.tanggal_pengukuran', 'numeric', 'min:0'],
            'pengukuran.panjang_tinggi_badan' => ['nullable', 'required_with:pengukuran.tanggal_pengukuran', 'numeric', 'min:0'],
            'pengukuran.lingkar_kepala' => ['nullable', 'numeric', 'min:0'],
            'pengukuran.lingkar_lengan_atas' => ['nullable', 'numeric', 'min:0'],
            'pengukuran.tempat_pengukuran' => ['nullable', 'string', 'max:255'],
            'pengukuran.hasil_kategori' => ['nullable', 'in:normal,stunting_ringan,stunting_sedang,stunting_berat'],
            'pengukuran.status_gizi' => ['nullable', 'in:gizi_buruk,gizi_kurang,gizi_baik,gizi_lebih'],

            // Riwayat kesehatan anak (PRD Bagian 22) - opsional untuk draft.
            'riwayat_kesehatan.status_imunisasi' => ['nullable', 'in:lengkap,tidak_lengkap,tidak_imunisasi'],
            'riwayat_kesehatan.imunisasi_terakhir' => ['nullable', 'string', 'max:255'],
            'riwayat_kesehatan.riwayat_penyakit' => ['nullable', 'string'],
            'riwayat_kesehatan.riwayat_diare' => ['nullable', 'boolean'],
            'riwayat_kesehatan.riwayat_ispa' => ['nullable', 'boolean'],
            'riwayat_kesehatan.penyakit_kronis' => ['nullable', 'boolean'],
            'riwayat_kesehatan.jenis_penyakit_kronis' => ['nullable', 'string', 'max:255'],
            'riwayat_kesehatan.penyakit_bawaan' => ['nullable', 'boolean'],
            'riwayat_kesehatan.jenis_penyakit_bawaan' => ['nullable', 'string', 'max:255'],
            'riwayat_kesehatan.riwayat_rawat_inap' => ['nullable', 'boolean'],
            'riwayat_kesehatan.akses_pelayanan_kesehatan' => ['nullable', 'boolean'],
            'riwayat_kesehatan.tata_laksana_gizi_buruk' => ['nullable', 'boolean'],
            'riwayat_kesehatan.tambahan_asupan_gizi' => ['nullable', 'boolean'],
            'riwayat_kesehatan.oralit_zinc' => ['nullable', 'boolean'],
            'riwayat_kesehatan.pneumonia' => ['nullable', 'boolean'],
            'riwayat_kesehatan.obat_cacing' => ['nullable', 'boolean'],
            'riwayat_kesehatan.memiliki_jkn' => ['nullable', 'boolean'],

            // ASI & MPASI (PRD Bagian 23) - opsional untuk draft.
            'asi_mpasi.asi_eksklusif' => ['nullable', 'boolean'],
            'asi_mpasi.masih_asi' => ['nullable', 'boolean'],
            'asi_mpasi.lama_pemberian_asi_bulan' => ['nullable', 'integer', 'min:0'],
            'asi_mpasi.kendala_pemberian_asi' => ['nullable', 'string'],
            'asi_mpasi.usia_mulai_mpasi_bulan' => ['nullable', 'integer', 'min:0'],
            'asi_mpasi.frekuensi_makan' => ['nullable', 'string', 'max:255'],
            'asi_mpasi.jenis_makanan' => ['nullable', 'string', 'max:255'],
            'asi_mpasi.sumber_protein' => ['nullable', 'string', 'max:255'],
            'asi_mpasi.konsumsi_sayur' => ['nullable', 'boolean'],
            'asi_mpasi.konsumsi_buah' => ['nullable', 'boolean'],
            'asi_mpasi.keragaman_makanan' => ['nullable', 'string', 'max:255'],

            // Sanitasi & lingkungan (PRD Bagian 24) - opsional untuk draft.
            'sanitasi.sumber_air_minum' => ['nullable', Rule::in($this->kodeReferensi('sumber_air'))],
            'sanitasi.sumber_air_memasak' => ['nullable', Rule::in($this->kodeReferensi('sumber_air'))],
            'sanitasi.jenis_jamban' => ['nullable', Rule::in($this->kodeReferensi('jenis_jamban'))],
            'sanitasi.septic_tank' => ['nullable', 'boolean'],
            'sanitasi.saluran_pembuangan' => ['nullable', 'string', 'max:255'],
            'sanitasi.pengelolaan_sampah' => ['nullable', Rule::in($this->kodeReferensi('pengelolaan_sampah'))],
            'sanitasi.kondisi_rumah' => ['nullable', 'in:permanen,semi_permanen,tidak_layak_huni'],
            'sanitasi.kepadatan_hunian' => ['nullable', 'numeric', 'min:0'],

            // Dokumentasi (PRD Bagian 25)
            'dokumen' => ['nullable', 'array'],
            'dokumen.*.file' => ['required_with:dokumen', 'file', 'image', 'max:5120'],
            'dokumen.*.jenis_dokumentasi' => ['required_with:dokumen', 'in:foto_anak,foto_pengukuran,foto_dokumen_pendukung,lainnya'],
            'dokumen.*.keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik_anak.unique' => 'NIK anak ini sudah terdaftar. Data anak tidak boleh duplikat.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nik_anak' => 'NIK anak',
            'riwayat_kelahiran.tempat_lahir' => 'tempat persalinan',
            'orang_tua.nama_ayah_lengkap' => 'nama ayah',
            'orang_tua.nama_ibu_lengkap' => 'nama ibu',
            'nomor_kk' => 'nomor KK',
            'nama_anak' => 'nama anak',
            'kecamatan_id' => 'kecamatan',
            'desa_kelurahan_id' => 'desa/kelurahan',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function kodeReferensi(string $kategori): array
    {
        return array_keys(config("referensi.{$kategori}", []));
    }
}
