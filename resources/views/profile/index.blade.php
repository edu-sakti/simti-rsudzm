@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Profil</h1>

    @include('profile._tabs', ['activeTab' => 'data-utama'])

    @include('profile.data-utama', ['profile' => $profile])
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
