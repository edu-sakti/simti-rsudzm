@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Nama</div>
                    <div class="fw-semibold">{{ $profile->nama ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Jenis Kelamin</div>
                    <div class="fw-semibold">{{ $profile->jenis_kelamin ?? '-' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Tempat Lahir</div>
                    <div class="fw-semibold">{{ $profile->tempat_lahir ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Tanggal Lahir</div>
                    <div class="fw-semibold">{{ $profile->tanggal_lahir ?? '-' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Agama</div>
                    <div class="fw-semibold">{{ $profile->agama ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Status Perkawinan</div>
                    <div class="fw-semibold">{{ $profile->status_perkawinan ?? '-' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Provinsi</div>
                    <div class="fw-semibold" id="provinsiLabel" data-code="{{ $profile->provinsi ?? '' }}">
                        {{ $profile->provinsi ?? '-' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Kabupaten/Kota</div>
                    <div class="fw-semibold" id="kabupatenLabel" data-code="{{ $profile->kabupaten ?? '' }}">
                        {{ $profile->kabupaten ?? '-' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Kecamatan</div>
                    <div class="fw-semibold" id="kecamatanLabel" data-code="{{ $profile->kecamatan ?? '' }}">
                        {{ $profile->kecamatan ?? '-' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Desa/Kelurahan</div>
                    <div class="fw-semibold" id="desaLabel" data-code="{{ $profile->desa ?? '' }}">
                        {{ $profile->desa ?? '-' }}
                    </div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Detail Alamat</div>
                    <div class="fw-semibold">{{ $profile->alamat ?? '-' }}</div>
                </div>
            </div>

            @php
                $roleName = '';
                if (auth()->check() && (auth()->user()->role_id ?? null)) {
                    $roleName = \App\Models\Role::where('id', auth()->user()->role_id)->value('name') ?? '';
                }
                $isAdminProfile = (auth()->user()->is_admin ?? false) || strtolower($roleName) === 'admin';
            @endphp
            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('profile.edit', profile_token_encode((string) $profile->id)) }}" class="btn btn-primary">
                    <i data-feather="edit-2"></i> Edit Profil
                </a>
                @if(!$isAdminProfile)
                    <a href="{{ url('/apps') }}" class="btn btn-outline-secondary">
                        <i data-feather="arrow-left"></i> Kembali
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  if (typeof feather !== 'undefined') feather.replace();

  const apiBase = @json(url('/api/wilayah'));
  const provEl = document.getElementById('provinsiLabel');
  const kabEl = document.getElementById('kabupatenLabel');
  const kecEl = document.getElementById('kecamatanLabel');
  const desaEl = document.getElementById('desaLabel');

  function setText(el, text) {
    if (!el) return;
    el.textContent = text || '-';
  }

  function setLoading(el) {
    if (!el) return;
    el.textContent = 'Memuat...';
  }

  async function fetchWilayah(url) {
    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = await res.json();
      return json?.data?.data ?? json?.data ?? [];
    } catch (err) {
      console.error('Gagal fetch wilayah:', err);
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'Gagal memuat data',
          text: 'Tidak dapat mengambil data wilayah. Silakan coba lagi.'
        });
      }
      return [];
    }
  }

  async function resolveWilayahLabels() {
    const provCode = provEl?.dataset.code || '';
    const kabCode = kabEl?.dataset.code || '';
    const kecCode = kecEl?.dataset.code || '';
    const desaCode = desaEl?.dataset.code || '';

    if (provCode) setLoading(provEl);
    if (kabCode) setLoading(kabEl);
    if (kecCode) setLoading(kecEl);
    if (desaCode) setLoading(desaEl);

    if (provCode) {
      const provinces = await fetchWilayah(`${apiBase}/provinces`);
      const prov = provinces.find((p) => p.code === provCode);
      setText(provEl, prov?.name || provCode);
    }

    const provFromKab = kabCode ? kabCode.split('.')[0] : '';
    if (kabCode && provFromKab) {
      const regencies = await fetchWilayah(`${apiBase}/regencies/${provFromKab}`);
      const kab = regencies.find((r) => r.code === kabCode);
      setText(kabEl, kab?.name || kabCode);
    }

    const regFromKec = kecCode ? kecCode.split('.').slice(0, 2).join('.') : '';
    if (kecCode && regFromKec) {
      const districts = await fetchWilayah(`${apiBase}/districts/${regFromKec}`);
      const kec = districts.find((d) => d.code === kecCode);
      setText(kecEl, kec?.name || kecCode);
    }

    const distFromDesa = desaCode ? desaCode.split('.').slice(0, 3).join('.') : '';
    if (desaCode && distFromDesa) {
      const villages = await fetchWilayah(`${apiBase}/villages/${distFromDesa}`);
      const des = villages.find((v) => v.code === desaCode);
      setText(desaEl, des?.name || desaCode);
    }
  }

  resolveWilayahLabels();
</script>
@endpush
