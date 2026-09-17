<?php

namespace App\Http\Requests\Kemiskinan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreKeluargaRequest extends FormRequest
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
            'action' => ['required', 'in:draft,kirim'],

            // Identitas keluarga (PRD Bagian 9)
            'nomor_kk' => ['required', 'digits:16'],
            'nik_kepala_keluarga' => ['required', 'digits:16'],
            'nama_kepala_keluarga' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'status_perkawinan' => ['nullable', Rule::in($this->kodeReferensi('status_perkawinan'))],
            'alamat' => ['required', 'string'],
            'rt' => ['required', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
            'kecamatan_id' => ['required', 'exists:kecamatans,id'],
            'desa_kelurahan_id' => ['required', 'exists:desa_kelurahans,id'],

            // Anggota keluarga (PRD Bagian 10). Baris hanya wajib lengkap jika nama diisi;
            // minimal satu anggota lengkap baru diwajibkan saat data dikirim (lihat withValidator()).
            'anggota' => ['nullable', 'array'],
            'anggota.*.nik' => ['nullable', 'digits:16'],
            'anggota.*.nama_lengkap' => ['nullable', 'string', 'max:255'],
            'anggota.*.jenis_kelamin' => ['required_with:anggota.*.nama_lengkap', 'in:laki-laki,perempuan'],
            'anggota.*.tempat_lahir' => ['nullable', 'string', 'max:255'],
            'anggota.*.tanggal_lahir' => ['required_with:anggota.*.nama_lengkap', 'date', 'before_or_equal:today'],
            'anggota.*.hubungan_keluarga' => ['required_with:anggota.*.nama_lengkap', 'string', 'max:255'],
            'anggota.*.status_perkawinan' => ['nullable', Rule::in($this->kodeReferensi('status_perkawinan'))],
            'anggota.*.pendidikan_terakhir' => ['nullable', Rule::in($this->kodeReferensi('pendidikan_terakhir'))],
            'anggota.*.status_pekerjaan' => ['nullable', Rule::in($this->kodeReferensi('status_pekerjaan'))],
            'anggota.*.disabilitas' => ['nullable', 'boolean'],
            'anggota.*.jenis_disabilitas' => ['nullable', 'string', 'max:255'],
            'anggota.*.penyakit_kronis' => ['nullable', 'boolean'],
            'anggota.*.jenis_penyakit_kronis' => ['nullable', 'string', 'max:255'],

            // Kondisi ekonomi (PRD Bagian 11) - total dihitung otomatis oleh model
            'kondisi_ekonomi.status_pekerjaan_kepala_keluarga' => ['nullable', Rule::in($this->kodeReferensi('status_pekerjaan'))],
            'kondisi_ekonomi.pekerjaan_utama' => ['nullable', 'string', 'max:255'],
            'kondisi_ekonomi.pekerjaan_tambahan' => ['nullable', 'string', 'max:255'],
            'kondisi_ekonomi.jumlah_anggota_bekerja' => ['nullable', 'integer', 'min:0'],
            'kondisi_ekonomi.jumlah_anggota_tidak_bekerja' => ['nullable', 'integer', 'min:0'],
            'kondisi_ekonomi.pendapatan_kepala_keluarga' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pendapatan_pasangan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pendapatan_anggota_lainnya' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_makanan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_pendidikan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_kesehatan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_listrik' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_air' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_transportasi' => ['nullable', 'numeric', 'min:0'],
            'kondisi_ekonomi.pengeluaran_lainnya' => ['nullable', 'numeric', 'min:0'],

            // Kondisi rumah (PRD Bagian 12) - wajib diisi hanya saat data dikirim untuk verifikasi,
            // draft boleh disimpan tanpa mengisi bagian ini.
            'kondisi_rumah.status_kepemilikan' => ['nullable', 'required_if:action,kirim', 'in:milik_sendiri,sewa,menumpang,rumah_dinas,lainnya'],
            'kondisi_rumah.kondisi_bangunan' => ['nullable', 'required_if:action,kirim', 'in:permanen,semi_permanen,tidak_layak_huni'],
            'kondisi_rumah.jenis_atap' => ['nullable', Rule::in($this->kodeReferensi('jenis_atap'))],
            'kondisi_rumah.jenis_dinding' => ['nullable', Rule::in($this->kodeReferensi('jenis_dinding'))],
            'kondisi_rumah.jenis_lantai' => ['nullable', Rule::in($this->kodeReferensi('jenis_lantai'))],
            'kondisi_rumah.sumber_listrik' => ['nullable', Rule::in($this->kodeReferensi('sumber_listrik'))],
            'kondisi_rumah.sumber_air' => ['nullable', Rule::in($this->kodeReferensi('sumber_air'))],
            'kondisi_rumah.jamban' => ['nullable', 'boolean'],
            'kondisi_rumah.septic_tank' => ['nullable', 'boolean'],
            'kondisi_rumah.drainase' => ['nullable', 'boolean'],
            'kondisi_rumah.pengelolaan_sampah' => ['nullable', Rule::in($this->kodeReferensi('pengelolaan_sampah'))],
            'kondisi_rumah.luas_tanah' => ['nullable', 'numeric', 'min:0'],
            'kondisi_rumah.luas_bangunan' => ['nullable', 'numeric', 'min:0'],
            'kondisi_rumah.jumlah_kamar' => ['nullable', 'integer', 'min:0'],
            'kondisi_rumah.jumlah_penghuni' => ['nullable', 'integer', 'min:0'],

            // Kepemilikan aset (PRD Bagian 13)
            'aset' => ['nullable', 'array'],
            'aset.*.jenis_aset' => ['nullable', Rule::in($this->kodeReferensi('jenis_aset'))],
            'aset.*.jumlah' => ['nullable', 'integer', 'min:1'],
            'aset.*.status_kepemilikan' => ['nullable', 'in:milik_sendiri,sewa,lainnya'],
            'aset.*.perkiraan_nilai' => ['nullable', 'numeric', 'min:0'],
            'aset.*.keterangan' => ['nullable', 'string'],

            // Kondisi sosial & layanan dasar (PRD Bagian 14)
            'kondisi_sosial.jumlah_anak_usia_sekolah' => ['nullable', 'integer', 'min:0'],
            'kondisi_sosial.jumlah_anak_bersekolah' => ['nullable', 'integer', 'min:0'],
            'kondisi_sosial.jumlah_anak_putus_sekolah' => ['nullable', 'integer', 'min:0'],
            'kondisi_sosial.jumlah_lansia' => ['nullable', 'integer', 'min:0'],
            'kondisi_sosial.jumlah_penyandang_disabilitas' => ['nullable', 'integer', 'min:0'],
            'kondisi_sosial.jumlah_anggota_sakit' => ['nullable', 'integer', 'min:0'],
            'kondisi_sosial.akses_fasilitas_kesehatan' => ['nullable', 'boolean'],
            'kondisi_sosial.jarak_fasilitas_kesehatan_km' => ['nullable', 'numeric', 'min:0'],
            'kondisi_sosial.akses_pendidikan' => ['nullable', 'boolean'],
            'kondisi_sosial.jarak_sekolah_km' => ['nullable', 'numeric', 'min:0'],
            'kondisi_sosial.kepemilikan_dokumen_kependudukan' => ['nullable', 'boolean'],

            // Kepesertaan program/bantuan (PRD Bagian 15)
            'program' => ['nullable', 'array'],
            'program.*.nama_program' => ['nullable', 'string', 'max:255'],
            'program.*.status_penerima' => ['nullable', 'in:penerima,bukan_penerima,pernah_menerima'],
            'program.*.tahun_menerima' => ['nullable', 'digits:4'],
            'program.*.sumber_instansi' => ['nullable', 'string', 'max:255'],
            'program.*.keterangan' => ['nullable', 'string'],

            // Dokumentasi (PRD Bagian 16)
            'dokumen' => ['nullable', 'array'],
            'dokumen.*.file' => ['required_with:dokumen', 'file', 'image', 'max:5120'],
            'dokumen.*.jenis_dokumentasi' => ['required_with:dokumen', 'in:foto_rumah,foto_lingkungan,foto_dokumen_pendukung,lainnya'],
            'dokumen.*.keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nomor_kk' => 'nomor KK',
            'nik_kepala_keluarga' => 'NIK kepala keluarga',
            'nama_kepala_keluarga' => 'nama kepala keluarga',
            'kecamatan_id' => 'kecamatan',
            'desa_kelurahan_id' => 'desa/kelurahan',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Draft boleh disimpan tanpa anggota; baru diwajibkan saat data dikirim untuk verifikasi.
            if ($this->input('action') !== 'kirim') {
                return;
            }

            $anggota = collect($this->input('anggota', []))
                ->filter(fn ($row) => filled($row['nama_lengkap'] ?? null));

            if ($anggota->isEmpty()) {
                $validator->errors()->add('anggota', 'Minimal satu anggota keluarga harus diisi sebelum data dikirim untuk verifikasi.');
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function kodeReferensi(string $kategori): array
    {
        return array_keys(config("referensi.{$kategori}", []));
    }
}
