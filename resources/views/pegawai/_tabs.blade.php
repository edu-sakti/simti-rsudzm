@php
    $activeTab = $activeTab ?? 'data-utama';
@endphp

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'data-utama' ? 'active' : '' }}" href="{{ route('pegawai.home') }}">
            Data Utama
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'golongan' ? 'active' : '' }}" href="{{ route('pegawai.golongan') }}">
            Golongan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'jabatan' ? 'active' : '' }}" href="{{ route('pegawai.jabatan') }}">
            Jabatan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'posisi' ? 'active' : '' }}" href="{{ route('pegawai.posisi') }}">
            Posisi
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'pendidikan' ? 'active' : '' }}" href="{{ route('pegawai.pendidikan') }}">
            Pendidikan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'profesi' ? 'active' : '' }}" href="{{ route('pegawai.profesi') }}">
            Profesi
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'keluarga' ? 'active' : '' }}" href="{{ route('pegawai.keluarga') }}">
            Keluarga
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'pensiun' ? 'active' : '' }}" href="{{ route('pegawai.pensiun') }}">
            Pensiun
        </a>
    </li>
</ul>

