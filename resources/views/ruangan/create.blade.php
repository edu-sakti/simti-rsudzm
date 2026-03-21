@extends('layouts.app')

@section('title', 'Tambah Ruangan')

@section('content')
<h1 class="page-title mb-4">Tambah Ruangan</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Form Tambah Ruangan</h5>
        <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
          <i data-feather="arrow-left"></i>
          <span>Kembali</span>
        </a>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('rooms.store') }}">
          @csrf
          <div class="row g-3">

            <div class="col-md-6">
              <label for="kategori" class="form-label">Kategori Ruangan</label>
              <select id="kategori" name="kategori" class="form-select" required>
                <option value="">Pilih kategori</option>
                @foreach(($categories ?? []) as $label => $prefix)
                  <option value="{{ $label }}" {{ old('kategori') === $label ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @error('kategori')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="name" class="form-label">Nama Ruangan</label>
              <input type="text" id="name" name="name" class="form-control" placeholder="Masukkan nama ruangan" value="{{ old('name') }}" required>
              @error('name')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 text-end mt-3">
              <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i data-feather="save"></i>
                <span>Simpan</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
