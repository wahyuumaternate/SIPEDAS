@php
    $keluarga = $keluarga ?? null;
    $isEdit = (bool) $keluarga;

    $anggotaRows = old('anggota', $isEdit ? $keluarga->anggotaKeluarga->map(fn ($a) => [
        'nik' => $a->nik,
        'nama_lengkap' => $a->nama_lengkap,
        'jenis_kelamin' => $a->jenis_kelamin,
        'tempat_lahir' => $a->tempat_lahir,
        'tanggal_lahir' => optional($a->tanggal_lahir)->format('Y-m-d'),
        'hubungan_keluarga' => $a->hubungan_keluarga,
        'status_perkawinan' => $a->status_perkawinan,
        'pendidikan_terakhir' => $a->pendidikan_terakhir,
        'status_pekerjaan' => $a->status_pekerjaan,
        'disabilitas' => $a->disabilitas,
        'jenis_disabilitas' => $a->jenis_disabilitas,
        'penyakit_kronis' => $a->penyakit_kronis,
        'jenis_penyakit_kronis' => $a->jenis_penyakit_kronis,
    ])->all() : [[]]);

    $ekonomi = old('kondisi_ekonomi', $isEdit ? ($keluarga->kondisiEkonomi?->toArray() ?? []) : []);
    $rumah = old('kondisi_rumah', $isEdit ? ($keluarga->kondisiRumah?->toArray() ?? []) : []);
    $sosial = old('kondisi_sosial', $isEdit ? ($keluarga->kondisiSosial?->toArray() ?? []) : []);

    $asetRows = old('aset', $isEdit ? $keluarga->asetKeluarga->map(fn ($a) => $a->only([
        'jenis_aset', 'jumlah', 'status_kepemilikan', 'perkiraan_nilai', 'keterangan',
    ]))->all() : []);

    $programRows = old('program', $isEdit ? $keluarga->kepesertaanProgram->map(fn ($p) => $p->only([
        'nama_program', 'status_penerima', 'tahun_menerima', 'sumber_instansi', 'keterangan',
    ]))->all() : []);
@endphp

<style>
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

    .repeatable-row {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        position: relative;
    }

    .repeatable-row .btn-remove-row {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
</style>

<form method="POST" action="{{ $isEdit ? route('kemiskinan.update', $keluarga) : route('kemiskinan.store') }}"
    enctype="multipart/form-data" id="form-kemiskinan">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <ul class="nav form-tabs mb-3" id="formTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-identitas"
                type="button">1. Identitas</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-anggota"
                type="button">2. Anggota Keluarga</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ekonomi"
                type="button">3. Ekonomi</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rumah"
                type="button">4. Rumah</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-aset"
                type="button">5. Aset</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sosial"
                type="button">6. Sosial</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-program"
                type="button">7. Program</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dokumen"
                type="button">8. Dokumentasi</button></li>
    </ul>

    <div class="tab-content">
        {{-- ========== 1. IDENTITAS KELUARGA (PRD Bagian 9) ========== --}}
        <div class="tab-pane fade show active panel" id="tab-identitas">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nomor KK <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_kk" maxlength="16" class="form-control @error('nomor_kk') is-invalid @enderror"
                        value="{{ old('nomor_kk', $keluarga?->nomor_kk) }}" required>
                    @error('nomor_kk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK Kepala Keluarga <span class="text-danger">*</span></label>
                    <input type="text" name="nik_kepala_keluarga" maxlength="16"
                        class="form-control @error('nik_kepala_keluarga') is-invalid @enderror"
                        value="{{ old('nik_kepala_keluarga', $keluarga?->nik_kepala_keluarga) }}" required>
                    @error('nik_kepala_keluarga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Kepala Keluarga <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kepala_keluarga"
                        class="form-control @error('nama_kepala_keluarga') is-invalid @enderror"
                        value="{{ old('nama_kepala_keluarga', $keluarga?->nama_kepala_keluarga) }}" required>
                    @error('nama_kepala_keluarga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor HP</label>
                    <input type="text" name="nomor_hp" class="form-control"
                        value="{{ old('nomor_hp', $keluarga?->nomor_hp) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="status_perkawinan" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refStatusPerkawinan as $kode => $label)
                            <option value="{{ $kode }}" @selected(old('status_perkawinan', $keluarga?->status_perkawinan) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2" required>{{ old('alamat', $keluarga?->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">RT <span class="text-danger">*</span></label>
                    <input type="text" name="rt" maxlength="5" class="form-control"
                        value="{{ old('rt', $keluarga?->rt) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">RW <span class="text-danger">*</span></label>
                    <input type="text" name="rw" maxlength="5" class="form-control"
                        value="{{ old('rw', $keluarga?->rw) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                    <select name="kecamatan_id" id="kecamatan_id"
                        class="form-select @error('kecamatan_id') is-invalid @enderror" required>
                        <option value="">Pilih Kecamatan</option>
                        @foreach ($kecamatans as $kecamatan)
                            <option value="{{ $kecamatan->id }}" @selected(old('kecamatan_id', $keluarga?->kecamatan_id) == $kecamatan->id)>{{ $kecamatan->nama }}</option>
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

        {{-- ========== 2. ANGGOTA KELUARGA (PRD Bagian 10) ========== --}}
        <div class="tab-pane fade panel" id="tab-anggota">
            <p class="text-muted small">Minimal satu anggota keluarga harus diisi. Usia dihitung otomatis dari tanggal lahir.</p>
            @error('anggota') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
            <div id="anggota-container"></div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-anggota">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Anggota
            </button>
        </div>

        {{-- ========== 3. KONDISI EKONOMI (PRD Bagian 11) ========== --}}
        <div class="tab-pane fade panel" id="tab-ekonomi">
            <h2 class="h6">Pekerjaan</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Status Pekerjaan Kepala Keluarga</label>
                    <select name="kondisi_ekonomi[status_pekerjaan_kepala_keluarga]" class="form-select">
                        <option value="">Pilih</option>
                        @foreach ($refStatusPekerjaan as $kode => $label)
                            <option value="{{ $kode }}" @selected(($ekonomi['status_pekerjaan_kepala_keluarga'] ?? null) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pekerjaan Utama</label>
                    <input type="text" name="kondisi_ekonomi[pekerjaan_utama]" class="form-control"
                        value="{{ $ekonomi['pekerjaan_utama'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pekerjaan Tambahan</label>
                    <input type="text" name="kondisi_ekonomi[pekerjaan_tambahan]" class="form-control"
                        value="{{ $ekonomi['pekerjaan_tambahan'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jml. Anggota Bekerja</label>
                    <input type="number" min="0" name="kondisi_ekonomi[jumlah_anggota_bekerja]" class="form-control"
                        value="{{ $ekonomi['jumlah_anggota_bekerja'] ?? 0 }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jml. Anggota Tidak Bekerja</label>
                    <input type="number" min="0" name="kondisi_ekonomi[jumlah_anggota_tidak_bekerja]" class="form-control"
                        value="{{ $ekonomi['jumlah_anggota_tidak_bekerja'] ?? 0 }}">
                </div>
            </div>

            <h2 class="h6">Pendapatan (Rp)</h2>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pendapatan Kepala Keluarga</label>
                    <input type="number" min="0" step="0.01" name="kondisi_ekonomi[pendapatan_kepala_keluarga]"
                        class="form-control" value="{{ $ekonomi['pendapatan_kepala_keluarga'] ?? 0 }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pendapatan Pasangan</label>
                    <input type="number" min="0" step="0.01" name="kondisi_ekonomi[pendapatan_pasangan]"
                        class="form-control" value="{{ $ekonomi['pendapatan_pasangan'] ?? 0 }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pendapatan Anggota Lainnya</label>
                    <input type="number" min="0" step="0.01" name="kondisi_ekonomi[pendapatan_anggota_lainnya]"
                        class="form-control" value="{{ $ekonomi['pendapatan_anggota_lainnya'] ?? 0 }}">
                </div>
            </div>

            <h2 class="h6">Pengeluaran (Rp)</h2>
            <div class="row g-3">
                @foreach ([
                    'pengeluaran_makanan' => 'Makanan',
                    'pengeluaran_pendidikan' => 'Pendidikan',
                    'pengeluaran_kesehatan' => 'Kesehatan',
                    'pengeluaran_listrik' => 'Listrik',
                    'pengeluaran_air' => 'Air',
                    'pengeluaran_transportasi' => 'Transportasi',
                    'pengeluaran_lainnya' => 'Lainnya',
                ] as $field => $label)
                    <div class="col-md-3">
                        <label class="form-label">{{ $label }}</label>
                        <input type="number" min="0" step="0.01" name="kondisi_ekonomi[{{ $field }}]"
                            class="form-control" value="{{ $ekonomi[$field] ?? 0 }}">
                    </div>
                @endforeach
            </div>
            <p class="text-muted small mt-2 mb-0">Total pendapatan dan pengeluaran dihitung otomatis oleh sistem.</p>
        </div>

        {{-- ========== 4. KONDISI RUMAH (PRD Bagian 12) ========== --}}
        <div class="tab-pane fade panel" id="tab-rumah">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Kepemilikan <span class="text-danger">*</span></label>
                    <select name="kondisi_rumah[status_kepemilikan]" class="form-select" data-required-kirim="1">
                        <option value="">Pilih</option>
                        @foreach (['milik_sendiri' => 'Milik Sendiri', 'sewa' => 'Sewa', 'menumpang' => 'Menumpang', 'rumah_dinas' => 'Rumah Dinas', 'lainnya' => 'Lainnya'] as $val => $label)
                            <option value="{{ $val }}" @selected(($rumah['status_kepemilikan'] ?? null) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kondisi Bangunan <span class="text-danger">*</span></label>
                    <select name="kondisi_rumah[kondisi_bangunan]" class="form-select" data-required-kirim="1">
                        <option value="">Pilih</option>
                        @foreach (['permanen' => 'Permanen', 'semi_permanen' => 'Semi Permanen', 'tidak_layak_huni' => 'Tidak Layak Huni'] as $val => $label)
                            <option value="{{ $val }}" @selected(($rumah['kondisi_bangunan'] ?? null) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @foreach ([
                    'jenis_atap' => ['Jenis Atap', $refJenisAtap],
                    'jenis_dinding' => ['Jenis Dinding', $refJenisDinding],
                    'jenis_lantai' => ['Jenis Lantai', $refJenisLantai],
                    'sumber_listrik' => ['Sumber Listrik', $refSumberListrik],
                    'sumber_air' => ['Sumber Air', $refSumberAir],
                    'pengelolaan_sampah' => ['Pengelolaan Sampah', $refPengelolaanSampah],
                ] as $field => [$label, $refs])
                    <div class="col-md-4">
                        <label class="form-label">{{ $label }}</label>
                        <select name="kondisi_rumah[{{ $field }}]" class="form-select">
                            <option value="">Pilih</option>
                            @foreach ($refs as $kode => $refLabel)
                                <option value="{{ $kode }}" @selected(($rumah[$field] ?? null) === $kode)>{{ $refLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="kondisi_rumah[jamban]" value="1"
                        id="jamban" @checked($rumah['jamban'] ?? false)>
                    <label class="form-check-label" for="jamban">Memiliki Jamban</label>
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="kondisi_rumah[septic_tank]" value="1"
                        id="septic_tank" @checked($rumah['septic_tank'] ?? false)>
                    <label class="form-check-label" for="septic_tank">Memiliki Septic Tank</label>
                </div>
                <div class="col-md-3 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="kondisi_rumah[drainase]" value="1"
                        id="drainase" @checked($rumah['drainase'] ?? false)>
                    <label class="form-check-label" for="drainase">Memiliki Drainase</label>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Luas Tanah (m²)</label>
                    <input type="number" min="0" step="0.01" name="kondisi_rumah[luas_tanah]" class="form-control"
                        value="{{ $rumah['luas_tanah'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Luas Bangunan (m²)</label>
                    <input type="number" min="0" step="0.01" name="kondisi_rumah[luas_bangunan]" class="form-control"
                        value="{{ $rumah['luas_bangunan'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah Kamar</label>
                    <input type="number" min="0" name="kondisi_rumah[jumlah_kamar]" class="form-control"
                        value="{{ $rumah['jumlah_kamar'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah Penghuni</label>
                    <input type="number" min="0" name="kondisi_rumah[jumlah_penghuni]" class="form-control"
                        value="{{ $rumah['jumlah_penghuni'] ?? '' }}">
                </div>
            </div>
        </div>

        {{-- ========== 5. KEPEMILIKAN ASET (PRD Bagian 13) ========== --}}
        <div class="tab-pane fade panel" id="tab-aset">
            <div id="aset-container"></div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-aset">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Aset
            </button>
        </div>

        {{-- ========== 6. KONDISI SOSIAL & LAYANAN DASAR (PRD Bagian 14) ========== --}}
        <div class="tab-pane fade panel" id="tab-sosial">
            <div class="row g-3">
                @foreach ([
                    'jumlah_anak_usia_sekolah' => 'Jumlah Anak Usia Sekolah',
                    'jumlah_anak_bersekolah' => 'Jumlah Anak Bersekolah',
                    'jumlah_anak_putus_sekolah' => 'Jumlah Anak Putus Sekolah',
                    'jumlah_lansia' => 'Jumlah Lansia',
                    'jumlah_penyandang_disabilitas' => 'Jumlah Penyandang Disabilitas',
                    'jumlah_anggota_sakit' => 'Jumlah Anggota Sakit',
                ] as $field => $label)
                    <div class="col-md-4">
                        <label class="form-label">{{ $label }}</label>
                        <input type="number" min="0" name="kondisi_sosial[{{ $field }}]" class="form-control"
                            value="{{ $sosial[$field] ?? 0 }}">
                    </div>
                @endforeach
                <div class="col-md-4 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="kondisi_sosial[akses_fasilitas_kesehatan]"
                        value="1" id="akses_faskes" @checked($sosial['akses_fasilitas_kesehatan'] ?? false)>
                    <label class="form-check-label" for="akses_faskes">Memiliki Akses Fasilitas Kesehatan</label>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jarak Fasilitas Kesehatan (km)</label>
                    <input type="number" min="0" step="0.01" name="kondisi_sosial[jarak_fasilitas_kesehatan_km]"
                        class="form-control" value="{{ $sosial['jarak_fasilitas_kesehatan_km'] ?? '' }}">
                </div>
                <div class="col-md-4 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input" name="kondisi_sosial[akses_pendidikan]" value="1"
                        id="akses_pendidikan" @checked($sosial['akses_pendidikan'] ?? false)>
                    <label class="form-check-label" for="akses_pendidikan">Memiliki Akses Pendidikan</label>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jarak Sekolah (km)</label>
                    <input type="number" min="0" step="0.01" name="kondisi_sosial[jarak_sekolah_km]"
                        class="form-control" value="{{ $sosial['jarak_sekolah_km'] ?? '' }}">
                </div>
                <div class="col-md-4 form-check pt-4 mt-2">
                    <input type="checkbox" class="form-check-input"
                        name="kondisi_sosial[kepemilikan_dokumen_kependudukan]" value="1" id="dok_kependudukan"
                        @checked($sosial['kepemilikan_dokumen_kependudukan'] ?? false)>
                    <label class="form-check-label" for="dok_kependudukan">Memiliki Dokumen Kependudukan Lengkap</label>
                </div>
            </div>
        </div>

        {{-- ========== 7. KEPESERTAAN PROGRAM/BANTUAN (PRD Bagian 15) ========== --}}
        <div class="tab-pane fade panel" id="tab-program">
            <p class="text-muted small">Pendataan status kepesertaan saja, bukan pengajuan atau penyaluran bantuan.</p>
            <div id="program-container"></div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-program">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah Program
            </button>
        </div>

        {{-- ========== 8. DOKUMENTASI (PRD Bagian 16) ========== --}}
        <div class="tab-pane fade panel" id="tab-dokumen">
            @if ($isEdit && $keluarga->dokumen->isNotEmpty())
                <h2 class="h6">Dokumentasi Tersimpan</h2>
                <div class="row g-2 mb-3">
                    @foreach ($keluarga->dokumen as $dok)
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
                <p class="text-muted small">Unggah foto rumah, kondisi lingkungan, atau dokumen pendukung lainnya.</p>
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

    <div class="d-flex justify-content-end align-items-center gap-2 mt-3">
        <a href="{{ $isEdit ? route('kemiskinan.show', $keluarga) : route('kemiskinan.index') }}"
            class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save" aria-hidden="true"></i> Simpan Data
        </button>
    </div>
</form>

@if ($isEdit)
    @foreach ($keluarga->dokumen as $dok)
        <form id="hapus-dokumen-{{ $dok->id }}" method="POST"
            action="{{ route('kemiskinan.dokumen.destroy', [$keluarga, $dok]) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@push('scripts')
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

            const form = document.getElementById('form-kemiskinan');

            const kecamatanData = @json($kecamatans->keyBy('id')->map->desaKelurahans);
            const kecamatanSelect = document.getElementById('kecamatan_id');
            const desaSelect = document.getElementById('desa_kelurahan_id');
            const selectedDesaId = '{{ old('desa_kelurahan_id', $keluarga?->desa_kelurahan_id) }}';

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

            const refs = {
                statusPerkawinan: @json($refStatusPerkawinan),
                pendidikan: @json($refPendidikanTerakhir),
                pekerjaan: @json($refStatusPekerjaan),
                jenisAset: @json($refJenisAset),
            };

            function selectOptions(map, selected) {
                return '<option value="">Pilih</option>' + Object.entries(map).map(function ([kode, label]) {
                    return '<option value="' + kode + '"' + (String(selected) === kode ? ' selected' : '') + '>' + label + '</option>';
                }).join('');
            }

            // ===== Anggota Keluarga =====
            const anggotaData = @json($anggotaRows);
            const anggotaContainer = document.getElementById('anggota-container');
            let anggotaIndex = 0;

            function addAnggotaRow(data) {
                data = data || {};
                const i = anggotaIndex++;
                const div = document.createElement('div');
                div.className = 'repeatable-row';
                div.innerHTML = `
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label small">NIK</label>
                            <input type="text" maxlength="16" name="anggota[${i}][nik]" class="form-control form-control-sm" value="${data.nik || ''}"></div>
                        <div class="col-md-4"><label class="form-label small">Nama Lengkap *</label>
                            <input type="text" name="anggota[${i}][nama_lengkap]" class="form-control form-control-sm" value="${data.nama_lengkap || ''}" data-required-kirim="1"></div>
                        <div class="col-md-2"><label class="form-label small">Jenis Kelamin *</label>
                            <select name="anggota[${i}][jenis_kelamin]" class="form-select form-select-sm" data-required-kirim="1">
                                <option value="laki-laki" ${data.jenis_kelamin === 'laki-laki' ? 'selected' : ''}>Laki-laki</option>
                                <option value="perempuan" ${data.jenis_kelamin === 'perempuan' ? 'selected' : ''}>Perempuan</option>
                            </select></div>
                        <div class="col-md-3"><label class="form-label small">Hubungan Keluarga *</label>
                            <input type="text" name="anggota[${i}][hubungan_keluarga]" class="form-control form-control-sm" value="${data.hubungan_keluarga || ''}" data-required-kirim="1"></div>
                        <div class="col-md-3"><label class="form-label small">Tempat Lahir</label>
                            <input type="text" name="anggota[${i}][tempat_lahir]" class="form-control form-control-sm" value="${data.tempat_lahir || ''}"></div>
                        <div class="col-md-3"><label class="form-label small">Tanggal Lahir *</label>
                            <input type="date" name="anggota[${i}][tanggal_lahir]" class="form-control form-control-sm" value="${data.tanggal_lahir || ''}" data-required-kirim="1"></div>
                        <div class="col-md-3"><label class="form-label small">Status Perkawinan</label>
                            <select name="anggota[${i}][status_perkawinan]" class="form-select form-select-sm">${selectOptions(refs.statusPerkawinan, data.status_perkawinan)}</select></div>
                        <div class="col-md-3"><label class="form-label small">Pendidikan Terakhir</label>
                            <select name="anggota[${i}][pendidikan_terakhir]" class="form-select form-select-sm">${selectOptions(refs.pendidikan, data.pendidikan_terakhir)}</select></div>
                        <div class="col-md-3"><label class="form-label small">Status Pekerjaan</label>
                            <select name="anggota[${i}][status_pekerjaan]" class="form-select form-select-sm">${selectOptions(refs.pekerjaan, data.status_pekerjaan)}</select></div>
                        <div class="col-md-3 d-flex align-items-end gap-3">
                            <div class="form-check"><input type="hidden" name="anggota[${i}][disabilitas]" value="0"><input type="checkbox" class="form-check-input" name="anggota[${i}][disabilitas]" value="1" ${data.disabilitas ? 'checked' : ''}><label class="form-check-label small">Disabilitas</label></div>
                            <div class="form-check"><input type="hidden" name="anggota[${i}][penyakit_kronis]" value="0"><input type="checkbox" class="form-check-input" name="anggota[${i}][penyakit_kronis]" value="1" ${data.penyakit_kronis ? 'checked' : ''}><label class="form-check-label small">Penyakit Kronis</label></div>
                        </div>
                    </div>`;
                div.querySelector('.btn-remove-row').addEventListener('click', function () { div.remove(); });
                anggotaContainer.appendChild(div);
            }

            (anggotaData.length ? anggotaData : [{}]).forEach(addAnggotaRow);
            document.getElementById('btn-add-anggota').addEventListener('click', function () { addAnggotaRow(); });

            // ===== Aset Keluarga =====
            const asetData = @json($asetRows);
            const asetContainer = document.getElementById('aset-container');
            let asetIndex = 0;

            function addAsetRow(data) {
                data = data || {};
                const i = asetIndex++;
                const div = document.createElement('div');
                div.className = 'repeatable-row';
                div.innerHTML = `
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label small">Jenis Aset</label>
                            <select name="aset[${i}][jenis_aset]" class="form-select form-select-sm">${selectOptions(refs.jenisAset, data.jenis_aset)}</select></div>
                        <div class="col-md-2"><label class="form-label small">Jumlah</label>
                            <input type="number" min="1" name="aset[${i}][jumlah]" class="form-control form-control-sm" value="${data.jumlah || ''}"></div>
                        <div class="col-md-3"><label class="form-label small">Status Kepemilikan</label>
                            <select name="aset[${i}][status_kepemilikan]" class="form-select form-select-sm">
                                <option value="milik_sendiri" ${data.status_kepemilikan === 'milik_sendiri' ? 'selected' : ''}>Milik Sendiri</option>
                                <option value="sewa" ${data.status_kepemilikan === 'sewa' ? 'selected' : ''}>Sewa</option>
                                <option value="lainnya" ${data.status_kepemilikan === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                            </select></div>
                        <div class="col-md-4"><label class="form-label small">Perkiraan Nilai (Rp)</label>
                            <input type="number" min="0" step="0.01" name="aset[${i}][perkiraan_nilai]" class="form-control form-control-sm" value="${data.perkiraan_nilai || ''}"></div>
                        <div class="col-12"><label class="form-label small">Keterangan</label>
                            <input type="text" name="aset[${i}][keterangan]" class="form-control form-control-sm" value="${data.keterangan || ''}"></div>
                    </div>`;
                div.querySelector('.btn-remove-row').addEventListener('click', function () { div.remove(); });
                asetContainer.appendChild(div);
            }

            asetData.forEach(addAsetRow);
            document.getElementById('btn-add-aset').addEventListener('click', function () { addAsetRow(); });

            // ===== Kepesertaan Program =====
            const programData = @json($programRows);
            const programContainer = document.getElementById('program-container');
            let programIndex = 0;

            function addProgramRow(data) {
                data = data || {};
                const i = programIndex++;
                const div = document.createElement('div');
                div.className = 'repeatable-row';
                div.innerHTML = `
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label small">Nama Program</label>
                            <input type="text" name="program[${i}][nama_program]" class="form-control form-control-sm" value="${data.nama_program || ''}"></div>
                        <div class="col-md-3"><label class="form-label small">Status Penerima</label>
                            <select name="program[${i}][status_penerima]" class="form-select form-select-sm">
                                <option value="">Pilih</option>
                                <option value="penerima" ${data.status_penerima === 'penerima' ? 'selected' : ''}>Penerima</option>
                                <option value="bukan_penerima" ${data.status_penerima === 'bukan_penerima' ? 'selected' : ''}>Bukan Penerima</option>
                                <option value="pernah_menerima" ${data.status_penerima === 'pernah_menerima' ? 'selected' : ''}>Pernah Menerima</option>
                            </select></div>
                        <div class="col-md-2"><label class="form-label small">Tahun Menerima</label>
                            <input type="number" name="program[${i}][tahun_menerima]" class="form-control form-control-sm" value="${data.tahun_menerima || ''}"></div>
                        <div class="col-md-3"><label class="form-label small">Sumber/Instansi</label>
                            <input type="text" name="program[${i}][sumber_instansi]" class="form-control form-control-sm" value="${data.sumber_instansi || ''}"></div>
                        <div class="col-12"><label class="form-label small">Keterangan</label>
                            <input type="text" name="program[${i}][keterangan]" class="form-control form-control-sm" value="${data.keterangan || ''}"></div>
                    </div>`;
                div.querySelector('.btn-remove-row').addEventListener('click', function () { div.remove(); });
                programContainer.appendChild(div);
            }

            programData.forEach(addProgramRow);
            document.getElementById('btn-add-program').addEventListener('click', function () { addProgramRow(); });

            // ===== Dokumentasi (hanya untuk data baru) =====
            const dokumenContainer = document.getElementById('dokumen-container');
            if (dokumenContainer) {
                let dokumenIndex = 0;

                function addDokumenRow() {
                    const i = dokumenIndex++;
                    const div = document.createElement('div');
                    div.className = 'repeatable-row';
                    div.innerHTML = `
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button>
                        <div class="row g-2">
                            <div class="col-md-5"><label class="form-label small">File Foto</label>
                                <input type="file" accept="image/*" name="dokumen[${i}][file]" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label small">Jenis Dokumentasi</label>
                                <select name="dokumen[${i}][jenis_dokumentasi]" class="form-select form-select-sm">
                                    <option value="foto_rumah">Foto Rumah</option>
                                    <option value="foto_lingkungan">Foto Lingkungan</option>
                                    <option value="foto_dokumen_pendukung">Foto Dokumen Pendukung</option>
                                    <option value="lainnya">Lainnya</option>
                                </select></div>
                            <div class="col-md-4"><label class="form-label small">Keterangan</label>
                                <input type="text" name="dokumen[${i}][keterangan]" class="form-control form-control-sm"></div>
                        </div>`;
                    div.querySelector('.btn-remove-row').addEventListener('click', function () { div.remove(); });
                    dokumenContainer.appendChild(div);
                }

                document.getElementById('btn-add-dokumen').addEventListener('click', addDokumenRow);
            }
        })();
    </script>
@endpush
