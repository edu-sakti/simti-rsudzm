<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIMTI RSUDZM') | SIMTI RSUDZM</title>
    <meta name="description" content="SIMTI RSUDZM - Sistem Informasi Manajemen Teknologi Informasi UPTD RSUD dr. Zubir Mahmud Idi.">
    <meta property="og:title" content="@yield('title', 'SIMTI RSUDZM') | SIMTI RSUDZM">
    <meta property="og:description" content="Sistem Informasi Manajemen Teknologi Informasi UPTD RSUD dr. Zubir Mahmud Idi.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('simti.png') }}">
    <meta property="og:image:alt" content="Logo SIMTI RSUDZM">
    <meta name="twitter:card" content="summary_large_image">
    @stack('meta')

    <link rel="shortcut icon" href="{{ asset('adminkit/img/icons/favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('adminkit/img/icons/favicon.ico') }}" type="image/x-icon">
    <link href="{{ asset('adminkit/css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    @stack('styles')
</head>
<body>
<div class="wrapper">
    @include('layouts.partials.sidebar')

    <div class="main">
        @include('layouts.partials.navbar')

        <main class="content">
            <div class="container-fluid p-0">
                @yield('content')
            </div>
        </main>

        @include('layouts.partials.footer')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('adminkit/js/app.js') }}"></script>
<script>
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-toggle="password"]');
        if (!btn) return;
        event.preventDefault();

        const selector = btn.getAttribute('data-target');
        const input = selector
            ? document.querySelector(selector)
            : btn.closest('.input-group')?.querySelector('input');

        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        btn.innerHTML = `<i data-feather="${isPassword ? 'eye' : 'eye-off'}"></i>`;

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

@stack('scripts')
</body>
</html>
