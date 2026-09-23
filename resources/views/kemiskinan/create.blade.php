@extends('layouts.app')

@section('title', 'Pendataan Baru - Kemiskinan')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-house-heart',
        'eyebrow' => 'Kemiskinan Ekstrem',
        'title' => 'Pendataan Baru',
        'description' => 'Lengkapi seluruh bagian formulir, lalu simpan. Data baru otomatis berstatus "Dalam Verifikasi".',
    ])

    @if ($errors->any())
        <div class="alert alert-danger">
            <p class="fw-semibold mb-1">Periksa kembali isian formulir:</p>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('kemiskinan._form')

@endsection
