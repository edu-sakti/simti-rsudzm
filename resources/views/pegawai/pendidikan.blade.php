@extends('layouts.app')

@section('title', 'Pendidikan Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    @include('pegawai._tabs', ['activeTab' => 'pendidikan'])

    <div class="card">
        <div class="card-body text-muted">Belum ada data pendidikan.</div>
    </div>
</div>
@endsection

