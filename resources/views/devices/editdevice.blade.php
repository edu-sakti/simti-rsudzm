@extends('layouts.app')

@section('title', 'Edit Perangkat')

@section('content')
<h1 class="page-title mb-4">Edit Perangkat</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Form Edit Perangkat</h5>
      </div>
      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('device.update', $encoded) }}">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Perangkat</label>
            <input type="text" name="device_name" class="form-control" required
                   value="{{ old('device_name', $device->device_name) }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Ruangan</label>
            <select name="room_id" class="form-select">
              <option value="">-- pilih ruangan --</option>
              @foreach($rooms as $room)
                <option value="{{ $room->room_id }}"
                  {{ old('room_id', $device->room_id) == $room->room_id ? 'selected' : '' }}>
                  {{ $room->room_id }} - {{ $room->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Tipe Perangkat</label>
            <select name="device_type" class="form-select" required>
              <option value="">Pilih Tipe</option>
              @foreach($deviceTypes as $type)
                <option value="{{ $type }}" {{ old('device_type', $device->device_type) == $type ? 'selected' : '' }}>
                  {{ $type }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Merek</label>
            <input type="text" name="brand" class="form-control"
                   value="{{ old('brand', $device->brand) }}" placeholder="contoh: Cisco">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Model</label>
            <input type="text" name="model" class="form-control"
                   value="{{ old('model', $device->model) }}" placeholder="contoh: ISR4321">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Kondisi</label>
              <select name="condition" class="form-select">
                <option value="">Pilih Kondisi</option>
                @foreach(['Good','Damage','Maintenance'] as $cond)
                  <option value="{{ $cond }}" {{ old('condition', $device->condition) == $cond ? 'selected' : '' }}>
                    {{ $cond }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">Pilih Status</option>
                @foreach(['Active','Inactive'] as $st)
                  <option value="{{ $st }}" {{ old('status', $device->status) == $st ? 'selected' : '' }}>
                    {{ $st }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="mb-3 mt-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan lain">{{ old('notes', $device->notes) }}</textarea>
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
