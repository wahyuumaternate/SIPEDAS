@extends('layouts.app')

@section('title', 'Daftar Data Kemiskinan')

@section('page-actions')
    <a class="btn btn-primary btn-sm" href="{{ route('kemiskinan.create') }}">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Pendataan Baru
    </a>
@endsection

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
@endphp

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-house-heart',
        'eyebrow' => 'Kemiskinan Ekstrem',
        'title' => 'Daftar Data Keluarga',
        'description' => 'Seluruh data pendataan kemiskinan ekstrem yang tersimpan di sistem.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <section class="panel mb-3">
        <form method="GET" action="{{ route('kemiskinan.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small" for="q">Cari</label>
                <input type="text" class="form-control form-control-sm" id="q" name="q"
                    value="{{ $filters['q'] ?? '' }}" placeholder="NIK, No. KK, atau nama">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="kecamatan_id">Kecamatan</label>
                <select class="form-select form-select-sm" id="kecamatan_id" name="kecamatan_id">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" @selected(($filters['kecamatan_id'] ?? null) == $kecamatan->id)>
                            {{ $kecamatan->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="status_data">Status</label>
                <select class="form-select form-select-sm" id="status_data" name="status_data">
                    <option value="">Semua Status</option>
                    @foreach ($statusLabelMap as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status_data'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"
                        aria-hidden="true"></i> Filter</button>
                <a href="{{ route('kemiskinan.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Kepala Keluarga</th>
                        <th scope="col">Wilayah</th>
                        <th scope="col">Jml. Anggota</th>
                        <th scope="col">Petugas</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal Input</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($keluargas as $keluarga)
                        <tr>
                            <td>
                                <p class="fw-semibold mb-0">{{ $keluarga->nama_kepala_keluarga }}</p>
                                <p class="text-muted small mb-0">NIK: {{ $keluarga->nik_kepala_keluarga }}</p>
                            </td>
                            <td>
                                <p class="mb-0">{{ $keluarga->kecamatan?->nama }}</p>
                                <p class="text-muted small mb-0">{{ $keluarga->desaKelurahan?->nama }}</p>
                            </td>
                            <td>{{ $keluarga->anggota_keluarga_count }} orang</td>
                            <td>{{ $keluarga->petugas?->nama }}</td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadgeMap[$keluarga->status_data] ?? 'secondary' }}">
                                    {{ $statusLabelMap[$keluarga->status_data] ?? $keluarga->status_data }}
                                </span>
                            </td>
                            <td>{{ $keluarga->tanggal_input?->format('d M Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-light btn-sm" href="{{ route('kemiskinan.show', $keluarga) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data pendataan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $keluargas->links() }}
        </div>
    </section>

@endsection
