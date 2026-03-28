@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    <ul class="nav nav-tabs mb-3" id="profilTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-data-utama" data-bs-toggle="tab" data-bs-target="#panel-data-utama" type="button" role="tab" aria-controls="panel-data-utama" aria-selected="true">
                Data Utama
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-golongan" data-bs-toggle="tab" data-bs-target="#panel-golongan" type="button" role="tab" aria-controls="panel-golongan" aria-selected="false">
                Golongan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-jabatan" data-bs-toggle="tab" data-bs-target="#panel-jabatan" type="button" role="tab" aria-controls="panel-jabatan" aria-selected="false">
                Jabatan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-posisi" data-bs-toggle="tab" data-bs-target="#panel-posisi" type="button" role="tab" aria-controls="panel-posisi" aria-selected="false">
                Posisi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-pendidikan" data-bs-toggle="tab" data-bs-target="#panel-pendidikan" type="button" role="tab" aria-controls="panel-pendidikan" aria-selected="false">
                Pendidikan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-profesi" data-bs-toggle="tab" data-bs-target="#panel-profesi" type="button" role="tab" aria-controls="panel-profesi" aria-selected="false">
                Profesi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-keluarga" data-bs-toggle="tab" data-bs-target="#panel-keluarga" type="button" role="tab" aria-controls="panel-keluarga" aria-selected="false">
                Keluarga
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-pensiun" data-bs-toggle="tab" data-bs-target="#panel-pensiun" type="button" role="tab" aria-controls="panel-pensiun" aria-selected="false">
                Pensiun
            </button>
        </li>
    </ul>

    <div class="tab-content" id="profilTabsContent">
        <div class="tab-pane fade show active" id="panel-data-utama" role="tabpanel" aria-labelledby="tab-data-utama" tabindex="0">
            @include('profile.data-utama', ['profile' => $profile])
        </div>
        <div class="tab-pane fade" id="panel-golongan" role="tabpanel" aria-labelledby="tab-golongan" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data golongan.</div></div>
        </div>
        <div class="tab-pane fade" id="panel-jabatan" role="tabpanel" aria-labelledby="tab-jabatan" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data jabatan.</div></div>
        </div>
        <div class="tab-pane fade" id="panel-posisi" role="tabpanel" aria-labelledby="tab-posisi" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data posisi.</div></div>
        </div>
        <div class="tab-pane fade" id="panel-pendidikan" role="tabpanel" aria-labelledby="tab-pendidikan" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data pendidikan.</div></div>
        </div>
        <div class="tab-pane fade" id="panel-profesi" role="tabpanel" aria-labelledby="tab-profesi" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data profesi.</div></div>
        </div>
        <div class="tab-pane fade" id="panel-keluarga" role="tabpanel" aria-labelledby="tab-keluarga" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data keluarga.</div></div>
        </div>
        <div class="tab-pane fade" id="panel-pensiun" role="tabpanel" aria-labelledby="tab-pensiun" tabindex="0">
            <div class="card"><div class="card-body text-muted">Belum ada data pensiun.</div></div>
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
