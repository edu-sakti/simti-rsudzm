@extends('layouts.guest')

@section('title', 'Masuk Aplikasi')

@section('content')
<div class="text-center mt-4">
    <h1 class="h2">Selamat Datang</h1>
    <p class="lead">Masuk untuk melanjutkan ke SIMTI RSUDZM</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="m-sm-3">
            <form method="POST" action="{{ route('auth.login') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input class="form-control form-control-lg @error('username') is-invalid @enderror" type="text" name="username" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group input-group-lg">
                      <input id="login_password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" placeholder="Masukkan password" required>
                      <button class="btn btn-outline-secondary" type="button" data-toggle="password" data-target="#login_password" aria-label="Tampilkan password">
                        <i data-feather="eye-off"></i>
                      </button>
                    </div>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-lg btn-primary">Masuk</button>
                </div>
                <div class="text-center mt-3">
                    Lupa password? Silakan lakukan <a href="/forget-password" class="text-primary">reset password.</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    Belum punya akun?
   <span style="color: blue;"> Silahkan Lapor ke Admin</span>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const success = @json(session('success'));
    if (success && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: success,
        timer: 1800,
        showConfirmButton: false
      });
    }
    @if ($errors->any())
      const message = {!! json_encode($errors->first()) !!};
      Swal.fire({
        icon: 'error',
        title: 'Login gagal',
        text: message,
        confirmButtonText: 'OK'
      });
      // Fokus ke field yang error terlebih dulu
      @if ($errors->has('username'))
        document.querySelector('input[name="username"]').focus();
      @elseif ($errors->has('password'))
        document.querySelector('input[name="password"]').focus();
      @endif
    @endif
  });
  </script>
@endpush
@endsection
