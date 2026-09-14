<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Terjadi kesalahan pada server Bappelitbangda Kota Ternate">

    <title>500 | Kesalahan Server</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body class="auth-body">

    <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema"
        title="Ganti tema">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>

    <main class="error-page">

        <section class="error-card">

            <a class="auth-brand justify-content-center" href="{{ url('/') }}">
                <span class="brand-icon">
                    <img src="{{ asset('logo_kota.png') }}" alt="" class="img-fluid" width="50">
                </span>

                <span>
                    <strong>SIPEDAS</strong>
                    <small>ERROR</small>
                </span>
            </a>

            <img class="error-illustration" src="{{ asset('assets/images/svg/maintenance.svg') }}"
                alt="Ilustrasi kesalahan server">

            <div class="error-code">
                500
            </div>

            <h1 class="h3 mb-2">
                Terjadi Kesalahan Server
            </h1>

            <p class="text-muted mb-4">
                Maaf, terjadi kesalahan pada sistem.
                Silakan coba kembali beberapa saat lagi.
            </p>

            <div class="d-flex justify-content-center">

                <a class="btn btn-primary" href="{{ Auth::check() ? route('dashboard') : route('login') }}">
                    <i class="{{ Auth::check() ? 'bi bi-speedometer2' : 'bi bi-box-arrow-in-right' }}"
                        aria-hidden="true"></i>

                    {{ Auth::check() ? 'Kembali ke Dashboard' : 'Ke Halaman Login' }}
                </a>

            </div>

        </section>

    </main>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

</body>

</html>
