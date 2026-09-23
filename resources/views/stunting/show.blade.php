@extends('layouts.app')

@section('title', 'Detail Anak - ' . $anak->nama_anak)

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
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('stunting.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
    <a class="btn btn-primary btn-sm" href="{{ route('stunting.edit', $anak) }}"><i class="bi bi-pencil"></i> Ubah</a>
@endsection

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-heart-pulse',
        'eyebrow' => 'Stunting · ' . $anak->kode_pendataan,
        'title' => $anak->nama_anak,
        'description' => $anak->kecamatan?->nama . ', ' . $anak->desaKelurahan?->nama,
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


    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="panel h-100 stat-tile"><span class="ico"><i class="bi bi-flag"></i></span><div><small>Status Data</small>
            <span class="badge text-bg-{{ $statusBadgeMap[$anak->status_data] ?? 'secondary' }} fs-6">{{ $statusLabelMap[$anak->status_data] ?? $anak->status_data }}</span></div></div></div>
        <div class="col-md-3"><div class="panel h-100 stat-tile"><span class="ico"><i class="bi bi-calendar-heart"></i></span><div><small>Usia Saat Ini</small><strong class="fs-5">{{ $anak->usia_terkini_bulan }} bulan</strong></div></div></div>
        <div class="col-md-3"><div class="panel h-100 stat-tile"><span class="ico"><i class="bi bi-rulers"></i></span><div><small>Jumlah Pengukuran</small><strong class="fs-5">{{ $anak->pengukurans->count() }}x</strong></div></div></div>
        <div class="col-md-3"><div class="panel h-100 stat-tile"><span class="ico"><i class="bi bi-person-badge"></i></span><div><small>Petugas Pendata</small><strong>{{ $anak->petugas?->nama ?? '-' }}</strong><small>{{ $anak->tanggal_input?->format('d M Y H:i') }}</small></div></div></div>
    </div>

    <div class="panel mb-3 d-flex flex-wrap align-items-center gap-2 py-3">
        <span class="text-muted small me-2">Aksi:</span>
        @foreach ($transisiVerifikasi[$anak->status_data] ?? [] as $status => $label)
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalVerifikasi-{{ $status }}">{{ $label }}</button>
        @endforeach

        <form method="POST" action="{{ route('stunting.destroy', $anak) }}" class="ms-auto"
            onsubmit="return confirm('Hapus data anak ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
        </form>
    </div>

    @foreach ($transisiVerifikasi[$anak->status_data] ?? [] as $status => $label)
        <div class="modal fade" id="modalVerifikasi-{{ $status }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('stunting.verifikasi.store', $anak) }}" class="modal-content">
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

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-card-text"></i> Identitas</div>
            <dl class="kv">
                                <dt>NIK Anak</dt><dd>{{ $anak->nik_anak }}</dd>
                                <dt>Nomor KK</dt><dd>{{ $anak->nomor_kk }}</dd>
                                <dt>Jenis Kelamin</dt><dd>{{ ucfirst($anak->jenis_kelamin) }}</dd>
                                <dt>Tempat, Tanggal Lahir</dt><dd>{{ $anak->tempat_lahir }}, {{ optional($anak->tanggal_lahir)->format('d M Y') }}</dd>
                                <dt>Nama Ayah / Ibu</dt><dd>{{ $anak->nama_ayah }} / {{ $anak->nama_ibu }}</dd>
                                <dt>Nomor HP Orang Tua</dt><dd>{{ $anak->nomor_hp_orang_tua ?: '-' }}</dd>
                                <dt>Alamat</dt><dd>{{ $anak->alamat }}</dd>
                                <dt>Wilayah</dt><dd>{{ $anak->desaKelurahan?->nama }}, {{ $anak->kecamatan?->nama }}</dd>
                            </dl>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-people"></i> Orang Tua</div>
            @php($ortu = $anak->orangTua)
            @if ($ortu)
                <div class="row g-3">
                    <div class="col-md-6">
                        <h2 class="h6">Ayah</h2>
                        <dl class="kv">
                            <dt>NIK</dt><dd>{{ $ortu->nik_ayah ?: '-' }}</dd>
                            <dt>Nama</dt><dd>{{ $ortu->nama_ayah_lengkap ?: '-' }}</dd>
                            <dt>Pendidikan</dt><dd>{{ $ortu->pendidikan_ayah_label ?: '-' }}</dd>
                            <dt>Pekerjaan</dt><dd>{{ $ortu->pekerjaan_ayah ?: '-' }}</dd>
                            <dt>Penghasilan</dt><dd>Rp {{ number_format($ortu->penghasilan_ayah ?? 0, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h2 class="h6">Ibu</h2>
                        <dl class="kv">
                            <dt>NIK</dt><dd>{{ $ortu->nik_ibu ?: '-' }}</dd>
                            <dt>Nama</dt><dd>{{ $ortu->nama_ibu_lengkap ?: '-' }}</dd>
                            <dt>Pendidikan</dt><dd>{{ $ortu->pendidikan_ibu_label ?: '-' }}</dd>
                            <dt>Pekerjaan</dt><dd>{{ $ortu->pekerjaan_ibu ?: '-' }}</dd>
                            <dt>Penghasilan</dt><dd>Rp {{ number_format($ortu->penghasilan_ibu ?? 0, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">Belum ada data orang tua.</p>
            @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-clipboard2-pulse"></i> Riwayat Kehamilan</div>
            @php($hml = $anak->riwayatKehamilan)
            @if ($hml)
                <dl class="kv">
                    <dt>Usia Ibu Saat Hamil</dt><dd>{{ $hml->usia_ibu_saat_hamil }} tahun</dd>
                    <dt>Kehamilan Ke- / Jumlah Kehamilan</dt><dd>{{ $hml->kehamilan_ke }} / {{ $hml->jumlah_kehamilan }}</dd>
                    <dt>Jumlah Pemeriksaan</dt><dd>{{ $hml->jumlah_pemeriksaan_kehamilan }}x di {{ $hml->tempat_pemeriksaan }}</dd>
                    <dt>Kondisi Kehamilan</dt><dd>{{ $hml->kondisi_kehamilan }} {{ $hml->risiko_kehamilan ? '(Berisiko)' : '' }}</dd>
                    <dt>Konsumsi Tablet Tambah Darah</dt><dd>{{ str($hml->konsumsi_tablet_tambah_darah ?? '-')->replace('_', ' ')->title() }}</dd>
                    <dt>Status KEK / LILA</dt><dd>{{ $hml->status_kek ? 'Ya' : 'Tidak' }} / {{ $hml->lila }} cm</dd>
                    <dt>Komplikasi</dt><dd>{{ $hml->komplikasi_kehamilan ?: '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data riwayat kehamilan.</p>
            @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-balloon-heart"></i> Riwayat Kelahiran</div>
            @php($lhr = $anak->riwayatKelahiran)
            @if ($lhr)
                <dl class="kv">
                    <dt>Tanggal, Tempat Lahir</dt><dd>{{ optional($lhr->tanggal_lahir)->format('d M Y') }}, {{ $lhr->tempat_lahir }}</dd>
                    <dt>Penolong / Cara Persalinan</dt><dd>{{ str($lhr->penolong_persalinan ?? '-')->title() }} / {{ str($lhr->cara_persalinan ?? '-')->title() }}</dd>
                    <dt>Berat / Panjang Lahir</dt><dd>{{ $lhr->berat_badan_lahir }} kg / {{ $lhr->panjang_badan_lahir }} cm</dd>
                    <dt>Kondisi</dt>
                    <dd>
                        @if ($lhr->status_prematur) <span class="badge text-bg-warning">Prematur</span> @endif
                        @if ($lhr->status_bblr) <span class="badge text-bg-warning">BBLR</span> @endif
                        @if ($lhr->imd) <span class="badge text-bg-success">IMD</span> @endif
                    </dd>
                    <dt>Kondisi Bayi Saat Lahir</dt><dd>{{ $lhr->kondisi_bayi_saat_lahir ?: '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data riwayat kelahiran.</p>
            @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-heart-pulse"></i> Kesehatan</div>
            @php($sht = $anak->riwayatKesehatan)
            @if ($sht)
                <dl class="kv">
                    <dt>Status Imunisasi</dt><dd>{{ str($sht->status_imunisasi ?? '-')->replace('_', ' ')->title() }} ({{ $sht->imunisasi_terakhir ?: '-' }})</dd>
                    <dt>Riwayat Penyakit</dt><dd>{{ $sht->riwayat_penyakit ?: '-' }}</dd>
                    <dt>Kondisi</dt>
                    <dd>
                        @if ($sht->riwayat_diare) <span class="badge text-bg-warning">Riwayat Diare</span> @endif
                        @if ($sht->riwayat_ispa) <span class="badge text-bg-warning">Riwayat ISPA</span> @endif
                        @if ($sht->penyakit_kronis) <span class="badge text-bg-danger">Penyakit Kronis: {{ $sht->jenis_penyakit_kronis }}</span> @endif
                        @if ($sht->penyakit_bawaan) <span class="badge text-bg-danger">Penyakit Bawaan: {{ $sht->jenis_penyakit_bawaan }}</span> @endif
                        @if ($sht->riwayat_rawat_inap) <span class="badge text-bg-warning">Pernah Rawat Inap</span> @endif
                    </dd>
                    <dt>Akses Pelayanan Kesehatan</dt><dd>{{ $sht->akses_pelayanan_kesehatan ? 'Ya' : 'Tidak' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data riwayat kesehatan.</p>
            @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-cup-hot"></i> ASI &amp; MPASI</div>
            @php($asi = $anak->asiMpasi)
            @if ($asi)
                <dl class="kv">
                    <dt>IMD / ASI Eksklusif</dt><dd>{{ $asi->imd ? 'Ya' : 'Tidak' }} / {{ $asi->asi_eksklusif ? 'Ya' : 'Tidak' }}</dd>
                    <dt>Lama Pemberian ASI</dt><dd>{{ $asi->lama_pemberian_asi_bulan }} bulan</dd>
                    <dt>Kendala Pemberian ASI</dt><dd>{{ $asi->kendala_pemberian_asi ?: '-' }}</dd>
                    <dt>Usia Mulai MPASI</dt><dd>{{ $asi->usia_mulai_mpasi_bulan }} bulan</dd>
                    <dt>Frekuensi / Jenis Makanan</dt><dd>{{ $asi->frekuensi_makan }} - {{ $asi->jenis_makanan }}</dd>
                    <dt>Sumber Protein</dt><dd>{{ $asi->sumber_protein ?: '-' }}</dd>
                    <dt>Konsumsi Sayur / Buah</dt><dd>{{ $asi->konsumsi_sayur ? 'Ya' : 'Tidak' }} / {{ $asi->konsumsi_buah ? 'Ya' : 'Tidak' }}</dd>
                    <dt>Keragaman Makanan</dt><dd>{{ $asi->keragaman_makanan ?: '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data ASI &amp; MPASI.</p>
            @endif
            </div>
        </div>
        <div class="col-lg-12">
            <div class="panel h-100">
                <div class="sec-title"><i class="bi bi-droplet"></i> Sanitasi &amp; Lingkungan</div>
            @php($san = $anak->sanitasi)
            @if ($san)
                <dl class="kv">
                    <dt>Sumber Air Minum / Memasak</dt><dd>{{ $san->sumber_air_minum_label ?: '-' }} / {{ $san->sumber_air_memasak_label ?: '-' }}</dd>
                    <dt>Jamban</dt><dd>{{ $san->kepemilikan_jamban ? 'Ada' : 'Tidak Ada' }} ({{ $san->jenis_jamban_label ?: '-' }}) {{ $san->septic_tank ? '+ Septic Tank' : '' }}</dd>
                    <dt>Saluran Pembuangan</dt><dd>{{ $san->saluran_pembuangan ?: '-' }}</dd>
                    <dt>Pengelolaan Sampah</dt><dd>{{ $san->pengelolaan_sampah_label ?: '-' }}</dd>
                    <dt>Kondisi Rumah</dt><dd>{{ str($san->kondisi_rumah ?? '-')->replace('_', ' ')->title() }}</dd>
                    <dt>Kepadatan Hunian</dt><dd>{{ $san->kepadatan_hunian }} m²/orang</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data sanitasi &amp; lingkungan.</p>
            @endif
            </div>
        </div>
    </div>

    <div class="panel mb-3">
        <div class="sec-title"><i class="bi bi-rulers"></i> Pengukuran <span class="badge text-bg-secondary">{{ $anak->pengukurans->count() }}</span></div>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-hover align-middle mb-0 show-table">
                    <thead><tr><th>Tanggal</th><th>Usia</th><th>BB (kg)</th><th>TB/PB (cm)</th><th>LK (cm)</th><th>LILA (cm)</th><th>Kategori</th><th>Petugas</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($anak->pengukurans as $p)
                            <tr>
                                <td>{{ optional($p->tanggal_pengukuran)->format('d M Y') }}</td>
                                <td>{{ $p->usia_saat_pengukuran_bulan }} bln</td>
                                <td>{{ $p->berat_badan }}</td>
                                <td>{{ $p->panjang_tinggi_badan }}</td>
                                <td>{{ $p->lingkar_kepala }}</td>
                                <td>{{ $p->lingkar_lengan_atas }}</td>
                                <td>{{ $p->hasil_kategori ? str($p->hasil_kategori)->replace('_', ' ')->title() : '-' }}</td>
                                <td>{{ $p->petugasPengukur?->nama }}</td>
                                <td>
                                    <form method="POST" action="{{ route('stunting.pengukuran.destroy', [$anak, $p]) }}"
                                        onsubmit="return confirm('Hapus data pengukuran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-3">Belum ada data pengukuran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('stunting.pengukuran.store', $anak) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label class="form-label small">Tanggal</label>
                    <input type="date" name="tanggal_pengukuran" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Berat Badan (kg)</label>
                    <input type="number" min="0" step="0.01" name="berat_badan" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Panjang/Tinggi (cm)</label>
                    <input type="number" min="0" step="0.01" name="panjang_tinggi_badan" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Lingkar Kepala (cm)</label>
                    <input type="number" min="0" step="0.01" name="lingkar_kepala" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Kategori</label>
                    <select name="hasil_kategori" class="form-select form-select-sm">
                        <option value="">Pilih</option>
                        <option value="normal">Normal</option>
                        <option value="stunting_ringan">Stunting Ringan</option>
                        <option value="stunting_sedang">Stunting Sedang</option>
                        <option value="stunting_berat">Stunting Berat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Tambah</button>
                </div>
            </form>
    </div>

    <div class="panel mb-3">
        <div class="sec-title"><i class="bi bi-images"></i> Dokumentasi <span class="badge text-bg-secondary">{{ $anak->dokumen->count() }}</span></div>
            <div class="row g-3 mb-3">
                @forelse ($anak->dokumen as $dok)
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center h-100 doc-thumb">
                            <img src="{{ Storage::url($dok->path_file) }}" class="img-fluid rounded mb-1" alt="{{ $dok->nama_file }}">
                            <p class="small mb-0 text-truncate">{{ str($dok->jenis_dokumentasi)->replace('_', ' ')->title() }}</p>
                            <p class="text-muted small mb-1">{{ $dok->pengunggah?->nama }} · {{ $dok->tanggal_upload?->format('d M Y') }}</p>
                            <form method="POST" action="{{ route('stunting.dokumen.destroy', [$anak, $dok]) }}"
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

            <form method="POST" action="{{ route('stunting.dokumen.store', $anak) }}" enctype="multipart/form-data"
                class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">Unggah Foto</label>
                    <input type="file" name="file" accept="image/*" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Jenis Dokumentasi</label>
                    <select name="jenis_dokumentasi" class="form-select form-select-sm" required>
                        <option value="foto_anak">Foto Anak</option>
                        <option value="foto_pengukuran">Foto Pengukuran</option>
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

    <div class="panel mb-3">
        <div class="sec-title"><i class="bi bi-clock-history"></i> Riwayat Verifikasi</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 show-table">
                    <thead><tr><th>Tanggal</th><th>Status</th><th>Verifikator</th><th>Catatan</th></tr></thead>
                    <tbody>
                        @forelse ($anak->riwayatVerifikasi->sortByDesc('tanggal_verifikasi') as $v)
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
