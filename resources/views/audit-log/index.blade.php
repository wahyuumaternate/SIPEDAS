@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-clock-history',
        'eyebrow' => 'Administrasi',
        'title' => 'Audit Log',
        'description' => 'Riwayat aktivitas penting: siapa mengubah apa, kapan, dan dari mana.',
    ])

    <section class="panel mb-3">
        <form method="GET" action="{{ route('audit-log.index') }}" class="row g-2 align-items-end p-3">
            <div class="col-6 col-md-3">
                <label class="form-label small" for="user_id">Pengguna</label>
                <select class="form-select form-select-sm" id="user_id" name="user_id">
                    <option value="">Semua Pengguna</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? null) == $user->id)>{{ $user->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="modul">Modul</label>
                <select class="form-select form-select-sm" id="modul" name="modul">
                    <option value="">Semua Modul</option>
                    @foreach ($modulOptions as $opsi)
                        <option value="{{ $opsi }}" @selected(($filters['modul'] ?? null) === $opsi)>{{ $opsi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="aksi">Aksi</label>
                <select class="form-select form-select-sm" id="aksi" name="aksi">
                    <option value="">Semua Aksi</option>
                    @foreach ($aksiOptions as $opsi)
                        <option value="{{ $opsi }}" @selected(($filters['aksi'] ?? null) === $opsi)>{{ $opsi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="dari">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="sampai">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}">
            </div>
            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i></button>
                <a href="{{ route('audit-log.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Modul</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>IP Address</th>
                        <th class="text-end">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $log->user?->nama ?? 'Sistem' }}</td>
                            <td><span class="badge text-bg-secondary">{{ $log->modul }}</span></td>
                            <td class="text-capitalize">{{ $log->aksi }}</td>
                            <td>{{ $log->deskripsi }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td class="text-end">
                                <a href="{{ route('audit-log.show', $log) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $logs->links() }}</div>
    </section>

@endsection
