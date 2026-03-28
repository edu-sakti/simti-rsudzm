<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Nama</small>
                <h4 class="h4 mb-0">{{ $profile->nama ?? '-' }}</h4>
            </div>
            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Jenis Kelamin</small>
                <h4 class="h4 mb-0">{{ ucfirst($profile->jenis_kelamin ?? '-') }}</h4>
            </div>

            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Tempat Lahir</small>
                <h4 class="h4 mb-0">{{ $profile->tempat_lahir ?? '-' }}</h4>
            </div>
            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Tanggal Lahir</small>
                <h4 class="h4 mb-0">{{ $profile->tanggal_lahir ?? '-' }}</h4>
            </div>

            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Agama</small>
                <h4 class="h4 mb-0">{{ $profile->agama ?? '-' }}</h4>
            </div>
            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Status Perkawinan</small>
                <h4 class="h4 mb-0">{{ $profile->status_perkawinan ?? '-' }}</h4>
            </div>

            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Provinsi</small>
                <h4 class="h4 mb-0" id="provinsiLabel" data-code="{{ $profile->provinsi ?? '' }}">
                    {{ $profile->provinsi ?? '-' }}
                </h4>
            </div>
            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Kabupaten/Kota</small>
                <h4 class="h4 mb-0" id="kabupatenLabel" data-code="{{ $profile->kabupaten ?? '' }}">
                    {{ $profile->kabupaten ?? '-' }}
                </h4>
            </div>

            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Kecamatan</small>
                <h4 class="h4 mb-0" id="kecamatanLabel" data-code="{{ $profile->kecamatan ?? '' }}">
                    {{ $profile->kecamatan ?? '-' }}
                </h4>
            </div>
            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Desa/Kelurahan</small>
                <h4 class="h4 mb-0" id="desaLabel" data-code="{{ $profile->desa ?? '' }}">
                    {{ $profile->desa ?? '-' }}
                </h4>
            </div>

            <div class="col-12 mb-3">
                <small class="text-muted d-block">Detail Alamat</small>
                <h4 class="h4 mb-0">{{ $profile->alamat ?? '-' }}</h4>
            </div>

            <div class="col-12 mt-2 d-flex align-items-center gap-2">
                <a href="{{ route('profile.edit', $token) }}" class="btn btn-primary">
                    <i data-feather="edit-2"></i> Edit Profil
                </a>
                @if(!(auth()->user()->is_admin ?? false))
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                        <i data-feather="arrow-left"></i> Kembali
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
