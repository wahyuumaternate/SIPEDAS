@extends('layouts.app')

@section('title', 'Detail Keluarga - ' . $keluarga->nama_kepala_keluarga)

@php
    $statusBadgeMap = [
        'dalam_verifikasi' => 'warning',
        'perlu_perbaikan' => 'warning',
        'valid' => 'success',
        'tidak_valid' => 'danger',
    ];
    $statusLabelMap = [
        'dalam_verifikasi' => 'Dalam Verifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'valid' => 'Valid',
        'tidak_valid' => 'Tidak Valid',
    ];
    $transisiVerifikasi = [
        'dalam_verifikasi' => ['valid' => 'Setujui (Valid)', 'perlu_perbaikan' => 'Perlu Perbaikan', 'tidak_valid' => 'Tandai Tidak Valid'],
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


    @push('styles')
        <style>
            .kv { display: grid; grid-template-columns: minmax(120px, 38%) 1fr; gap: .55rem 1rem; margin: 0; }
            .kv dt { color: var(--admin-muted); font-weight: 500; font-size: .85rem; }
            .kv dd { margin: 0; font-weight: 500; word-break: break-word; }
            .sec-title { display: flex; align-items: center; gap: .6rem; font-size: 1rem; font-weight: 700; margin-bottom: 1rem; }
            .sec-title i { display: inline-grid; place-items: center; width: 32px; height: 32px; border-radius: 9px; background: rgba(37, 99, 235, .1); color: var(--admin-primary); }
            .sec-title .badge { margin-left: auto; }
            .stat-tile { display: flex; align-items: center; gap: .9rem; }
            .stat-tile .ico { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 1.25rem; background: rgba(37, 99, 235, .1); color: var(--admin-primary); flex: none; }
            .stat-tile small { color: var(--admin-muted); display: block; }
            .show-table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--admin-muted); font-weight: 600; white-space: nowrap; }
            .doc-thumb img { height: 130px; width: 100%; object-fit: cover; }
        </style>
    @endpush

    {{-- Ringkasan --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="panel h-100 stat-tile">
                <span class="ico"><i class="bi bi-flag"></i></span>
                <div>
                    <small>Status Data</small>
                    <span class="badge text-bg-{{ $statusBadgeMap[$keluarga->status_data] ?? 'secondary' }} fs-6">
                        {{ $statusLabelMap[$keluarga->status_data] ?? $keluarga->status_data }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel h-100 stat-tile">
                <span class="ico"><i class="bi bi-person-badge"></i></span>
                <div>
                    <small>Petugas Pendata</small>
                    <strong>{{ $keluarga->petugas?->nama ?? '-' }}</strong>
                    <small>{{ $keluarga->tanggal_input?->format('d M Y H:i') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel h-100 stat-tile">
                <span class="ico"><i class="bi bi-people"></i></span>
                <div>
                    <small>Jumlah Anggota Keluarga</small>
                    <strong class="fs-5">{{ $keluarga->jumlah_anggota_keluarga }} orang</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Aksi --}}
    <div class="panel mb-3 d-flex flex-wrap align-items-center gap-2 py-3">
        <span class="text-muted small me-2">Aksi:</span>
        @foreach ($transisiVerifikasi[$keluarga->status_data] ?? [] as $status => $label)
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalVerifikasi-{{ $status }}">{{ $label }}</button>
        @endforeach

        <form method="POST" action="{{ route('kemiskinan.destroy', $keluarga) }}" class="ms-auto"
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
                        <label class="form-label">Catatan @if (in_array($status, ['perlu_perbaikan', 'tidak_valid'])) <span class="text-danger">*</span> @endif</label>
                        <textarea name="catatan" class="form-control" rows="3"
                            {{ in_array($status, ['perlu_perbaikan', 'tidak_valid']) ? 'required' : '' }}></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @php
        $ek = $keluarga->kondisiEkonomi;
        $rmh = $keluarga->kondisiRumah;
        $sos = $keluarga->kondisiSosial;
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-card-text"></i> Identitas</div>
                <dl class="kv">
                    <dt>Nomor KK</dt><dd>{{ $keluarga->nomor_kk }}</dd>
                    <dt>NIK Kepala Keluarga</dt><dd>{{ $keluarga->nik_kepala_keluarga }}</dd>
                    <dt>Nomor HP</dt><dd>{{ $keluarga->nomor_hp ?: '-' }}</dd>
                    <dt>Status Perkawinan</dt><dd>{{ $keluarga->status_perkawinan_label ?: '-' }}</dd>
                    <dt>Alamat</dt><dd>{{ $keluarga->alamat }} (RT {{ $keluarga->rt }}/RW {{ $keluarga->rw }})</dd>
                    <dt>Wilayah</dt><dd>{{ $keluarga->desaKelurahan?->nama }}, {{ $keluarga->kecamatan?->nama }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-cash-coin"></i> Kondisi Ekonomi</div>
                @if ($ek)
                    <dl class="kv">
                        <dt>Pekerjaan Utama</dt><dd>{{ $ek->pekerjaan_utama ?: '-' }}</dd>
                        <dt>Status Pekerjaan KK</dt><dd>{{ $ek->status_pekerjaan_kepala_keluarga_label ?: '-' }}</dd>
                        <dt>Total Pendapatan</dt><dd>Rp {{ number_format($ek->total_pendapatan, 0, ',', '.') }}</dd>
                        <dt>Total Pengeluaran</dt><dd>Rp {{ number_format($ek->total_pengeluaran, 0, ',', '.') }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">Belum ada data kondisi ekonomi.</p>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-house-door"></i> Kondisi Rumah</div>
                @if ($rmh)
                    <dl class="kv">
                        <dt>Status Kepemilikan</dt><dd>{{ str($rmh->status_kepemilikan)->replace('_', ' ')->title() }}</dd>
                        <dt>Kondisi Bangunan</dt><dd>{{ str($rmh->kondisi_bangunan)->replace('_', ' ')->title() }}</dd>
                        <dt>Atap / Dinding / Lantai</dt><dd>{{ $rmh->jenis_atap_label }} / {{ $rmh->jenis_dinding_label }} / {{ $rmh->jenis_lantai_label }}</dd>
                        <dt>Listrik / Air</dt><dd>{{ $rmh->sumber_listrik_label }} / {{ $rmh->sumber_air_label }}</dd>
                        <dt>Fasilitas</dt>
                        <dd>
                            @if ($rmh->jamban) <span class="badge text-bg-success">Jamban</span> @endif
                            @if ($rmh->septic_tank) <span class="badge text-bg-success">Septic Tank</span> @endif
                            @if ($rmh->drainase) <span class="badge text-bg-success">Drainase</span> @endif
                            @if (! $rmh->jamban && ! $rmh->septic_tank && ! $rmh->drainase) - @endif
                        </dd>
                        <dt>Luas Tanah / Bangunan</dt><dd>{{ $rmh->luas_tanah }} m² / {{ $rmh->luas_bangunan }} m²</dd>
                        <dt>Kamar / Penghuni</dt><dd>{{ $rmh->jumlah_kamar }} / {{ $rmh->jumlah_penghuni }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">Belum ada data kondisi rumah.</p>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-people"></i> Kondisi Sosial</div>
                @if ($sos)
                    <dl class="kv">
                        <dt>Anak Usia Sekolah / Bersekolah / Putus</dt>
                        <dd>{{ $sos->jumlah_anak_usia_sekolah }} / {{ $sos->jumlah_anak_bersekolah }} / {{ $sos->jumlah_anak_putus_sekolah }}</dd>
                        <dt>Lansia / Disabilitas / Sakit</dt>
                        <dd>{{ $sos->jumlah_lansia }} / {{ $sos->jumlah_penyandang_disabilitas }} / {{ $sos->jumlah_anggota_sakit }}</dd>
                        <dt>Akses Fasilitas Kesehatan</dt>
                        <dd>{{ $sos->akses_fasilitas_kesehatan ? 'Ya' : 'Tidak' }} ({{ $sos->jarak_fasilitas_kesehatan_km }} km)</dd>
                        <dt>Akses Pendidikan</dt>
                        <dd>{{ $sos->akses_pendidikan ? 'Ya' : 'Tidak' }} ({{ $sos->jarak_sekolah_km }} km)</dd>
                        <dt>Dokumen Kependudukan Lengkap</dt>
                        <dd>{{ $sos->kepemilikan_dokumen_kependudukan ? 'Ya' : 'Tidak' }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">Belum ada data kondisi sosial.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="panel mb-3">
        <div class="sec-title"><i class="bi bi-person-lines-fill"></i> Anggota Keluarga <span class="badge text-bg-secondary">{{ $keluarga->anggotaKeluarga->count() }}</span></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 show-table">
                <thead><tr><th>Nama</th><th>NIK</th><th>JK</th><th>Tgl Lahir</th><th>Usia</th><th>Hubungan</th><th>Pendidikan</th><th>Pekerjaan</th><th>Ket.</th></tr></thead>
                <tbody>
                    @forelse ($keluarga->anggotaKeluarga as $a)
                        <tr>
                            <td class="fw-semibold">{{ $a->nama_lengkap }}</td>
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

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-box-seam"></i> Aset <span class="badge text-bg-secondary">{{ $keluarga->asetKeluarga->count() }}</span></div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 show-table">
                        <thead><tr><th>Jenis</th><th>Jml</th><th>Kepemilikan</th><th>Nilai</th></tr></thead>
                        <tbody>
                            @forelse ($keluarga->asetKeluarga as $a)
                                <tr>
                                    <td>{{ $a->jenis_aset_label }}</td>
                                    <td>{{ $a->jumlah }}</td>
                                    <td>{{ str($a->status_kepemilikan)->replace('_', ' ')->title() }}</td>
                                    <td>Rp {{ number_format($a->perkiraan_nilai ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data aset.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-gift"></i> Kepesertaan Program <span class="badge text-bg-secondary">{{ $keluarga->kepesertaanProgram->count() }}</span></div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 show-table">
                        <thead><tr><th>Program</th><th>Status</th><th>Tahun</th><th>Instansi</th></tr></thead>
                        <tbody>
                            @forelse ($keluarga->kepesertaanProgram as $p)
                                <tr>
                                    <td>{{ $p->nama_program }}</td>
                                    <td>{{ str($p->status_penerima)->replace('_', ' ')->title() }}</td>
                                    <td>{{ $p->tahun_menerima }}</td>
                                    <td>{{ $p->sumber_instansi }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data kepesertaan program.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="panel mb-3">
        <div class="sec-title"><i class="bi bi-images"></i> Dokumentasi <span class="badge text-bg-secondary">{{ $keluarga->dokumen->count() }}</span></div>
            <div class="row g-3 mb-3">
                @forelse ($keluarga->dokumen as $dok)
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center h-100 doc-thumb">
                            <img src="{{ Storage::url($dok->path_file) }}" class="img-fluid rounded mb-1" alt="{{ $dok->nama_file }}">
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

    </div>

    <div class="panel mb-3">
        <div class="sec-title"><i class="bi bi-clock-history"></i> Riwayat Verifikasi</div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 show-table">
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

@endsection
