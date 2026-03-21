@extends('layouts.guest')

@section('title', 'Ganti Password')
@section('guest_col_class', 'col-sm-10 col-md-6 col-lg-5 col-xl-4')

@section('content')
<div class="text-center mt-4">
    <h1 class="h2">Ganti Password</h1>
    <p class="lead">Masukkan password baru untuk akun Anda</p>
</div>

<div class="card mx-auto" style="max-width: 520px;">
    <div class="card-body">
        <div class="m-sm-3">
            <form method="POST" action="/change-password/{{ $token }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <div class="input-group input-group-lg">
                      <input id="new_password" class="form-control @error('password') is-invalid @enderror"
                             type="password"
                             name="password"
                             placeholder="Masukkan password baru"
                             required>
                      <button class="btn btn-outline-secondary" type="button" data-toggle="password" data-target="#new_password" aria-label="Tampilkan password">
                        <i data-feather="eye-off"></i>
                      </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-group input-group-lg">
                      <input id="confirm_password" class="form-control"
                             type="password"
                             name="password_confirmation"
                             placeholder="Ulangi password"
                             required>
                      <button class="btn btn-outline-secondary" type="button" data-toggle="password" data-target="#confirm_password" aria-label="Tampilkan password">
                        <i data-feather="eye-off"></i>
                      </button>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-lg btn-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    Kembali ke halaman
   <a href="/auth/login" class="text-primary">login</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const error = @json($errors->first());
    const success = @json(session('success'));
    if (error && typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Gagal', text: error });
    }
    if (success && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: success,
        timer: 1800,
        showConfirmButton: false
      }).then(() => {
        window.location.href = '/auth/login';
      });
    }
  });
</script>
@endpush
