@extends('layouts.app')

@section('title', 'Master Role')

@section('content')

    @section('page-actions')
        <a href="{{ route('master.role.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Role</a>
    @endsection

    @include('partials.page-heading', [
        'icon' => 'bi-person-badge',
        'eyebrow' => 'Master Data',
        'title' => 'Role',
        'description' => 'Kelola role dan hak akses pengguna sistem.',
    ])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Slug</th>
                        <th>Jumlah Hak Akses</th>
                        <th>Jumlah Pengguna</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $role->nama }}</td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>{{ $role->slug === 'super-admin' ? 'Semua' : count($role->hak_akses ?? []) }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>
                                <span class="badge text-bg-{{ $role->is_active ? 'success' : 'secondary' }}">
                                    {{ $role->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('master.role.edit', $role) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if ($role->slug !== 'super-admin')
                                    <form method="POST" action="{{ route('master.role.destroy', $role) }}"
                                        class="d-inline" onsubmit="return confirm('Hapus role ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data role.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

@endsection
