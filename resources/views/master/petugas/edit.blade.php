@extends('layouts.app')

@section('title', 'Ubah Petugas')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-person-gear',
        'eyebrow' => 'Master Data / Petugas',
        'title' => 'Ubah Petugas',
        'description' => 'Perbarui data, role, dan wilayah kewenangan petugas.',
    ])

    <section class="panel">
        <form method="POST" action="{{ route('master.petugas.update', $petugas) }}" class="p-3">
            @csrf
            @method('PUT')

            @include('master.petugas._form')

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.petugas.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </section>

@endsection
