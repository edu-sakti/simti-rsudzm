@extends('layouts.guest')

@section('title', 'Selamat Datang')

@push('meta')
<meta name="description" content="SIMTI RSUDZM - Sistem Informasi Manajemen Teknologi Informasi UPTD RSUD dr. Zubir Mahmud Idi.">
<meta property="og:title" content="SIMTI RSUDZM - Portal SIMTI">
<meta property="og:description" content="Sistem Informasi Manajemen Teknologi Informasi UPTD RSUD dr. Zubir Mahmud Idi.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:image" content="{{ asset('simti.png') }}">
<meta property="og:image:alt" content="Logo SIMTI RSUDZM">
<meta name="twitter:card" content="summary_large_image">
@endpush

@push('styles')
<link href="{{ asset('adminkit/css/home.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="text-center hero">
    <img src="{{ asset('adminkit/img/icons/logo-simti.png') }}" alt="Logo RSUDZM" class="hero-logo">

    <h1><span>SIMTI</span> RSUDZM</h1>

    <p>
        <strong>Sistem Informasi Manajemen Teknologi Informasi</strong><br>
        <strong>UPTD RSUD dr. Zubir Mahmud Idi</strong><br>
    </p>

@auth
<a href="{{ url('/dashboard') }}" class="continue-application mt-1">
    <div>
        <div class="pencil"></div>
        <div class="folder">
            <div class="top">
                <svg viewBox="0 0 24 27" aria-hidden="true">
                    <path d="M1,0 L23,0 C23.5522847,0 24,0.44771525 24,1 L24,8.17157288 C24,8.70200585 23.7892863,9.21071368 23.4142136,9.58578644 L20.5857864,12.4142136 C20.2107137,12.7892863 20,13.2979941 20,13.8284271 L20,26 C20,26.5522847 19.5522847,27 19,27 L1,27 C0.44771525,27 0,26.5522847 0,26 L0,1 C0,0.44771525 0.44771525,0 1,0 Z"></path>
                </svg>
            </div>
            <div class="paper"></div>
        </div>
    </div>
    Buka Dashboard
</a>
@else
<a href="{{ url('/auth/login') }}" class="continue-application mt-1">
    <div>
        <div class="pencil"></div>
        <div class="folder">
            <div class="top">
                <svg viewBox="0 0 24 27" aria-hidden="true">
                    <path d="M1,0 L23,0 C23.5522847,0 24,0.44771525 24,1 L24,8.17157288 C24,8.70200585 23.7892863,9.21071368 23.4142136,9.58578644 L20.5857864,12.4142136 C20.2107137,12.7892863 20,13.2979941 20,13.8284271 L20,26 C20,26.5522847 19.5522847,27 19,27 L1,27 C0.44771525,27 0,26.5522847 0,26 L0,1 C0,0.44771525 0.44771525,0 1,0 Z"></path>
                </svg>
            </div>
            <div class="paper"></div>
        </div>
    </div>
    Masuk ke Sistem
</a>
@endauth
</div>
@endsection
