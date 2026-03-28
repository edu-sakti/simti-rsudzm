@extends('layouts.app')

@section('title', 'Posisi Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    @include('profile._tabs', ['activeTab' => 'posisi'])

    <div class="card">
        <div class="card-body text-muted">Belum ada data posisi.</div>
    </div>
</div>
@endsection
