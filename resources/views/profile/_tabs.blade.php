@php
    $activeTab = $activeTab ?? 'data-utama';
@endphp

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'data-utama' ? 'active' : '' }}" href="{{ route('profile.home') }}">
            Data Utama
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'golongan' ? 'active' : '' }}" href="{{ route('profile.golongan') }}">
            Golongan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'jabatan' ? 'active' : '' }}" href="{{ route('profile.jabatan') }}">
            Jabatan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'posisi' ? 'active' : '' }}" href="{{ route('profile.posisi') }}">
            Posisi
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'pendidikan' ? 'active' : '' }}" href="{{ route('profile.pendidikan') }}">
            Pendidikan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'profesi' ? 'active' : '' }}" href="{{ route('profile.profesi') }}">
            Profesi
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'keluarga' ? 'active' : '' }}" href="{{ route('profile.keluarga') }}">
            Keluarga
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'pensiun' ? 'active' : '' }}" href="{{ route('profile.pensiun') }}">
            Pensiun
        </a>
    </li>
</ul>
