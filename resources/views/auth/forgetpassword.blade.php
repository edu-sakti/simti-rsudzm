@extends('layouts.guest')

@section('title', 'Reset Password')
@section('guest_col_class', 'col-sm-10 col-md-6 col-lg-5 col-xl-4')

@section('content')
<div class="text-center mt-4">
    <h1 class="h2">Reset Password</h1>
    <p class="lead">Masukkan nomor HP untuk reset password</p>
</div>

<div class="card mx-auto" style="max-width: 520px;">
    <div class="card-body">
        <div class="m-sm-3">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <form method="POST" action="/forget-password" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label">No HP</label>
                    <input class="form-control form-control-lg @error('phone') is-invalid @enderror"
                           type="text"
                           name="phone"
                           placeholder="Contoh: 628xxx"
                           value="{{ old('phone') }}"
                           required
                           autofocus>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-lg btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    Kembali ke halaman
   <a href="/auth/login" class="text-primary">login</a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const success = @json(session('success'));
    const error = @json(session('error'));
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
    if (error && typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Gagal', text: error });
    }
  });
</script>
@endpush

@endsection
