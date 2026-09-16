@extends('layouts.app')

@section('title', 'Ubah Data Anak - Stunting')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-heart-pulse',
        'eyebrow' => 'Stunting',
        'title' => 'Ubah Data: ' . $anak->nama_anak,
        'description' => 'Kode Pendataan: ' . $anak->kode_pendataan,
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

    @include('stunting._form')

@endsection
