@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Edit Profil</h1>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Profil</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('pegawai.update', $token) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $profile->nama ?? ($user->name ?? '')) }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                            <option value="">Pilih</option>
                            <option value="laki-laki" @selected(old('jenis_kelamin', $profile->jenis_kelamin) === 'laki-laki')>Laki-laki</option>
                            <option value="perempuan" @selected(old('jenis_kelamin', $profile->jenis_kelamin) === 'perempuan')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                               value="{{ old('tempat_lahir', $profile->tempat_lahir) }}">
                        @error('tempat_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                               value="{{ old('tanggal_lahir', $profile->tanggal_lahir) }}">
                        @error('tanggal_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Agama</label>
                        <select name="agama" class="form-select @error('agama') is-invalid @enderror">
                            <option value="">Pilih</option>
                            @foreach(['Islam','Kristen Protestan','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                <option value="{{ $agama }}" @selected(old('agama', $profile->agama) === $agama)>{{ $agama }}</option>
                            @endforeach
                        </select>
                        @error('agama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status Perkawinan</label>
                        <select name="status_perkawinan" class="form-select @error('status_perkawinan') is-invalid @enderror">
                            <option value="">Pilih</option>
                            @foreach(['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'] as $status)
                                <option value="{{ $status }}" @selected(old('status_perkawinan', $profile->status_perkawinan) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        @error('status_perkawinan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Provinsi</label>
                        <select name="provinsi" id="provinsi" class="form-select @error('provinsi') is-invalid @enderror"
                                data-selected="{{ old('provinsi', $profile->provinsi ?? null) }}">
                            <option value="">Pilih Provinsi</option>
                        </select>
                        @error('provinsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kabupaten/Kota</label>
                        <select name="kabupaten" id="kabupaten" class="form-select @error('kabupaten') is-invalid @enderror"
                                data-selected="{{ old('kabupaten', $profile->kabupaten ?? null) }}" disabled>
                            <option value="">Pilih Kabupaten/Kota</option>
                        </select>
                        @error('kabupaten')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kecamatan</label>
                        <select name="kecamatan" id="kecamatan" class="form-select @error('kecamatan') is-invalid @enderror"
                                data-selected="{{ old('kecamatan', $profile->kecamatan ?? null) }}" disabled>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                        @error('kecamatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Desa/Kelurahan</label>
                        <select name="desa" id="desa" class="form-select @error('desa') is-invalid @enderror"
                                data-selected="{{ old('desa', $profile->desa ?? null) }}" disabled>
                            <option value="">Pilih Desa/Kelurahan</option>
                        </select>
                        @error('desa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Detail Alamat (jalan, lorong, dll)</label>
                        <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $profile->alamat) }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Simpan
                    </button>
                    <a href="{{ url('/pegawai') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  if (typeof feather !== 'undefined') feather.replace();

  const apiBase = @json(url('/api/wilayah'));
  const provinsi = document.getElementById('provinsi');
  const kabupaten = document.getElementById('kabupaten');
  const kecamatan = document.getElementById('kecamatan');
  const desa = document.getElementById('desa');

  function setLoading(select, loading, placeholder) {
    if (!select) return;
    if (loading) {
      select.innerHTML = `<option value="">${placeholder || 'Pilih'}</option>`;
      select.disabled = true;
    }
  }

  function resetSelect(select, placeholder) {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = true;
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

  async function loadProvinsi() {
    setLoading(provinsi, true, 'Pilih Provinsi');
    const data = await fetchWilayah(`${apiBase}/provinces`);
    resetSelect(provinsi, 'Pilih Provinsi');
    data.forEach((item) => {
      const opt = document.createElement('option');
      opt.value = item.code;
      opt.textContent = item.name;
      provinsi.appendChild(opt);
    });
    const selected = provinsi.dataset.selected;
    if (selected) provinsi.value = selected;
    provinsi.disabled = false;
    if (provinsi.value) {
      await loadKabupaten(provinsi.value);
    }
  }

  async function loadKabupaten(provinceId) {
    resetSelect(kabupaten, 'Pilih Kabupaten/Kota');
    resetSelect(kecamatan, 'Pilih Kecamatan');
    resetSelect(desa, 'Pilih Desa/Kelurahan');
    if (!provinceId) return;
    setLoading(kabupaten, true, 'Pilih Kabupaten/Kota');
    const data = await fetchWilayah(`${apiBase}/regencies/${provinceId}`);
    data.forEach((item) => {
      const opt = document.createElement('option');
      opt.value = item.code;
      opt.textContent = item.name;
      kabupaten.appendChild(opt);
    });
    const selected = kabupaten.dataset.selected;
    if (selected) kabupaten.value = selected;
    kabupaten.disabled = false;
    if (kabupaten.value) {
      await loadKecamatan(kabupaten.value);
    }
  }

  async function loadKecamatan(regencyId) {
    resetSelect(kecamatan, 'Pilih Kecamatan');
    resetSelect(desa, 'Pilih Desa/Kelurahan');
    if (!regencyId) return;
    setLoading(kecamatan, true, 'Pilih Kecamatan');
    const data = await fetchWilayah(`${apiBase}/districts/${regencyId}`);
    data.forEach((item) => {
      const opt = document.createElement('option');
      opt.value = item.code;
      opt.textContent = item.name;
      kecamatan.appendChild(opt);
    });
    const selected = kecamatan.dataset.selected;
    if (selected) kecamatan.value = selected;
    kecamatan.disabled = false;
    if (kecamatan.value) {
      await loadDesa(kecamatan.value);
    }
  }

  async function loadDesa(districtId) {
    resetSelect(desa, 'Pilih Desa/Kelurahan');
    if (!districtId) return;
    setLoading(desa, true, 'Pilih Desa/Kelurahan');
    const data = await fetchWilayah(`${apiBase}/villages/${districtId}`);
    data.forEach((item) => {
      const opt = document.createElement('option');
      opt.value = item.code;
      opt.textContent = item.name;
      desa.appendChild(opt);
    });
    const selected = desa.dataset.selected;
    if (selected) desa.value = selected;
    desa.disabled = false;
  }

  provinsi?.addEventListener('change', () => loadKabupaten(provinsi.value));
  kabupaten?.addEventListener('change', () => loadKecamatan(kabupaten.value));
  kecamatan?.addEventListener('change', () => loadDesa(kecamatan.value));

  function validateProfileForm(event) {
    const fields = [
      { selector: 'input[name="nama"]', label: 'Nama' },
      { selector: 'select[name="jenis_kelamin"]', label: 'Jenis Kelamin' },
      { selector: 'input[name="tempat_lahir"]', label: 'Tempat Lahir' },
      { selector: 'input[name="tanggal_lahir"]', label: 'Tanggal Lahir' },
      { selector: 'select[name="agama"]', label: 'Agama' },
      { selector: 'select[name="status_perkawinan"]', label: 'Status Perkawinan' },
      { selector: 'select[name="provinsi"]', label: 'Provinsi' },
      { selector: 'select[name="kabupaten"]', label: 'Kabupaten/Kota' },
      { selector: 'select[name="kecamatan"]', label: 'Kecamatan' },
      { selector: 'select[name="desa"]', label: 'Desa/Kelurahan' },
      { selector: 'textarea[name="alamat"]', label: 'Detail Alamat' },
    ];

    const missing = fields
      .map((field) => {
        const el = document.querySelector(field.selector);
        const val = (el?.value || '').trim();
        return val ? null : field.label;
      })
      .filter(Boolean);

    if (missing.length) {
      event.preventDefault();
      const message = `<div style="text-align:left">Mohon lengkapi:<ul style="margin:8px 0 0 18px;">${missing
        .map((item) => `<li>${item}</li>`)
        .join('')}</ul></div>`;
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Data belum lengkap',
          html: message,
        });
      } else {
        alert('Mohon lengkapi data profile terlebih dahulu.');
      }
      return false;
    }
    return true;
  }

  document.querySelector('form')?.addEventListener('submit', validateProfileForm);

  loadProvinsi();
  @if (session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: @json(session('success')),
      timer: 1600,
      showConfirmButton: false
    });
  @endif
  @if ($errors->any())
    Swal.fire({
      icon: 'error',
      title: 'Gagal menyimpan',
      text: @json($errors->first())
    });
  @endif
</script>
@endpush

