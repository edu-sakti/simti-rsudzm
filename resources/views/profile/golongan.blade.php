@extends('layouts.app')

@section('title', 'Golongan Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    @include('profile._tabs', ['activeTab' => 'golongan'])

    <div class="card">
        <div class="card-body text-muted">Belum ada data golongan.</div>
    </div>
</div>
@endsection
