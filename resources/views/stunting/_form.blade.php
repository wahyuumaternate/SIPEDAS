@php
    $anak = $anak ?? null;
    $isEdit = (bool) $anak;
    $kunciDraft = $anak ? 'stunting.edit.'.$anak->id : 'stunting.create';

    $orangTua = old('orang_tua', $isEdit ? ($anak->orangTua?->toArray() ?? []) : []);
    if ($isEdit && ! old('orang_tua')) {
        // Data lama yang belum punya baris orang_tua tetap menampilkan nama dari identitas anak.
        $orangTua['nama_ayah_lengkap'] = ($orangTua['nama_ayah_lengkap'] ?? null) ?: $anak->nama_ayah;
        $orangTua['nama_ibu_lengkap'] = ($orangTua['nama_ibu_lengkap'] ?? null) ?: $anak->nama_ibu;
    }
    $riwayatKehamilan = old('riwayat_kehamilan', $isEdit ? ($anak->riwayatKehamilan?->toArray() ?? []) : []);
    $riwayatKelahiran = old('riwayat_kelahiran', $isEdit ? ($anak->riwayatKelahiran?->toArray() ?? []) : []);
    $riwayatKesehatan = old('riwayat_kesehatan', $isEdit ? ($anak->riwayatKesehatan?->toArray() ?? []) : []);
    $asiMpasi = old('asi_mpasi', $isEdit ? ($anak->asiMpasi?->toArray() ?? []) : []);
    $sanitasi = old('sanitasi', $isEdit ? ($anak->sanitasi?->toArray() ?? []) : []);
@endphp

<style>
    .terisi-otomatis {
        background-color: rgba(25, 135, 84, 0.08);
        border-color: rgba(25, 135, 84, 0.5);
    }

    .form-tabs .nav-link {
        border: 0;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .form-tabs .nav-link.active {
        color: var(--bs-primary, #4b7bec);
        border-bottom-color: var(--bs-primary, #4b7bec);
        background: transparent;
    }
</style>

<form method="POST" action="{{ $isEdit ? route('stunting.update', $anak) : route('stunting.store') }}"
    enctype="multipart/form-data" id="form-stunting">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <ul class="nav form-tabs mb-3" id="formTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-identitas"
                type="button">1. Identitas Anak</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ortu"
                type="button">2. Orang Tua</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-hamil"
                type="button">3. Riwayat Kehamilan</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lahir"
                type="button">4. Riwayat Kelahiran</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ukur"
                type="button">5. Pengukuran</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sehat"
                type="button">6. Kesehatan</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-asi"
                type="button">7. ASI &amp; MPASI</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sanitasi"
                type="button">8. Sanitasi</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dokumen"
                type="button">9. Dokumentasi</button></li>
    </ul>

    <div class="tab-content">
        {{-- ========== 1. IDENTITAS ANAK (PRD Bagian 17) ========== --}}
        <div class="tab-pane fade show active panel" id="tab-identitas">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIK Anak <span class="text-danger">*</span></label>
                    <input type="text" name="nik_anak" maxlength="16" class="form-control @error('nik_anak') is-invalid @enderror"
                        value="{{ old('nik_anak', $anak?->nik_anak) }}" required>
                    @error('nik_anak') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-check mt-2">
                        <input type="hidden" name="memiliki_akta_kelahiran" value="0">
                        <input type="checkbox" class="form-check-input" name="memiliki_akta_kelahiran" value="1"
                            id="memiliki_akta_kelahiran" @checked(old('memiliki_akta_kelahiran', $anak?->memiliki_akta_kelahiran))>
                        <label class="form-check-label small" for="memiliki_akta_kelahiran">Memiliki Akta Kelahiran</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor KK <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_kk" id="nomor_kk" maxlength="16" inputmode="numeric"
                        class="form-control @error('nomor_kk') is-invalid @enderror"
                        value="{{ old('nomor_kk', $anak?->nomor_kk) }}" required
                        data-url-cari="{{ route('stunting.cari-keluarga') }}" data-kecuali-anak="{{ $anak?->id }}">
                    @error('nomor_kk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div id="infoKeluargaKk" class="form-text" aria-live="polite">
                        Jika Nomor KK sudah terdata di modul kemiskinan, data orang tua terisi otomatis.
                    </div>
                    <div id="pilihAnakKk" class="mt-2 d-none">
                        <label class="form-label small mb-1" for="pilihAnakKkSelect">Pilih anak dari Kartu Keluarga</label>
                        <select id="pilihAnakKkSelect" class="form-select form-select-sm"></select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Anak <span class="text-danger">*</span></label>
                    <input type="text" name="nama_anak" class="form-control @error('nama_anak') is-invalid @enderror"
                        value="{{ old('nama_anak', $anak?->nama_anak) }}" required>
                    @error('nama_anak') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                        <option value="">Pilih</option>
                        <option value="laki-laki" @selected(old('jenis_kelamin', $anak?->jenis_kelamin) === 'laki-laki')>Laki-laki</option>
                        <option value="perempuan" @selected(old('jenis_kelamin', $anak?->jenis_kelamin) === 'perempuan')>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                    <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                        value="{{ old('tempat_lahir', $anak?->tempat_lahir) }}" required>
                    @error('tempat_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                        value="{{ old('tanggal_lahir', optional($anak?->tanggal_lahir)->format('Y-m-d')) }}" required>
                    @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2" required>{{ old('alamat', $anak?->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <select name="kecamatan_id" id="kecamatan_id"
                        class="form-select @error('kecamatan_id') is-invalid @enderror" required>
                        <option value="">Pilih Kecamatan</option>
                        @foreach ($kecamatans as $kecamatan)
                            <option value="{{ $kecamatan->id }}" @selected(old('kecamatan_id', $anak?->kecamatan_id) == $kecamatan->id)>{{ $kecamatan->nama }}</option>
                        @endforeach
                    </select>
                    @error('kecamatan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Desa/Kelurahan <span class="text-danger">*</span></label>
                    <select name="desa_kelurahan_id" id="desa_kelurahan_id"
                        class="form-select @error('desa_kelurahan_id') is-invalid @enderror" required>
                        <option value="">Pilih Kecamatan Terlebih Dahulu</option>
                    </select>
                    @error('desa_kelurahan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ========== 2. DATA ORANG TUA (PRD Bagian 18) ========== --}}
        <div class="tab-pane fade panel" id="tab-ortu">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small">Nomor HP Orang Tua</label>
                    <input type="text" name="nomor_hp_orang_tua" class="form-control @error('nomor_hp_orang_tua') is-invalid @enderror"
                        value="{{ old('nomor_hp_orang_tua', $anak?->nomor_hp_orang_tua) }}">
                    @error('nomor_hp_orang_tua') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <h2 class="h6">Ayah</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label small">NIK Ayah</label>
                    <input type="text" maxlength="16" name="orang_tua[nik_ayah]" class="form-control"
                        value="{{ $orangTua['nik_ayah'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Nama Ayah <span class="text-danger">*</span></label>
                    <input type="text" name="orang_tua[nama_ayah_lengkap]"
                        class="form-control @error('orang_tua.nama_ayah_lengkap') is-invalid @enderror"
                        value="{{ $orangTua['nama_ayah_lengkap'] ?? '' }}" required>
                    @error('orang_tua.nama_ayah_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Tanggal Lahir Ayah</label>
                    <input type="date" name="orang_tua[tanggal_lahir_ayah]" class="form-control"
                        value="{{ $orangTua['tanggal_lahir_ayah'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Pendidikan Ayah</label>
                    <select name="orang_tua[pendidikan_ayah]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refPendidikan as $kode => $label)
                            <option value="{{ $kode }}" @selected(($orangTua['pendidikan_ayah'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Pekerjaan Ayah</label>
                    <input type="text" name="orang_tua[pekerjaan_ayah]" class="form-control"
                        value="{{ $orangTua['pekerjaan_ayah'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Penghasilan Ayah (Rp)</label>
                    <input type="number" min="0" step="0.01" name="orang_tua[penghasilan_ayah]" class="form-control"
                        value="{{ $orangTua['penghasilan_ayah'] ?? '' }}">
                </div>
            </div>

            <h2 class="h6">Ibu</h2>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">NIK Ibu</label>
                    <input type="text" maxlength="16" name="orang_tua[nik_ibu]" class="form-control"
                        value="{{ $orangTua['nik_ibu'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Nama Ibu <span class="text-danger">*</span></label>
                    <input type="text" name="orang_tua[nama_ibu_lengkap]"
                        class="form-control @error('orang_tua.nama_ibu_lengkap') is-invalid @enderror"
                        value="{{ $orangTua['nama_ibu_lengkap'] ?? '' }}" required>
                    @error('orang_tua.nama_ibu_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Tanggal Lahir Ibu</label>
                    <input type="date" name="orang_tua[tanggal_lahir_ibu]" class="form-control"
                        value="{{ $orangTua['tanggal_lahir_ibu'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Pendidikan Ibu</label>
                    <select name="orang_tua[pendidikan_ibu]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refPendidikan as $kode => $label)
                            <option value="{{ $kode }}" @selected(($orangTua['pendidikan_ibu'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Pekerjaan Ibu</label>
                    <input type="text" name="orang_tua[pekerjaan_ibu]" class="form-control"
                        value="{{ $orangTua['pekerjaan_ibu'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Penghasilan Ibu (Rp)</label>
                    <input type="number" min="0" step="0.01" name="orang_tua[penghasilan_ibu]" class="form-control"
                        value="{{ $orangTua['penghasilan_ibu'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- ========== 3. RIWAYAT KEHAMILAN (PRD Bagian 19) ========== --}}
        <div class="tab-pane fade panel" id="tab-hamil">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Usia Ibu Saat Hamil</label>
                    <input type="number" min="0" name="riwayat_kehamilan[usia_ibu_saat_hamil]" class="form-control"
                        value="{{ $riwayatKehamilan['usia_ibu_saat_hamil'] ?? '' }}">
                    <div class="form-text">Kosongkan bila tanggal lahir ibu sudah diisi; dihitung otomatis.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Kehamilan Ke-</label>
                    <input type="number" min="1" name="riwayat_kehamilan[kehamilan_ke]" class="form-control"
                        value="{{ $riwayatKehamilan['kehamilan_ke'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Jumlah Kehamilan</label>
                    <input type="number" min="1" name="riwayat_kehamilan[jumlah_kehamilan]" class="form-control"
                        value="{{ $riwayatKehamilan['jumlah_kehamilan'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Jumlah Pemeriksaan Kehamilan</label>
                    <input type="number" min="0" name="riwayat_kehamilan[jumlah_pemeriksaan_kehamilan]"
                        class="form-control" value="{{ $riwayatKehamilan['jumlah_pemeriksaan_kehamilan'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Tempat Pemeriksaan</label>
                    <input type="text" name="riwayat_kehamilan[tempat_pemeriksaan]" class="form-control"
                        value="{{ $riwayatKehamilan['tempat_pemeriksaan'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Kondisi Kehamilan</label>
                    <input type="text" name="riwayat_kehamilan[kondisi_kehamilan]" class="form-control"
                        value="{{ $riwayatKehamilan['kondisi_kehamilan'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Jumlah Tablet Tambah Darah/MMS Dikonsumsi</label>
                    <input type="number" min="0" max="1000" name="riwayat_kehamilan[jumlah_tablet_tambah_darah]" class="form-control"
                        value="{{ $riwayatKehamilan['jumlah_tablet_tambah_darah'] ?? '' }}">
                    <div class="form-text">Standar minimal 180 tablet. Isi 0 bila tidak pernah mengonsumsi.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">LILA (cm)</label>
                    <input type="number" min="0" step="0.01" name="riwayat_kehamilan[lila]" class="form-control"
                        value="{{ $riwayatKehamilan['lila'] ?? '' }}">
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kehamilan[risiko_kehamilan]"
                        value="1" id="risiko_kehamilan" @checked($riwayatKehamilan['risiko_kehamilan'] ?? false)>
                    <label class="form-check-label" for="risiko_kehamilan">Kehamilan Berisiko</label>
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kehamilan[status_kek]" value="1"
                        id="status_kek" @checked($riwayatKehamilan['status_kek'] ?? false)>
                    <label class="form-check-label" for="status_kek">Status KEK <span class="text-muted small">(otomatis bila LILA &lt; 23,5 cm)</span></label>
                </div>
                <div class="col-md-4 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kehamilan[pmt_kek]" value="1"
                        id="pmt_kek" @checked($riwayatKehamilan['pmt_kek'] ?? false)>
                    <label class="form-check-label" for="pmt_kek">Ibu KEK Mendapat PMT</label>
                </div>
                <div class="col-md-4 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kehamilan[makanan_bergizi_seimbang]" value="1"
                        id="makanan_bergizi_seimbang" @checked($riwayatKehamilan['makanan_bergizi_seimbang'] ?? false)>
                    <label class="form-check-label" for="makanan_bergizi_seimbang">Mengonsumsi Makanan Bergizi Seimbang</label>
                </div>
                <div class="col-md-4 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kehamilan[kb_pascapersalinan]" value="1"
                        id="kb_pascapersalinan" @checked($riwayatKehamilan['kb_pascapersalinan'] ?? false)>
                    <label class="form-check-label" for="kb_pascapersalinan">Ber-KB Pascapersalinan (≤ 42 hari)</label>
                </div>
                <div class="col-12">
                    <label class="form-label small">Komplikasi Kehamilan</label>
                    <textarea name="riwayat_kehamilan[komplikasi_kehamilan]" class="form-control" rows="2">{{ $riwayatKehamilan['komplikasi_kehamilan'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- ========== 4. RIWAYAT KELAHIRAN (PRD Bagian 20) ========== --}}
        <div class="tab-pane fade panel" id="tab-lahir">
            <div class="row g-3">
                <div class="col-md-4">
                    @php($tempatPersalinan = $riwayatKelahiran['tempat_lahir'] ?? '')
                    @php($pilihanPersalinan = ['Rumah Sakit', 'Puskesmas', 'Klinik/Praktik Bidan', 'Rumah', 'Lainnya'])
                    <label class="form-label">Tempat Persalinan <span class="text-danger">*</span></label>
                    <select name="riwayat_kelahiran[tempat_lahir]"
                        class="form-select @error('riwayat_kelahiran.tempat_lahir') is-invalid @enderror" required>
                        <option value="">Pilih</option>
                        @foreach ($pilihanPersalinan as $pilihan)
                            <option value="{{ $pilihan }}" @selected($tempatPersalinan === $pilihan)>{{ $pilihan }}</option>
                        @endforeach
                        @if ($tempatPersalinan !== '' && ! in_array($tempatPersalinan, $pilihanPersalinan, true))
                            <option value="{{ $tempatPersalinan }}" selected>{{ $tempatPersalinan }}</option>
                        @endif
                    </select>
                    @error('riwayat_kelahiran.tempat_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Fasilitas tempat ibu melahirkan. Tanggal &amp; kota lahir diisi di langkah Identitas Anak.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Penolong Persalinan</label>
                    <select name="riwayat_kelahiran[penolong_persalinan]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach (['dokter' => 'Dokter', 'bidan' => 'Bidan', 'perawat' => 'Perawat', 'dukun' => 'Dukun', 'lainnya' => 'Lainnya'] as $val => $label)
                            <option value="{{ $val }}" @selected(($riwayatKelahiran['penolong_persalinan'] ?? null) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cara Persalinan</label>
                    <select name="riwayat_kelahiran[cara_persalinan]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach (['normal' => 'Normal', 'caesar' => 'Caesar', 'lainnya' => 'Lainnya'] as $val => $label)
                            <option value="{{ $val }}" @selected(($riwayatKelahiran['cara_persalinan'] ?? null) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Berat Badan Lahir (kg)</label>
                    <input type="number" min="0" step="0.01" name="riwayat_kelahiran[berat_badan_lahir]"
                        class="form-control" value="{{ $riwayatKelahiran['berat_badan_lahir'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Panjang Badan Lahir (cm)</label>
                    <input type="number" min="0" step="0.01" name="riwayat_kelahiran[panjang_badan_lahir]"
                        class="form-control" value="{{ $riwayatKelahiran['panjang_badan_lahir'] ?? '' }}">
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kelahiran[status_prematur]"
                        value="1" id="status_prematur" @checked($riwayatKelahiran['status_prematur'] ?? false)>
                    <label class="form-check-label" for="status_prematur">Prematur</label>
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kelahiran[status_bblr]" value="1"
                        id="status_bblr" @checked($riwayatKelahiran['status_bblr'] ?? false)>
                    <label class="form-check-label" for="status_bblr">BBLR</label>
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kelahiran[imd]" value="1"
                        id="imd_lahir" @checked($riwayatKelahiran['imd'] ?? false)>
                    <label class="form-check-label" for="imd_lahir">IMD (Inisiasi Menyusu Dini)</label>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kondisi Bayi Saat Lahir</label>
                    <input type="text" name="riwayat_kelahiran[kondisi_bayi_saat_lahir]" class="form-control"
                        value="{{ $riwayatKelahiran['kondisi_bayi_saat_lahir'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- ========== 5. PENGUKURAN AWAL (PRD Bagian 21) ========== --}}
        <div class="tab-pane fade panel" id="tab-ukur">
            <p class="text-muted small">Opsional saat pendataan awal. Pengukuran berikutnya dapat ditambahkan dari halaman detail anak tanpa menimpa data lama.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Tanggal Pengukuran</label>
                    <input type="date" name="pengukuran[tanggal_pengukuran]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Berat Badan (kg)</label>
                    <input type="number" min="0" step="0.01" name="pengukuran[berat_badan]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Panjang/Tinggi Badan (cm)</label>
                    <input type="number" min="0" step="0.01" name="pengukuran[panjang_tinggi_badan]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Lingkar Kepala (cm)</label>
                    <input type="number" min="0" step="0.01" name="pengukuran[lingkar_kepala]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Lingkar Lengan Atas (cm)</label>
                    <input type="number" min="0" step="0.01" name="pengukuran[lingkar_lengan_atas]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Tempat Pengukuran</label>
                    <input type="text" name="pengukuran[tempat_pengukuran]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Hasil Kategori</label>
                    <select name="pengukuran[hasil_kategori]" class="form-select">
                        <option value="">Pilih</option>
                        <option value="normal">Normal</option>
                        <option value="stunting_ringan">Stunting Ringan</option>
                        <option value="stunting_sedang">Stunting Sedang</option>
                        <option value="stunting_berat">Stunting Berat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Status Gizi (BB/TB)</label>
                    <select name="pengukuran[status_gizi]" class="form-select">
                        <option value="">Pilih</option>
                        <option value="gizi_buruk">Gizi Buruk</option>
                        <option value="gizi_kurang">Gizi Kurang</option>
                        <option value="gizi_baik">Gizi Baik</option>
                        <option value="gizi_lebih">Gizi Lebih</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ========== 6. RIWAYAT KESEHATAN ANAK (PRD Bagian 22) ========== --}}
        <div class="tab-pane fade panel" id="tab-sehat">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Status Imunisasi</label>
                    <select name="riwayat_kesehatan[status_imunisasi]" class="form-select">
                        <option value="">Pilih</option>
                        <option value="lengkap" @selected(($riwayatKesehatan['status_imunisasi'] ?? null) === 'lengkap')>Lengkap</option>
                        <option value="tidak_lengkap" @selected(($riwayatKesehatan['status_imunisasi'] ?? null) === 'tidak_lengkap')>Tidak Lengkap</option>
                        <option value="tidak_imunisasi" @selected(($riwayatKesehatan['status_imunisasi'] ?? null) === 'tidak_imunisasi')>Tidak Imunisasi</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Imunisasi Terakhir</label>
                    <input type="text" name="riwayat_kesehatan[imunisasi_terakhir]" class="form-control"
                        value="{{ $riwayatKesehatan['imunisasi_terakhir'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label small">Riwayat Penyakit</label>
                    <input type="text" name="riwayat_kesehatan[riwayat_penyakit]" class="form-control"
                        value="{{ $riwayatKesehatan['riwayat_penyakit'] ?? '' }}">
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kesehatan[riwayat_diare]" value="1"
                        id="riwayat_diare" @checked($riwayatKesehatan['riwayat_diare'] ?? false)>
                    <label class="form-check-label" for="riwayat_diare">Riwayat Diare</label>
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kesehatan[riwayat_ispa]" value="1"
                        id="riwayat_ispa" @checked($riwayatKesehatan['riwayat_ispa'] ?? false)>
                    <label class="form-check-label" for="riwayat_ispa">Riwayat ISPA</label>
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kesehatan[riwayat_rawat_inap]"
                        value="1" id="riwayat_rawat_inap" @checked($riwayatKesehatan['riwayat_rawat_inap'] ?? false)>
                    <label class="form-check-label" for="riwayat_rawat_inap">Pernah Rawat Inap</label>
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input"
                        name="riwayat_kesehatan[akses_pelayanan_kesehatan]" value="1" id="akses_yankes"
                        @checked($riwayatKesehatan['akses_pelayanan_kesehatan'] ?? false)>
                    <label class="form-check-label" for="akses_yankes">Akses Pelayanan Kesehatan</label>
                </div>
                <div class="col-md-6 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kesehatan[penyakit_kronis]"
                        value="1" id="penyakit_kronis" @checked($riwayatKesehatan['penyakit_kronis'] ?? false)>
                    <label class="form-check-label" for="penyakit_kronis">Memiliki Penyakit Kronis</label>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Jenis Penyakit Kronis</label>
                    <input type="text" name="riwayat_kesehatan[jenis_penyakit_kronis]" class="form-control"
                        value="{{ $riwayatKesehatan['jenis_penyakit_kronis'] ?? '' }}">
                </div>
                <div class="col-md-6 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="riwayat_kesehatan[penyakit_bawaan]"
                        value="1" id="penyakit_bawaan" @checked($riwayatKesehatan['penyakit_bawaan'] ?? false)>
                    <label class="form-check-label" for="penyakit_bawaan">Memiliki Penyakit Bawaan</label>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Jenis Penyakit Bawaan</label>
                    <input type="text" name="riwayat_kesehatan[jenis_penyakit_bawaan]" class="form-control"
                        value="{{ $riwayatKesehatan['jenis_penyakit_bawaan'] ?? '' }}">
                </div>
            </div>

            <h2 class="h6 mt-4">Layanan yang Diterima</h2>
            <div class="row g-3">
                @foreach ([
                    'tata_laksana_gizi_buruk' => 'Mendapat Tata Laksana Gizi Buruk',
                    'tambahan_asupan_gizi' => 'Gizi Kurang Mendapat Tambahan Asupan Gizi (PMT)',
                    'oralit_zinc' => 'Diare Mendapat Oralit & Zinc',
                    'pneumonia' => 'Terdiagnosis Pneumonia',
                    'obat_cacing' => 'Mendapat Obat Cacing (POPM)',
                    'memiliki_jkn' => 'Memiliki JKN/BPJS Kesehatan',
                ] as $kolom => $label)
                    <div class="col-md-4 form-check pt-2">
                        <input type="checkbox" class="form-check-input" name="riwayat_kesehatan[{{ $kolom }}]" value="1"
                            id="{{ $kolom }}" @checked($riwayatKesehatan[$kolom] ?? false)>
                        <label class="form-check-label" for="{{ $kolom }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ========== 7. ASI & MPASI (PRD Bagian 23) ========== --}}
        <div class="tab-pane fade panel" id="tab-asi">
            <h2 class="h6">ASI</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="asi_mpasi[asi_eksklusif]" value="1"
                        id="asi_eksklusif" @checked($asiMpasi['asi_eksklusif'] ?? false)>
                    <label class="form-check-label" for="asi_eksklusif">ASI Eksklusif</label>
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="asi_mpasi[masih_asi]" value="1"
                        id="masih_asi" @checked($asiMpasi['masih_asi'] ?? false)>
                    <label class="form-check-label" for="masih_asi">Masih Mendapat ASI</label>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Lama Pemberian ASI (bulan)</label>
                    <input type="number" min="0" name="asi_mpasi[lama_pemberian_asi_bulan]" class="form-control"
                        value="{{ $asiMpasi['lama_pemberian_asi_bulan'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Usia Mulai MPASI (bulan)</label>
                    <input type="number" min="0" name="asi_mpasi[usia_mulai_mpasi_bulan]" class="form-control"
                        value="{{ $asiMpasi['usia_mulai_mpasi_bulan'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label small">Kendala Pemberian ASI</label>
                    <input type="text" name="asi_mpasi[kendala_pemberian_asi]" class="form-control"
                        value="{{ $asiMpasi['kendala_pemberian_asi'] ?? '' }}">
                </div>
            </div>

            <h2 class="h6">MPASI</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Frekuensi Makan</label>
                    <input type="text" name="asi_mpasi[frekuensi_makan]" class="form-control"
                        value="{{ $asiMpasi['frekuensi_makan'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Jenis Makanan</label>
                    <input type="text" name="asi_mpasi[jenis_makanan]" class="form-control"
                        value="{{ $asiMpasi['jenis_makanan'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Sumber Protein</label>
                    <input type="text" name="asi_mpasi[sumber_protein]" class="form-control"
                        value="{{ $asiMpasi['sumber_protein'] ?? '' }}">
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="asi_mpasi[konsumsi_sayur]" value="1"
                        id="konsumsi_sayur" @checked($asiMpasi['konsumsi_sayur'] ?? false)>
                    <label class="form-check-label" for="konsumsi_sayur">Konsumsi Sayur</label>
                </div>
                <div class="col-md-3 form-check pt-2">
                    <input type="checkbox" class="form-check-input" name="asi_mpasi[konsumsi_buah]" value="1"
                        id="konsumsi_buah" @checked($asiMpasi['konsumsi_buah'] ?? false)>
                    <label class="form-check-label" for="konsumsi_buah">Konsumsi Buah</label>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Keragaman Makanan</label>
                    <input type="text" name="asi_mpasi[keragaman_makanan]" class="form-control"
                        value="{{ $asiMpasi['keragaman_makanan'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- ========== 8. SANITASI & LINGKUNGAN (PRD Bagian 24) ========== --}}
        <div class="tab-pane fade panel" id="tab-sanitasi">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Sumber Air Minum</label>
                    <select name="sanitasi[sumber_air_minum]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refSumberAir as $kode => $label)
                            <option value="{{ $kode }}" @selected(($sanitasi['sumber_air_minum'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Sumber Air Memasak</label>
                    <select name="sanitasi[sumber_air_memasak]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refSumberAir as $kode => $label)
                            <option value="{{ $kode }}" @selected(($sanitasi['sumber_air_memasak'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Jenis Jamban</label>
                    <select name="sanitasi[jenis_jamban]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refJenisJamban as $kode => $label)
                            <option value="{{ $kode }}" @selected(($sanitasi['jenis_jamban'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="sanitasi[septic_tank]" value="1"
                        id="septic_tank_sanitasi" @checked($sanitasi['septic_tank'] ?? false)>
                    <label class="form-check-label" for="septic_tank_sanitasi">Memiliki Septic Tank</label>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Pengelolaan Sampah</label>
                    <select name="sanitasi[pengelolaan_sampah]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refPengelolaanSampah as $kode => $label)
                            <option value="{{ $kode }}" @selected(($sanitasi['pengelolaan_sampah'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Saluran Pembuangan</label>
                    <input type="text" name="sanitasi[saluran_pembuangan]" class="form-control"
                        value="{{ $sanitasi['saluran_pembuangan'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Kondisi Rumah</label>
                    <select name="sanitasi[kondisi_rumah]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach (['permanen' => 'Permanen', 'semi_permanen' => 'Semi Permanen', 'tidak_layak_huni' => 'Tidak Layak Huni'] as $val => $label)
                            <option value="{{ $val }}" @selected(($sanitasi['kondisi_rumah'] ?? null) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Kepadatan Hunian (m²/orang)</label>
                    <input type="number" min="0" step="0.01" name="sanitasi[kepadatan_hunian]" class="form-control"
                        value="{{ $sanitasi['kepadatan_hunian'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- ========== 9. DOKUMENTASI (PRD Bagian 25) ========== --}}
        <div class="tab-pane fade panel" id="tab-dokumen">
            @if ($isEdit && $anak->dokumen->isNotEmpty())
                <h2 class="h6">Dokumentasi Tersimpan</h2>
                <div class="row g-2 mb-3">
                    @foreach ($anak->dokumen as $dok)
                        <div class="col-md-3">
                            <div class="border rounded p-2 text-center">
                                <img src="{{ Storage::url($dok->path_file) }}" class="img-fluid rounded mb-1"
                                    style="max-height:100px;object-fit:cover;" alt="{{ $dok->nama_file }}">
                                <p class="small mb-1 text-truncate">{{ $dok->nama_file }}</p>
                                <button type="submit" form="hapus-dokumen-{{ $dok->id }}"
                                    class="btn btn-outline-danger btn-sm">Hapus</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-muted small">Untuk menambah dokumentasi baru, simpan form ini terlebih dahulu lalu unggah dari halaman detail.</p>
            @else
                <p class="text-muted small">Unggah foto anak, foto pengukuran, atau dokumen pendukung lainnya.</p>
                <div id="dokumen-container"></div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-dokumen">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Dokumentasi
                </button>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-between gap-2 mt-3">
        <button type="button" class="btn btn-outline-secondary" id="btn-tab-prev">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Sebelumnya
        </button>
        <button type="button" class="btn btn-outline-primary" id="btn-tab-next">
            Selanjutnya <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </button>
    </div>

    <div class="d-flex justify-content-end align-items-center gap-2 mt-3 flex-wrap">
        <span id="status-draft" class="small text-muted me-auto" aria-live="polite"></span>
        <a href="{{ $isEdit ? route('stunting.show', $anak) : route('stunting.index') }}"
            class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save" aria-hidden="true"></i> Simpan Data
        </button>
    </div>
</form>

@if ($isEdit)
    @foreach ($anak->dokumen as $dok)
        <form id="hapus-dokumen-{{ $dok->id }}" method="POST"
            action="{{ route('stunting.dokumen.destroy', [$anak, $dok]) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@push('scripts')
    <script src="{{ asset('assets/js/draft-formulir.js') }}"></script>
    <script>
        (function () {
            // ===== Navigasi Sebelumnya / Selanjutnya antar tab =====
            const tabButtons = Array.from(document.querySelectorAll('#formTab button[data-bs-toggle="tab"]'));
            const btnPrev = document.getElementById('btn-tab-prev');
            const btnNext = document.getElementById('btn-tab-next');

            function activeTabIndex() {
                return tabButtons.findIndex((btn) => btn.classList.contains('active'));
            }

            function goToTab(index) {
                if (index < 0 || index >= tabButtons.length) return;
                bootstrap.Tab.getOrCreateInstance(tabButtons[index]).show();
            }

            function updatePrevNextState() {
                const index = activeTabIndex();
                btnPrev.disabled = index <= 0;
                btnNext.classList.toggle('d-none', index >= tabButtons.length - 1);
            }

            btnPrev.addEventListener('click', () => goToTab(activeTabIndex() - 1));
            btnNext.addEventListener('click', () => goToTab(activeTabIndex() + 1));
            tabButtons.forEach((btn) => btn.addEventListener('shown.bs.tab', updatePrevNextState));
            updatePrevNextState();

            const form = document.getElementById('form-stunting');

            const kecamatanData = @json($kecamatans->keyBy('id')->map->desaKelurahans);
            const kecamatanSelect = document.getElementById('kecamatan_id');
            const desaSelect = document.getElementById('desa_kelurahan_id');
            const selectedDesaId = '{{ old('desa_kelurahan_id', $anak?->desa_kelurahan_id) }}';

            function renderDesaOptions() {
                const list = kecamatanData[kecamatanSelect.value] || [];
                desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                list.forEach(function (desa) {
                    const opt = document.createElement('option');
                    opt.value = desa.id;
                    opt.textContent = desa.nama;
                    if (String(desa.id) === selectedDesaId) opt.selected = true;
                    desaSelect.appendChild(opt);
                });
            }

            kecamatanSelect.addEventListener('change', renderDesaOptions);
            if (kecamatanSelect.value) renderDesaOptions();

            // ===== LILA ibu < 23,5 cm = KEK =====
            const inputLila = document.querySelector('[name="riwayat_kehamilan[lila]"]');
            const cekKek = document.getElementById('status_kek');
            if (inputLila && cekKek) {
                inputLila.addEventListener('input', function () {
                    const lila = parseFloat(inputLila.value);
                    if (!Number.isNaN(lila) && lila > 0 && lila < 23.5) {
                        cekKek.checked = true;
                    }
                });
            }

            // ===== Field wajib di tab lain: buka tab tersebut agar pesan error terlihat =====
            const formStunting = document.getElementById('form-stunting');

            function bukaTabDari(el) {
                const pane = el.closest('.tab-pane');
                const tombol = pane && tabButtons.find((btn) => btn.dataset.bsTarget === `#${pane.id}`);
                if (tombol && !pane.classList.contains('active')) {
                    bootstrap.Tab.getOrCreateInstance(tombol).show();
                }
            }

            formStunting.addEventListener('invalid', function (event) {
                bukaTabDari(event.target);
            }, true);

            const errorServer = formStunting.querySelector('.is-invalid');
            if (errorServer) {
                bukaTabDari(errorServer);
            }

            // ===== Isi otomatis dari data keluarga (modul kemiskinan) berdasarkan Nomor KK =====
            (function () {
                const form = document.getElementById('form-stunting');
                const inputKk = document.getElementById('nomor_kk');
                const info = document.getElementById('infoKeluargaKk');
                const wadahPilihAnak = document.getElementById('pilihAnakKk');
                const pilihAnak = document.getElementById('pilihAnakKkSelect');
                let kkTerakhir = null;
                let jeda = null;
                let daftarAnak = [];

                /** Isi field hanya bila masih kosong, agar isian petugas tidak tertimpa. */
                function isiJikaKosong(nama, nilai) {
                    const el = form.querySelector(`[name="${nama}"]`);
                    if (!el || nilai === null || nilai === undefined || nilai === '' || el.value) {
                        return false;
                    }
                    el.value = nilai;
                    el.classList.add('terisi-otomatis');
                    return true;
                }

                function isiWilayah(kecamatanId, desaId) {
                    if (!kecamatanId || kecamatanSelect.value) {
                        return;
                    }
                    kecamatanSelect.value = String(kecamatanId);
                    renderDesaOptions();
                    desaSelect.value = String(desaId || '');
                    kecamatanSelect.classList.add('terisi-otomatis');
                    desaSelect.classList.add('terisi-otomatis');
                }

                function isiOrangTua(peran, data) {
                    if (!data) {
                        return;
                    }
                    isiJikaKosong(`orang_tua[nik_${peran}]`, data.nik);
                    isiJikaKosong(`orang_tua[nama_${peran}_lengkap]`, data.nama);
                    isiJikaKosong(`orang_tua[tanggal_lahir_${peran}]`, data.tanggal_lahir);
                    isiJikaKosong(`orang_tua[pendidikan_${peran}]`, data.pendidikan);
                    isiJikaKosong(`orang_tua[pekerjaan_${peran}]`, data.pekerjaan);
                }

                function tampilkanPilihanAnak(anak) {
                    daftarAnak = anak || [];
                    pilihAnak.innerHTML = '<option value="">— Pilih anak (opsional) —</option>' + daftarAnak.map((a, i) =>
                        `<option value="${i}">${a.nama.replace(/</g, '&lt;')} (${a.tanggal_lahir})</option>`
                    ).join('');
                    wadahPilihAnak.classList.toggle('d-none', daftarAnak.length === 0);
                }

                pilihAnak.addEventListener('change', function () {
                    const a = daftarAnak[pilihAnak.value];
                    if (!a) {
                        return;
                    }
                    isiJikaKosong('nik_anak', a.nik);
                    isiJikaKosong('nama_anak', a.nama);
                    isiJikaKosong('tempat_lahir', a.tempat_lahir);
                    isiJikaKosong('tanggal_lahir', a.tanggal_lahir);
                    const jk = form.querySelector('[name="jenis_kelamin"]');
                    if (jk && a.jenis_kelamin) {
                        jk.value = a.jenis_kelamin;
                    }
                });

                async function cariKeluarga() {
                    const kk = inputKk.value.trim();
                    if (!/^\d{16}$/.test(kk) || kk === kkTerakhir) {
                        return;
                    }
                    kkTerakhir = kk;
                    info.className = 'form-text';
                    info.textContent = 'Mencari data keluarga…';

                    try {
                        const url = new URL(inputKk.dataset.urlCari, window.location.origin);
                        url.searchParams.set('nomor_kk', kk);
                        if (inputKk.dataset.kecualiAnak) {
                            url.searchParams.set('kecuali_anak_id', inputKk.dataset.kecualiAnak);
                        }
                        const respons = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const hasil = await respons.json();

                        if (!hasil.ditemukan) {
                            info.textContent = 'Nomor KK belum terdata di modul kemiskinan; silakan isi data secara manual.';
                            tampilkanPilihanAnak([]);
                            return;
                        }

                        isiOrangTua('ayah', hasil.ayah);
                        isiOrangTua('ibu', hasil.ibu);
                        isiJikaKosong('nomor_hp_orang_tua', hasil.keluarga.nomor_hp);
                        isiJikaKosong('alamat', hasil.keluarga.alamat);
                        isiWilayah(hasil.keluarga.kecamatan_id, hasil.keluarga.desa_kelurahan_id);
                        tampilkanPilihanAnak(hasil.anak);

                        const kurang = [!hasil.ayah && 'ayah', !hasil.ibu && 'ibu'].filter(Boolean);
                        info.className = kurang.length ? 'form-text text-warning-emphasis' : 'form-text text-success';
                        info.textContent = `Data keluarga ditemukan (KK a.n. ${hasil.keluarga.nama_kepala_keluarga}, ${hasil.keluarga.wilayah}). `
                            + 'Field kosong telah diisi otomatis — periksa kembali sebelum menyimpan.'
                            + (kurang.length ? ` Data ${kurang.join(' dan ')} tidak ada di Kartu Keluarga (anggota dengan hubungan Kepala Keluarga/Suami/Istri), silakan isi manual.` : '')
                            + (hasil.jumlah_balita_terdata && !hasil.anak.length ? ' Semua balita di KK ini sudah terdata di modul stunting.' : '')
                            + (hasil.jumlah_balita_terdata && hasil.anak.length ? ` ${hasil.jumlah_balita_terdata} balita lain di KK ini sudah terdata dan tidak ditampilkan.` : '');
                    } catch (e) {
                        kkTerakhir = null;
                        info.className = 'form-text text-danger';
                        info.textContent = 'Gagal mencari data keluarga. Coba lagi.';
                    }
                }

                inputKk.addEventListener('input', function () {
                    clearTimeout(jeda);
                    jeda = setTimeout(cariKeluarga, 400);
                });
                inputKk.addEventListener('blur', cariKeluarga);
            })();

            // ===== Dokumentasi (hanya untuk data baru) =====
            const dokumenContainer = document.getElementById('dokumen-container');
            if (dokumenContainer) {
                let dokumenIndex = 0;

                function addDokumenRow() {
                    const i = dokumenIndex++;
                    const div = document.createElement('div');
                    div.className = 'repeatable-row border rounded p-3 mb-2';
                    div.innerHTML = `
                        <button type="button" class="btn btn-sm btn-outline-danger float-end"><i class="bi bi-trash"></i></button>
                        <div class="row g-2">
                            <div class="col-md-5"><label class="form-label small">File Foto</label>
                                <input type="file" accept="image/*" name="dokumen[${i}][file]" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label small">Jenis Dokumentasi</label>
                                <select name="dokumen[${i}][jenis_dokumentasi]" class="form-select form-select-sm">
                                    <option value="foto_anak">Foto Anak</option>
                                    <option value="foto_pengukuran">Foto Pengukuran</option>
                                    <option value="foto_dokumen_pendukung">Foto Dokumen Pendukung</option>
                                    <option value="lainnya">Lainnya</option>
                                </select></div>
                            <div class="col-md-4"><label class="form-label small">Keterangan</label>
                                <input type="text" name="dokumen[${i}][keterangan]" class="form-control form-control-sm"></div>
                        </div>`;
                    div.querySelector('button').addEventListener('click', function () { div.remove(); });
                    dokumenContainer.appendChild(div);
                }

                document.getElementById('btn-add-dokumen').addEventListener('click', addDokumenRow);
            }

            // ===== Draft otomatis =====
            DraftFormulir.pasang(formStunting, {
                kunci: @json($kunciDraft),
                url: @json(route('draft-formulir.update', $kunciDraft)),
                userId: @json(auth()->id()),
                status: document.getElementById('status-draft'),
                abaikan: /^dokumen\[/,
                adaInputLama: @json($errors->any() || session()->hasOldInput()),
                diperbaruiPada: @json($anak?->updated_at?->toIso8601String()),
                repeaters: {},
            });
        })();
    </script>
@endpush
