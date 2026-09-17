{{--
    Partial header halaman, dipakai ulang di semua halaman admin.
    Contoh pemakaian dari view lain:

    @include('partials.page-heading', [
        'icon' => 'bi-speedometer2',
        'eyebrow' => 'Overview',
        'title' => 'Dashboard',
        'description' => 'Monitor performance, sales, users, and support from one clean workspace.',
    ])

    Untuk tombol aksi kustom, gunakan @section('page-actions') ... @endsection
    di view anak sebelum @include ini, lalu render dengan @yield('page-actions').
--}}
<div class="page-heading">
  <div class="page-heading-copy">
    <span class="page-icon"><i class="bi {{ $icon ?? 'bi-speedometer2' }}" aria-hidden="true"></i></span>
    <div>
      <p class="eyebrow mb-1">{{ $eyebrow ?? 'Overview' }}</p>
      <h1 class="h3 mb-1">{{ $title ?? 'Dashboard' }}</h1>
      <p class="text-muted mb-0">{{ $description ?? '' }}</p>
    </div>
  </div>
  @hasSection('page-actions')
    <div class="heading-actions">
      @yield('page-actions')
    </div>
  @endif
</div>
