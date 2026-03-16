@extends('layouts.app')

@section('title', 'Tambah Perangkat')

@section('content')
<h1 class="page-title mb-4">Tambah Perangkat</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="card-title mb-0">Form Tambah Perangkat</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('device.store') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Perangkat</label>
            <input type="text" name="device_name" class="form-control" placeholder="contoh: Router Core" value="{{ old('device_name') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Ruangan</label>
            <select name="room_id" class="form-select" required>
              <option value="">Pilih Ruangan</option>
              @foreach($rooms ?? [] as $room)
                <option value="{{ $room->room_id }}" {{ old('room_id') == $room->room_id ? 'selected' : '' }}>
                  {{ $room->room_id }} - {{ $room->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Tipe Perangkat</label>
            <select name="device_type" id="device_type" class="form-select" required>
              <option value="">Pilih Tipe</option>
              @foreach($deviceTypes ?? ['CPU','Monitor','PC AIO','Laptop','Router','Hub','Printer','Telepon'] as $type)
                <option value="{{ $type }}" {{ old('device_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
              @endforeach
            </select>
          </div>

          {{-- kolom IP dihilangkan sesuai permintaan --}}

          <div class="mb-3">
            <label class="form-label fw-semibold">Merek</label>
            <input type="text" name="brand" class="form-control" placeholder="contoh: Cisco" value="{{ old('brand') }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Model</label>
            <input type="text" name="model" class="form-control" placeholder="contoh: ISR4321" value="{{ old('model') }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Kondisi</label>
            <select name="condition" class="form-select">
              <option value="">Pilih Kondisi</option>
              @foreach(['Good' => 'Baik', 'Damage' => 'Rusak', 'Maintenance' => 'Perawatan'] as $cond => $label)
                <option value="{{ $cond }}" {{ old('condition') == $cond ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="">Pilih Status</option>
              @foreach(['Active' => 'Aktif', 'Inactive' => 'Non Aktif'] as $val => $label)
                <option value="{{ $val }}" {{ old('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('device.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
