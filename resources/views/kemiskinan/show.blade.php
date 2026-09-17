@extends('layouts.app')

@section('title', 'Detail Keluarga - ' . $keluarga->nama_kepala_keluarga)

@php
    $statusBadgeMap = [
        'draft' => 'secondary',
        'dikirim' => 'info',
        'dalam_verifikasi' => 'warning',
        'perlu_perbaikan' => 'warning',
        'valid' => 'success',
        'tidak_valid' => 'danger',
        'duplikat' => 'purple',
    ];
    $statusLabelMap = [
        'draft' => 'Draft',
        'dikirim' => 'Dikirim',
        'dalam_verifikasi' => 'Dalam Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'valid' => 'Valid',
        'tidak_valid' => 'Tidak Valid',
        'duplikat' => 'Duplikat',
    ];
    $transisiVerifikasi = [
        'dikirim' => ['dalam_verifikasi' => 'Mulai Verifikasi', 'valid' => 'Tandai Valid', 'perlu_perbaikan' => 'Perlu Perbaikan', 'tidak_valid' => 'Tandai Tidak Valid', 'duplikat' => 'Tandai Duplikat'],
        'dalam_verifikasi' => ['valid' => 'Tandai Valid', 'perlu_perbaikan' => 'Perlu Perbaikan', 'tidak_valid' => 'Tandai Tidak Valid', 'duplikat' => 'Tandai Duplikat'],
    ];
@endphp

@section('page-actions')
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('kemiskinan.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
    <a class="btn btn-primary btn-sm" href="{{ route('kemiskinan.edit', $keluarga) }}"><i class="bi bi-pencil"></i> Ubah</a>
@endsection

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-house-heart',
        'eyebrow' => 'Kemiskinan Ekstrem · ' . $keluarga->kode_pendataan,
        'title' => $keluarga->nama_kepala_keluarga,
        'description' => $keluarga->kecamatan?->nama . ', ' . $keluarga->desaKelurahan?->nama,
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('duplikat_warning'))
        <div class="alert alert-warning">{{ session('duplikat_warning') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="panel h-100">
                <span class="text-muted small">Status Data</span>
                <p class="h5 mb-0">
                    <span class="badge text-bg-{{ $statusBadgeMap[$keluarga->status_data] ?? 'secondary' }}">
                        {{ $statusLabelMap[$keluarga->status_data] ?? $keluarga->status_data }}
                    </span>
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel h-100">
                <span class="text-muted small">Petugas Pendata</span>
                <p class="h6 mb-0">{{ $keluarga->petugas?->nama }}</p>
                <span class="text-muted small">{{ $keluarga->tanggal_input?->format('d M Y H:i') }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel h-100">
                <span class="text-muted small">Jumlah Anggota Keluarga</span>
                <p class="h5 mb-0">{{ $keluarga->jumlah_anggota_keluarga }} orang</p>
            </div>
        </div>
    </div>

    @if ($duplikatLain->isNotEmpty())
        <div class="alert alert-warning">
            <strong>Potensi duplikasi:</strong> ditemukan data lain dengan NIK/Nomor KK yang sama:
            <ul class="mb-0">
                @foreach ($duplikatLain as $d)
                    <li><a href="{{ route('kemiskinan.show', $d) }}">{{ $d->kode_pendataan }} - {{ $d->nama_kepala_keluarga }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        @if (in_array($keluarga->status_data, ['draft', 'perlu_perbaikan'], true))
            <form method="POST" action="{{ route('kemiskinan.kirim', $keluarga) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Kirim untuk Verifikasi</button>
            </form>
        @endif

        @foreach ($transisiVerifikasi[$keluarga->status_data] ?? [] as $status => $label)
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalVerifikasi-{{ $status }}">{{ $label }}</button>
        @endforeach

        <form method="POST" action="{{ route('kemiskinan.destroy', $keluarga) }}"
            onsubmit="return confirm('Hapus data keluarga ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
        </form>
    </div>

    @foreach ($transisiVerifikasi[$keluarga->status_data] ?? [] as $status => $label)
        <div class="modal fade" id="modalVerifikasi-{{ $status }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('kemiskinan.verifikasi.store', $keluarga) }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Catatan @if (in_array($status, ['perlu_perbaikan', 'tidak_valid', 'duplikat'])) <span class="text-danger">*</span> @endif</label>
                        <textarea name="catatan" class="form-control" rows="3"
                            {{ in_array($status, ['perlu_perbaikan', 'tidak_valid', 'duplikat']) ? 'required' : '' }}></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <ul class="nav form-tabs mb-3" id="detailTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#d-identitas" type="button">Identitas</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-anggota" type="button">Anggota ({{ $keluarga->anggotaKeluarga->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-ekonomi" type="button">Ekonomi</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-rumah" type="button">Rumah</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-aset" type="button">Aset ({{ $keluarga->asetKeluarga->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-sosial" type="button">Sosial</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-program" type="button">Program</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-dokumen" type="button">Dokumentasi ({{ $keluarga->dokumen->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-riwayat" type="button">Riwayat Verifikasi</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active panel" id="d-identitas">
            <dl class="row mb-0">
                <dt class="col-sm-3">Nomor KK</dt><dd class="col-sm-9">{{ $keluarga->nomor_kk }}</dd>
                <dt class="col-sm-3">NIK Kepala Keluarga</dt><dd class="col-sm-9">{{ $keluarga->nik_kepala_keluarga }}</dd>
                <dt class="col-sm-3">Nomor HP</dt><dd class="col-sm-9">{{ $keluarga->nomor_hp ?: '-' }}</dd>
                <dt class="col-sm-3">Status Perkawinan</dt><dd class="col-sm-9">{{ $keluarga->status_perkawinan_label ?: '-' }}</dd>
                <dt class="col-sm-3">Alamat</dt><dd class="col-sm-9">{{ $keluarga->alamat }} (RT {{ $keluarga->rt }}/RW {{ $keluarga->rw }})</dd>
                <dt class="col-sm-3">Wilayah</dt><dd class="col-sm-9">{{ $keluarga->desaKelurahan?->nama }}, {{ $keluarga->kecamatan?->nama }}</dd>
            </dl>
        </div>

        <div class="tab-pane fade panel" id="d-anggota">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Nama</th><th>NIK</th><th>Jenis Kelamin</th><th>Tgl Lahir</th><th>Usia</th><th>Hubungan</th><th>Pendidikan</th><th>Pekerjaan</th><th>Ket.</th></tr></thead>
                    <tbody>
                        @forelse ($keluarga->anggotaKeluarga as $a)
                            <tr>
                                <td>{{ $a->nama_lengkap }}</td>
                                <td>{{ $a->nik ?: '-' }}</td>
                                <td>{{ ucfirst($a->jenis_kelamin) }}</td>
                                <td>{{ optional($a->tanggal_lahir)->format('d M Y') }}</td>
                                <td>{{ $a->usia_terkini }} th</td>
                                <td>{{ $a->hubungan_keluarga }}</td>
                                <td>{{ $a->pendidikan_terakhir_label ?: '-' }}</td>
                                <td>{{ $a->status_pekerjaan_label ?: '-' }}</td>
                                <td>
                                    @if ($a->disabilitas) <span class="badge text-bg-warning">Disabilitas</span> @endif
                                    @if ($a->penyakit_kronis) <span class="badge text-bg-danger">Penyakit Kronis</span> @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-3">Belum ada data anggota.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade panel" id="d-ekonomi">
            @php($ek = $keluarga->kondisiEkonomi)
            @if ($ek)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Pekerjaan Utama</dt><dd class="col-sm-8">{{ $ek->pekerjaan_utama ?: '-' }}</dd>
                    <dt class="col-sm-4">Status Pekerjaan Kepala Keluarga</dt><dd class="col-sm-8">{{ $ek->status_pekerjaan_kepala_keluarga_label ?: '-' }}</dd>
                    <dt class="col-sm-4">Total Pendapatan</dt><dd class="col-sm-8">Rp {{ number_format($ek->total_pendapatan, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">Total Pengeluaran</dt><dd class="col-sm-8">Rp {{ number_format($ek->total_pengeluaran, 0, ',', '.') }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data kondisi ekonomi.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-rumah">
            @php($rmh = $keluarga->kondisiRumah)
            @if ($rmh)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Status Kepemilikan</dt><dd class="col-sm-8">{{ str($rmh->status_kepemilikan)->replace('_', ' ')->title() }}</dd>
                    <dt class="col-sm-4">Kondisi Bangunan</dt><dd class="col-sm-8">{{ str($rmh->kondisi_bangunan)->replace('_', ' ')->title() }}</dd>
                    <dt class="col-sm-4">Jenis Atap / Dinding / Lantai</dt><dd class="col-sm-8">{{ $rmh->jenis_atap_label }} / {{ $rmh->jenis_dinding_label }} / {{ $rmh->jenis_lantai_label }}</dd>
                    <dt class="col-sm-4">Sumber Listrik / Air</dt><dd class="col-sm-8">{{ $rmh->sumber_listrik_label }} / {{ $rmh->sumber_air_label }}</dd>
                    <dt class="col-sm-4">Fasilitas</dt>
                    <dd class="col-sm-8">
                        @if ($rmh->jamban) <span class="badge text-bg-success">Jamban</span> @endif
                        @if ($rmh->septic_tank) <span class="badge text-bg-success">Septic Tank</span> @endif
                        @if ($rmh->drainase) <span class="badge text-bg-success">Drainase</span> @endif
                    </dd>
                    <dt class="col-sm-4">Luas Tanah / Bangunan</dt><dd class="col-sm-8">{{ $rmh->luas_tanah }} m² / {{ $rmh->luas_bangunan }} m²</dd>
                    <dt class="col-sm-4">Jumlah Kamar / Penghuni</dt><dd class="col-sm-8">{{ $rmh->jumlah_kamar }} / {{ $rmh->jumlah_penghuni }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data kondisi rumah.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-aset">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Jenis Aset</th><th>Jumlah</th><th>Status Kepemilikan</th><th>Perkiraan Nilai</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @forelse ($keluarga->asetKeluarga as $a)
                            <tr>
                                <td>{{ $a->jenis_aset_label }}</td>
                                <td>{{ $a->jumlah }}</td>
                                <td>{{ str($a->status_kepemilikan)->replace('_', ' ')->title() }}</td>
                                <td>Rp {{ number_format($a->perkiraan_nilai ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $a->keterangan }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data aset.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade panel" id="d-sosial">
            @php($sos = $keluarga->kondisiSosial)
            @if ($sos)
                <dl class="row mb-0">
                    <dt class="col-sm-5">Anak Usia Sekolah / Bersekolah / Putus Sekolah</dt>
                    <dd class="col-sm-7">{{ $sos->jumlah_anak_usia_sekolah }} / {{ $sos->jumlah_anak_bersekolah }} / {{ $sos->jumlah_anak_putus_sekolah }}</dd>
                    <dt class="col-sm-5">Lansia / Disabilitas / Anggota Sakit</dt>
                    <dd class="col-sm-7">{{ $sos->jumlah_lansia }} / {{ $sos->jumlah_penyandang_disabilitas }} / {{ $sos->jumlah_anggota_sakit }}</dd>
                    <dt class="col-sm-5">Akses Fasilitas Kesehatan</dt>
                    <dd class="col-sm-7">{{ $sos->akses_fasilitas_kesehatan ? 'Ya' : 'Tidak' }} ({{ $sos->jarak_fasilitas_kesehatan_km }} km)</dd>
                    <dt class="col-sm-5">Akses Pendidikan</dt>
                    <dd class="col-sm-7">{{ $sos->akses_pendidikan ? 'Ya' : 'Tidak' }} ({{ $sos->jarak_sekolah_km }} km)</dd>
                    <dt class="col-sm-5">Dokumen Kependudukan Lengkap</dt>
                    <dd class="col-sm-7">{{ $sos->kepemilikan_dokumen_kependudukan ? 'Ya' : 'Tidak' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data kondisi sosial.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-program">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Nama Program</th><th>Status</th><th>Tahun</th><th>Sumber/Instansi</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        @forelse ($keluarga->kepesertaanProgram as $p)
                            <tr>
                                <td>{{ $p->nama_program }}</td>
                                <td>{{ str($p->status_penerima)->replace('_', ' ')->title() }}</td>
                                <td>{{ $p->tahun_menerima }}</td>
                                <td>{{ $p->sumber_instansi }}</td>
                                <td>{{ $p->keterangan }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data kepesertaan program.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade panel" id="d-dokumen">
            <div class="row g-3 mb-3">
                @forelse ($keluarga->dokumen as $dok)
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <img src="{{ Storage::url($dok->path_file) }}" class="img-fluid rounded mb-1"
                                style="max-height:120px;object-fit:cover;" alt="{{ $dok->nama_file }}">
                            <p class="small mb-0 text-truncate">{{ str($dok->jenis_dokumentasi)->replace('_', ' ')->title() }}</p>
                            <p class="text-muted small mb-1">{{ $dok->pengunggah?->nama }} · {{ $dok->tanggal_upload?->format('d M Y') }}</p>
                            <form method="POST" action="{{ route('kemiskinan.dokumen.destroy', [$keluarga, $dok]) }}"
                                onsubmit="return confirm('Hapus dokumentasi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted text-center py-3">Belum ada dokumentasi.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('kemiskinan.dokumen.store', $keluarga) }}" enctype="multipart/form-data"
                class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">Unggah Foto</label>
                    <input type="file" name="file" accept="image/*" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Jenis Dokumentasi</label>
                    <select name="jenis_dokumentasi" class="form-select form-select-sm" required>
                        <option value="foto_rumah">Foto Rumah</option>
                        <option value="foto_lingkungan">Foto Lingkungan</option>
                        <option value="foto_dokumen_pendukung">Foto Dokumen Pendukung</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Unggah</button>
                </div>
            </form>
        </div>

        <div class="tab-pane fade panel" id="d-riwayat">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Tanggal</th><th>Status</th><th>Verifikator</th><th>Catatan</th></tr></thead>
                    <tbody>
                        @forelse ($keluarga->riwayatVerifikasi->sortByDesc('tanggal_verifikasi') as $v)
                            <tr>
                                <td>{{ $v->tanggal_verifikasi?->format('d M Y H:i') }}</td>
                                <td><span class="badge text-bg-{{ $statusBadgeMap[$v->status] ?? 'secondary' }}">{{ $statusLabelMap[$v->status] ?? $v->status }}</span></td>
                                <td>{{ $v->verifikator?->nama }}</td>
                                <td>{{ $v->catatan }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat verifikasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
