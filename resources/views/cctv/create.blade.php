@extends('layouts.app')

@section('title', 'Tambah CCTV')

@section('content')
  <h1 class="page-title mb-4">Tambah CCTV</h1>

  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="card-title mb-0">Form Tambah CCTV</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('cctv.store') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label fw-semibold">Lokasi DVR</label>
              <select name="lokasi" class="form-select" required>
                <option value="">Pilih Ruangan</option>
                @foreach($rooms ?? [] as $room)
                  <option value="{{ $room->room_id }}">{{ $room->room_id }} - {{ $room->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select" required>
                <option value="aktif">Aktif</option>
                <option value="non_aktif">Non Aktif</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Keterangan</label>
              <textarea name="keterangan" class="form-control" rows="3" placeholder="Tulis keterangan..."></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">IP Kamera</label>
              <input type="text" name="ip" class="form-control" placeholder="contoh: 192.168.1.10" required>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Simpan</button>
              <a href="{{ route('cctv.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
