@extends('layouts.app')

@section('title', 'Master Petugas')

@section('content')

    @section('page-actions')
        <a href="{{ route('master.petugas.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Tambah Petugas</a>
    @endsection

    @include('partials.page-heading', [
        'icon' => 'bi-people',
        'eyebrow' => 'Master Data',
        'title' => 'Petugas',
        'description' => 'Kelola akun petugas: buat, ubah, aktifkan/nonaktifkan, dan reset kata sandi.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel mb-3">
        <form method="GET" action="{{ route('master.petugas.index') }}" class="row g-2 align-items-end p-3">
            <div class="col-12 col-md-4">
                <label class="form-label small" for="q">Cari</label>
                <input type="text" class="form-control form-control-sm" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Nama, username, atau email">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="role_id">Role</label>
                <select class="form-select form-select-sm" id="role_id" name="role_id">
                    <option value="">Semua Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(($filters['role_id'] ?? null) == $role->id)>{{ $role->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="status">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(($filters['status'] ?? null) === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? null) === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('master.petugas.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username / Email</th>
                        <th>Role</th>
                        <th>Wilayah</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($petugas as $item)
                        <tr>
                            <td>{{ $item->nama }}</td>
                            <td>
                                <p class="mb-0">{{ $item->username }}</p>
                                <p class="text-muted small mb-0">{{ $item->email }}</p>
                            </td>
                            <td>{{ $item->role?->nama }}</td>
                            <td>{{ $item->kecamatan?->nama }}{{ $item->desaKelurahan ? ', '.$item->desaKelurahan->nama : '' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $item->status === 'aktif' ? 'success' : 'secondary' }}">
                                    {{ $item->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master.petugas.edit', $item) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalResetPassword-{{ $item->id }}">
                                    <i class="bi bi-key"></i>
                                </button>
                                <form method="POST" action="{{ route('master.petugas.toggle-status', $item) }}" class="d-inline"
                                    onsubmit="return confirm('{{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} petugas ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $item->status === 'aktif' ? 'danger' : 'success' }} btn-sm">
                                        <i class="bi bi-{{ $item->status === 'aktif' ? 'slash-circle' : 'check-circle' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data petugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $petugas->links() }}</div>
    </section>

    @foreach ($petugas as $item)
        <div class="modal fade" id="modalResetPassword-{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('master.petugas.reset-password', $item) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Kata Sandi — {{ $item->nama }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Kata Sandi</label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection
