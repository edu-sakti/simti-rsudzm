@extends('layouts.app')

@section('title', 'Pensiun Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    @include('profile._tabs', ['activeTab' => 'pensiun'])

    <div class="card">
        <div class="card-body text-muted">Belum ada data pensiun.</div>
    </div>
</div>
@endsection
