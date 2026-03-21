@extends('layouts.app')

@section('title', 'Tambah IP Address')

@section('content')
<h1 class="page-title mb-4">Tambah IP Address</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="card-title mb-0">Form Tambah IP Address</h5>
      </div>
      <div class="card-body">

        <form method="POST" action="{{ url('/ip-address/tambah') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label fw-semibold">IP Address</label>
            <input type="text" name="ip_address" class="form-control" placeholder="contoh: 10.10.1.2" value="{{ old('ip_address') }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Subnet</label>
            <input type="text" name="subnet" class="form-control" placeholder="contoh: 255.255.255.0" value="{{ old('subnet') }}">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select" required>
              <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
              <option value="used" {{ old('status') === 'used' ? 'selected' : '' }}>Used</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Catatan tambahan">{{ old('description') }}</textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('ipaddr.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  (function() {
    const success = @json(session('success'));
    const errorMessage = @json(session('error'));
    const errors = @json($errors->all());
    if (success && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: success,
        timer: 1800,
        showConfirmButton: false
      });
    }
    if (errorMessage && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: errorMessage
      });
    }
    if (errors.length && typeof Swal !== 'undefined') {
      const list = '<ul style="text-align:left;margin:0;padding-left:18px;">' +
        errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
      Swal.fire({
        icon: 'error',
        title: 'Validasi gagal',
        html: list
      });
    }
  })();
</script>
@endsection
