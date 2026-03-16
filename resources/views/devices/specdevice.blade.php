@extends('layouts.app')

@section('title','Spesifikasi Perangkat')

@section('content')
@php($spec = $editDevice->spec ?? null)
<h1 class="page-title mb-4">
  {{ isset($editDevice) ? 'Edit Spesifikasi Perangkat' : 'Spesifikasi Perangkat' }}
</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Tambah / Ubah Spesifikasi</h5>
      </div>
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('device.spec.save') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Perangkat</label>
            <select name="device_id" class="form-select" required>
              <option value="">-- pilih perangkat --</option>
              @foreach($devices as $dev)
                <option value="{{ $dev->id }}" {{ old('device_id', $editDevice->id ?? '') == $dev->id ? 'selected' : '' }}>
                  {{ $dev->device_name }} ({{ $dev->device_type }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Processor</label>
            <select name="processor" class="form-select" required>
              <option value="">Pilih Processor</option>
              @foreach(['Intel Core i3','Intel Core i5','Intel Core i7','Intel Core i9','AMD Ryzen 3','AMD Ryzen 5','AMD Ryzen 7'] as $cpu)
                <option value="{{ $cpu }}" {{ old('processor', $spec->processor ?? '') == $cpu ? 'selected' : '' }}>{{ $cpu }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">RAM</label>
            <select name="ram" class="form-select" required>
              <option value="">Pilih RAM</option>
              @foreach($ramOptions as $ram)
                <option value="{{ $ram }}" {{ old('ram', $spec->ram ?? '') == $ram ? 'selected' : '' }}>{{ $ram }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Storage Type</label>
            <select name="storage_type" class="form-select" required>
              <option value="HDD" {{ old('storage_type', $spec->storage_type ?? '') == 'HDD' ? 'selected' : '' }}>HDD</option>
              <option value="SSD" {{ old('storage_type', $spec->storage_type ?? 'SSD') == 'SSD' ? 'selected' : '' }}>SSD</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Storage Capacity</label>
            <select name="storage_capacity" class="form-select" required>
              <option value="">Pilih Kapasitas</option>
              @foreach($capacityOptions as $cap)
                <option value="{{ $cap }}" {{ old('storage_capacity', $spec->storage_capacity ?? '') == $cap ? 'selected' : '' }}>{{ $cap }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">IP Address</label>
            <input type="text" name="ip_address" class="form-control" placeholder="Contoh: 10.10.1.10" value="{{ old('ip_address', $spec->ip_address ?? '') }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Subnet</label>
            <input type="text" name="subnet" class="form-control" placeholder="Contoh: 255.255.255.0" value="{{ old('subnet', $spec->subnet ?? '') }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">GPU</label>
            <select name="gpu" class="form-select">
              <option value="">Pilih GPU</option>
              @foreach(['Integrated','Nvidia GTX','Nvidia RTX','AMD Radeon'] as $gpu)
                <option value="{{ $gpu }}" {{ old('gpu', $spec->gpu ?? '') == $gpu ? 'selected' : '' }}>{{ $gpu }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">OS</label>
            <select name="os" class="form-select" required>
              <option value="">Pilih OS</option>
              @foreach(['Windows 10','Windows 11','Ubuntu 22.04','Debian 12','macOS'] as $os)
                <option value="{{ $os }}" {{ old('os', $spec->os ?? '') == $os ? 'selected' : '' }}>{{ $os }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Detail</label>
            <textarea name="details" class="form-control" rows="3" placeholder="Keterangan lain">{{ old('details', $spec->details ?? '') }}</textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('device.index') }}" class="btn btn-outline-secondary">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
