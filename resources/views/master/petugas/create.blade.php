@extends('layouts.app')

@section('title', 'Tambah Petugas')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-person-plus',
        'eyebrow' => 'Master Data / Petugas',
        'title' => 'Tambah Petugas',
        'description' => 'Buat akun petugas baru beserta role dan wilayah kewenangannya.',
    ])

    <section class="panel">
        <form method="POST" action="{{ route('master.petugas.store') }}" class="p-3">
            @csrf

            @include('master.petugas._form')

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.petugas.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </section>

@endsection
