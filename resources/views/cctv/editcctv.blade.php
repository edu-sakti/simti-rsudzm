@extends('layouts.app')

@section('title', 'Edit CCTV')

@section('content')
<h1 class="page-title mb-4">Edit CCTV</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="card-title mb-0">Form Edit CCTV</h5>
      </div>
      <div class="card-body">

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('cctv.update', $cctv->id) }}">
          @csrf
          @method('PUT')

          <!-- Lokasi DVR -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Lokasi DVR</label>
            <select name="lokasi" class="form-select" required>
              <option value="">Pilih Ruangan</option>

              @foreach($rooms as $room)
                <option value="{{ $room->room_id }}"
                  {{ old('lokasi', $cctv->room_id) == $room->room_id ? 'selected' : '' }}>
                  {{ $room->room_id }} - {{ $room->name }}
                </option>
              @endforeach

            </select>
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select" required>
              <option value="aktif" {{ old('status', $cctv->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
              <option value="non_aktif" {{ old('status', $cctv->status) === 'non_aktif' ? 'selected' : '' }}>Non Aktif</option>
            </select>
          </div>

          <!-- Keterangan -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $cctv->keterangan) }}</textarea>
          </div>

          <!-- IP Kamera -->
          <div class="mb-3">
            <label class="form-label fw-semibold">IP Kamera</label>
            <input type="text" name="ip" class="form-control" value="{{ old('ip', $cctv->ip) }}" required>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Perbarui</button>
            <a href="{{ route('cctv.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection
