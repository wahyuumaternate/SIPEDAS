@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-clock-history',
        'eyebrow' => 'Administrasi / Audit Log',
        'title' => 'Detail Aktivitas',
        'description' => $log->deskripsi ?? '',
    ])

    <section class="panel p-3 mb-3">
        <dl class="row mb-0">
            <dt class="col-sm-3">Waktu</dt>
            <dd class="col-sm-9">{{ $log->created_at->format('d F Y H:i:s') }} WIT</dd>

            <dt class="col-sm-3">Pengguna</dt>
            <dd class="col-sm-9">{{ $log->user?->nama ?? 'Sistem' }}</dd>

            <dt class="col-sm-3">Modul</dt>
            <dd class="col-sm-9"><span class="badge text-bg-secondary">{{ $log->modul }}</span></dd>

            <dt class="col-sm-3">Aksi</dt>
            <dd class="col-sm-9 text-capitalize">{{ $log->aksi }}</dd>

            <dt class="col-sm-3">Subjek</dt>
            <dd class="col-sm-9">{{ $log->subjek_tipe ? class_basename($log->subjek_tipe).' #'.$log->subjek_id : '-' }}</dd>

            <dt class="col-sm-3">IP Address</dt>
            <dd class="col-sm-9">{{ $log->ip_address ?? '-' }}</dd>

            <dt class="col-sm-3">User Agent</dt>
            <dd class="col-sm-9 text-break">{{ $log->user_agent ?? '-' }}</dd>
        </dl>
    </section>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <section class="panel">
                <div class="p-3 pb-0"><h2 class="h6 mb-0">Data Sebelum</h2></div>
                <div class="p-3">
                    <pre class="mb-0 small">{{ $log->data_lama ? json_encode($log->data_lama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Tidak ada.' }}</pre>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6">
            <section class="panel">
                <div class="p-3 pb-0"><h2 class="h6 mb-0">Data Sesudah</h2></div>
                <div class="p-3">
                    <pre class="mb-0 small">{{ $log->data_baru ? json_encode($log->data_baru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Tidak ada.' }}</pre>
                </div>
            </section>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('audit-log.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Audit Log
        </a>
    </div>

@endsection
