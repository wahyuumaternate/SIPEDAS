@extends('layouts.app')

@section('title', 'Detail Anak - ' . $anak->nama_anak)

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
    @if (session('duplikat_warning'))
        <div class="alert alert-warning">{{ session('duplikat_warning') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="panel h-100">
                <span class="text-muted small">Status Data</span>
                <p class="h5 mb-0">
                    <span class="badge text-bg-{{ $statusBadgeMap[$anak->status_data] ?? 'secondary' }}">
                        {{ $statusLabelMap[$anak->status_data] ?? $anak->status_data }}
                    </span>
                </p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel h-100">
                <span class="text-muted small">Usia Saat Ini</span>
                <p class="h5 mb-0">{{ $anak->usia_terkini_bulan }} bulan</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel h-100">
                <span class="text-muted small">Jumlah Pengukuran</span>
                <p class="h5 mb-0">{{ $anak->pengukurans->count() }}x</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel h-100">
                <span class="text-muted small">Petugas Pendata</span>
                <p class="h6 mb-0">{{ $anak->petugas?->nama }}</p>
                <span class="text-muted small">{{ $anak->tanggal_input?->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>

    @if ($duplikatLain->isNotEmpty())
        <div class="alert alert-warning">
            <strong>Potensi duplikasi:</strong> ditemukan data lain dengan NIK/Nomor KK yang sama:
            <ul class="mb-0">
                @foreach ($duplikatLain as $d)
                    <li><a href="{{ route('stunting.show', $d) }}">{{ $d->kode_pendataan }} - {{ $d->nama_anak }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        @if (in_array($anak->status_data, ['draft', 'perlu_perbaikan'], true))
            <form method="POST" action="{{ route('stunting.kirim', $anak) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Kirim untuk Verifikasi</button>
            </form>
        @endif

        @foreach ($transisiVerifikasi[$anak->status_data] ?? [] as $status => $label)
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalVerifikasi-{{ $status }}">{{ $label }}</button>
        @endforeach

        <form method="POST" action="{{ route('stunting.destroy', $anak) }}"
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
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-ortu" type="button">Orang Tua</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-hamil" type="button">Riwayat Kehamilan</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-lahir" type="button">Riwayat Kelahiran</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-ukur" type="button">Pengukuran ({{ $anak->pengukurans->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-sehat" type="button">Kesehatan</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-asi" type="button">ASI &amp; MPASI</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-sanitasi" type="button">Sanitasi</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-dokumen" type="button">Dokumentasi ({{ $anak->dokumen->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d-riwayat" type="button">Riwayat Verifikasi</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active panel" id="d-identitas">
            <dl class="row mb-0">
                <dt class="col-sm-3">NIK Anak</dt><dd class="col-sm-9">{{ $anak->nik_anak }}</dd>
                <dt class="col-sm-3">Nomor KK</dt><dd class="col-sm-9">{{ $anak->nomor_kk }}</dd>
                <dt class="col-sm-3">Jenis Kelamin</dt><dd class="col-sm-9">{{ ucfirst($anak->jenis_kelamin) }}</dd>
                <dt class="col-sm-3">Tempat, Tanggal Lahir</dt><dd class="col-sm-9">{{ $anak->tempat_lahir }}, {{ optional($anak->tanggal_lahir)->format('d M Y') }}</dd>
                <dt class="col-sm-3">Nama Ayah / Ibu</dt><dd class="col-sm-9">{{ $anak->nama_ayah }} / {{ $anak->nama_ibu }}</dd>
                <dt class="col-sm-3">Nomor HP Orang Tua</dt><dd class="col-sm-9">{{ $anak->nomor_hp_orang_tua ?: '-' }}</dd>
                <dt class="col-sm-3">Alamat</dt><dd class="col-sm-9">{{ $anak->alamat }}</dd>
                <dt class="col-sm-3">Wilayah</dt><dd class="col-sm-9">{{ $anak->desaKelurahan?->nama }}, {{ $anak->kecamatan?->nama }}</dd>
            </dl>
        </div>

        <div class="tab-pane fade panel" id="d-ortu">
            @php($ortu = $anak->orangTua)
            @if ($ortu)
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="h6">Ayah</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">NIK</dt><dd class="col-sm-7">{{ $ortu->nik_ayah ?: '-' }}</dd>
                            <dt class="col-sm-5">Nama</dt><dd class="col-sm-7">{{ $ortu->nama_ayah_lengkap ?: '-' }}</dd>
                            <dt class="col-sm-5">Pendidikan</dt><dd class="col-sm-7">{{ $ortu->pendidikan_ayah_label ?: '-' }}</dd>
                            <dt class="col-sm-5">Pekerjaan</dt><dd class="col-sm-7">{{ $ortu->pekerjaan_ayah ?: '-' }}</dd>
                            <dt class="col-sm-5">Penghasilan</dt><dd class="col-sm-7">Rp {{ number_format($ortu->penghasilan_ayah ?? 0, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h2 class="h6">Ibu</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">NIK</dt><dd class="col-sm-7">{{ $ortu->nik_ibu ?: '-' }}</dd>
                            <dt class="col-sm-5">Nama</dt><dd class="col-sm-7">{{ $ortu->nama_ibu_lengkap ?: '-' }}</dd>
                            <dt class="col-sm-5">Pendidikan</dt><dd class="col-sm-7">{{ $ortu->pendidikan_ibu_label ?: '-' }}</dd>
                            <dt class="col-sm-5">Pekerjaan</dt><dd class="col-sm-7">{{ $ortu->pekerjaan_ibu ?: '-' }}</dd>
                            <dt class="col-sm-5">Penghasilan</dt><dd class="col-sm-7">Rp {{ number_format($ortu->penghasilan_ibu ?? 0, 0, ',', '.') }}</dd>
                        </dl>
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">Belum ada data orang tua.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-hamil">
            @php($hml = $anak->riwayatKehamilan)
            @if ($hml)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Usia Ibu Saat Hamil</dt><dd class="col-sm-8">{{ $hml->usia_ibu_saat_hamil }} tahun</dd>
                    <dt class="col-sm-4">Kehamilan Ke- / Jumlah Kehamilan</dt><dd class="col-sm-8">{{ $hml->kehamilan_ke }} / {{ $hml->jumlah_kehamilan }}</dd>
                    <dt class="col-sm-4">Jumlah Pemeriksaan</dt><dd class="col-sm-8">{{ $hml->jumlah_pemeriksaan_kehamilan }}x di {{ $hml->tempat_pemeriksaan }}</dd>
                    <dt class="col-sm-4">Kondisi Kehamilan</dt><dd class="col-sm-8">{{ $hml->kondisi_kehamilan }} {{ $hml->risiko_kehamilan ? '(Berisiko)' : '' }}</dd>
                    <dt class="col-sm-4">Konsumsi Tablet Tambah Darah</dt><dd class="col-sm-8">{{ str($hml->konsumsi_tablet_tambah_darah ?? '-')->replace('_', ' ')->title() }}</dd>
                    <dt class="col-sm-4">Status KEK / LILA</dt><dd class="col-sm-8">{{ $hml->status_kek ? 'Ya' : 'Tidak' }} / {{ $hml->lila }} cm</dd>
                    <dt class="col-sm-4">Komplikasi</dt><dd class="col-sm-8">{{ $hml->komplikasi_kehamilan ?: '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data riwayat kehamilan.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-lahir">
            @php($lhr = $anak->riwayatKelahiran)
            @if ($lhr)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tanggal, Tempat Lahir</dt><dd class="col-sm-8">{{ optional($lhr->tanggal_lahir)->format('d M Y') }}, {{ $lhr->tempat_lahir }}</dd>
                    <dt class="col-sm-4">Penolong / Cara Persalinan</dt><dd class="col-sm-8">{{ str($lhr->penolong_persalinan ?? '-')->title() }} / {{ str($lhr->cara_persalinan ?? '-')->title() }}</dd>
                    <dt class="col-sm-4">Berat / Panjang Lahir</dt><dd class="col-sm-8">{{ $lhr->berat_badan_lahir }} kg / {{ $lhr->panjang_badan_lahir }} cm</dd>
                    <dt class="col-sm-4">Kondisi</dt>
                    <dd class="col-sm-8">
                        @if ($lhr->status_prematur) <span class="badge text-bg-warning">Prematur</span> @endif
                        @if ($lhr->status_bblr) <span class="badge text-bg-warning">BBLR</span> @endif
                        @if ($lhr->imd) <span class="badge text-bg-success">IMD</span> @endif
                    </dd>
                    <dt class="col-sm-4">Kondisi Bayi Saat Lahir</dt><dd class="col-sm-8">{{ $lhr->kondisi_bayi_saat_lahir ?: '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data riwayat kelahiran.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-ukur">
            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle mb-0">
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

        <div class="tab-pane fade panel" id="d-sehat">
            @php($sht = $anak->riwayatKesehatan)
            @if ($sht)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Status Imunisasi</dt><dd class="col-sm-8">{{ str($sht->status_imunisasi ?? '-')->replace('_', ' ')->title() }} ({{ $sht->imunisasi_terakhir ?: '-' }})</dd>
                    <dt class="col-sm-4">Riwayat Penyakit</dt><dd class="col-sm-8">{{ $sht->riwayat_penyakit ?: '-' }}</dd>
                    <dt class="col-sm-4">Kondisi</dt>
                    <dd class="col-sm-8">
                        @if ($sht->riwayat_diare) <span class="badge text-bg-warning">Riwayat Diare</span> @endif
                        @if ($sht->riwayat_ispa) <span class="badge text-bg-warning">Riwayat ISPA</span> @endif
                        @if ($sht->penyakit_kronis) <span class="badge text-bg-danger">Penyakit Kronis: {{ $sht->jenis_penyakit_kronis }}</span> @endif
                        @if ($sht->penyakit_bawaan) <span class="badge text-bg-danger">Penyakit Bawaan: {{ $sht->jenis_penyakit_bawaan }}</span> @endif
                        @if ($sht->riwayat_rawat_inap) <span class="badge text-bg-warning">Pernah Rawat Inap</span> @endif
                    </dd>
                    <dt class="col-sm-4">Akses Pelayanan Kesehatan</dt><dd class="col-sm-8">{{ $sht->akses_pelayanan_kesehatan ? 'Ya' : 'Tidak' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data riwayat kesehatan.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-asi">
            @php($asi = $anak->asiMpasi)
            @if ($asi)
                <dl class="row mb-0">
                    <dt class="col-sm-4">IMD / ASI Eksklusif</dt><dd class="col-sm-8">{{ $asi->imd ? 'Ya' : 'Tidak' }} / {{ $asi->asi_eksklusif ? 'Ya' : 'Tidak' }}</dd>
                    <dt class="col-sm-4">Lama Pemberian ASI</dt><dd class="col-sm-8">{{ $asi->lama_pemberian_asi_bulan }} bulan</dd>
                    <dt class="col-sm-4">Kendala Pemberian ASI</dt><dd class="col-sm-8">{{ $asi->kendala_pemberian_asi ?: '-' }}</dd>
                    <dt class="col-sm-4">Usia Mulai MPASI</dt><dd class="col-sm-8">{{ $asi->usia_mulai_mpasi_bulan }} bulan</dd>
                    <dt class="col-sm-4">Frekuensi / Jenis Makanan</dt><dd class="col-sm-8">{{ $asi->frekuensi_makan }} - {{ $asi->jenis_makanan }}</dd>
                    <dt class="col-sm-4">Sumber Protein</dt><dd class="col-sm-8">{{ $asi->sumber_protein ?: '-' }}</dd>
                    <dt class="col-sm-4">Konsumsi Sayur / Buah</dt><dd class="col-sm-8">{{ $asi->konsumsi_sayur ? 'Ya' : 'Tidak' }} / {{ $asi->konsumsi_buah ? 'Ya' : 'Tidak' }}</dd>
                    <dt class="col-sm-4">Keragaman Makanan</dt><dd class="col-sm-8">{{ $asi->keragaman_makanan ?: '-' }}</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data ASI &amp; MPASI.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-sanitasi">
            @php($san = $anak->sanitasi)
            @if ($san)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Sumber Air Minum / Memasak</dt><dd class="col-sm-8">{{ $san->sumber_air_minum_label ?: '-' }} / {{ $san->sumber_air_memasak_label ?: '-' }}</dd>
                    <dt class="col-sm-4">Jamban</dt><dd class="col-sm-8">{{ $san->kepemilikan_jamban ? 'Ada' : 'Tidak Ada' }} ({{ $san->jenis_jamban_label ?: '-' }}) {{ $san->septic_tank ? '+ Septic Tank' : '' }}</dd>
                    <dt class="col-sm-4">Saluran Pembuangan</dt><dd class="col-sm-8">{{ $san->saluran_pembuangan ?: '-' }}</dd>
                    <dt class="col-sm-4">Pengelolaan Sampah</dt><dd class="col-sm-8">{{ $san->pengelolaan_sampah_label ?: '-' }}</dd>
                    <dt class="col-sm-4">Kondisi Rumah</dt><dd class="col-sm-8">{{ str($san->kondisi_rumah ?? '-')->replace('_', ' ')->title() }}</dd>
                    <dt class="col-sm-4">Kepadatan Hunian</dt><dd class="col-sm-8">{{ $san->kepadatan_hunian }} m²/orang</dd>
                </dl>
            @else
                <p class="text-muted mb-0">Belum ada data sanitasi &amp; lingkungan.</p>
            @endif
        </div>

        <div class="tab-pane fade panel" id="d-dokumen">
            <div class="row g-3 mb-3">
                @forelse ($anak->dokumen as $dok)
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center h-100">
                            <img src="{{ Storage::url($dok->path_file) }}" class="img-fluid rounded mb-1"
                                style="max-height:120px;object-fit:cover;" alt="{{ $dok->nama_file }}">
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

        <div class="tab-pane fade panel" id="d-riwayat">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
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
    </div>

@endsection
