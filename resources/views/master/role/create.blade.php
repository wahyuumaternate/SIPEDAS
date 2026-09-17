@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-person-badge',
        'eyebrow' => 'Master Data / Role',
        'title' => 'Tambah Role',
        'description' => 'Buat role baru beserta hak aksesnya.',
    ])

    <section class="panel">
        <form method="POST" action="{{ route('master.role.store') }}" class="p-3">
            @csrf

            @include('master.role._form')

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.role.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </section>

@endsection
