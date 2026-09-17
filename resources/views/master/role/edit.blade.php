@extends('layouts.app')

@section('title', 'Ubah Role')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-person-badge',
        'eyebrow' => 'Master Data / Role',
        'title' => 'Ubah Role',
        'description' => 'Perbarui nama, deskripsi, dan hak akses role.',
    ])

    <section class="panel">
        <form method="POST" action="{{ route('master.role.update', $role) }}" class="p-3">
            @csrf
            @method('PUT')

            @include('master.role._form')

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.role.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </section>

@endsection
