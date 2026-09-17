@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')

    @include('partials.page-heading', [
        'icon' => 'bi-person-circle',
        'eyebrow' => 'Akun',
        'title' => 'Profil Saya',
        'description' => 'Kelola informasi akun dan kata sandi Anda.',
    ])

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <section class="panel p-3">
                @include('profile.partials.update-profile-information-form')
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section class="panel p-3">
                @include('profile.partials.update-password-form')
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section class="panel p-3">
                @include('profile.partials.delete-user-form')
            </section>
        </div>
    </div>

@endsection
