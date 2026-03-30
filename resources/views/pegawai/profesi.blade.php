@extends('layouts.app')

@section('title', 'Profesi Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    @include('pegawai._tabs', ['activeTab' => 'profesi'])

    <div class="card">
        <div class="card-body text-muted">Belum ada data profesi.</div>
    </div>
</div>
@endsection

