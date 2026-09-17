<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SIPENTAS - Sistem Informasi Pendataan Kemiskinan Ekstrem dan Stunting Bappelitbangda">
    <title>Login | SIPENTAS</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo_kota.png') }}">

    <style>
        /* Honeypot: sembunyikan dari manusia (bukan display:none agar tetap "terisi" oleh bot form-filler,
           tapi tetap tidak terlihat & tidak terjangkau oleh pengguna asli / screen reader). */
        .hp-field {
            position: absolute;
            left: -9999px;
            top: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
    </style>
</head>

<body class="auth-body">
    <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle
        aria-label="Switch color theme" title="Switch color theme">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>
    <main class="auth-page">
        <section class="auth-card">
            <a class="auth-brand" href="{{ url('/') }}"><img src="{{ asset('logo_kota.png') }}" alt=""
                    class="img-fluid" width="50"><span><strong>SIPENTAS</strong><small>Sistem Informasi Pendataan
                        Kemiskinan
                        Ekstrem dan Stunting Bappelitbangda</small></span></a>
            {{-- <div class="auth-visual"><img src="{{ asset('LogoKotaRempah.png') }}" alt="SIPENTAS dashboard interface">
            </div> --}}

            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                @csrf

                <div class="mb-4">
                    <p class="eyebrow mb-1">Bappelitbangda</p>
                    <h1 class="h3 mb-1">Masuk ke SIPENTAS</h1>
                    <p class="text-muted mb-0">Sistem Informasi Pendataan Kemiskinan Ekstrem dan Stunting.</p>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="loginEmail">Alamat Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="loginEmail" name="email"
                        type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @else
                        <div class="invalid-feedback">Masukkan alamat email yang valid.</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="loginPassword">Kata Sandi</label>
                    </div>
                    <div class="input-group has-validation">
                        <input class="form-control @error('password') is-invalid @enderror" id="loginPassword"
                            name="password" type="password" minlength="6" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                            aria-label="Tampilkan kata sandi" tabindex="-1">
                            <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Kata sandi minimal 6 karakter.</div>
                        @enderror
                    </div>
                </div>

                {{-- Honeypot: harus selalu kosong. Bot biasanya otomatis mengisi semua field. --}}
                <div class="hp-field" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                {{-- Timestamp render form, dipakai untuk deteksi submit terlalu cepat (bot). --}}
                <input type="hidden" name="form_rendered_at" value="{{ time() }}">

                {{-- hCaptcha (buzz/laravel-h-captcha) — dilewati di environment local --}}
                @unless (app()->environment('local'))
                    <div class="mb-3">
                        <div class="d-flex justify-content-center">
                            {!! app('captcha')->display([], ['data-theme' => 'dark']) !!}
                        </div>

                        @error('h-captcha-response')
                            <div class="text-danger small mt-1 text-center">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                @endunless
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"
                        aria-hidden="true"></i> Masuk</button>
            </form>

            <div class="auth-footer">
                <small class="text-muted">Akun hanya dapat dibuat oleh Administrator sistem.</small>
            </div>
        </section>
    </main>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    {{-- {!! app('captcha')->renderJs() !!} --}}
    <script>
        (function() {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('loginPassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (toggleBtn && passwordInput && toggleIcon) {
                toggleBtn.addEventListener('click', function() {
                    const isHidden = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                    toggleIcon.classList.toggle('bi-eye', !isHidden);
                    toggleIcon.classList.toggle('bi-eye-slash', isHidden);
                    toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' :
                        'Tampilkan kata sandi');
                });
            }
        })();
    </script>
</body>

</html>
