@extends(isset($invite_code) ? 'layouts.guest' : 'layouts.app')

@if (isset($invite_code))
@section('guest_col_class', 'col-sm-12 col-md-10 col-lg-9 col-xl-8')
@endif

@section('title', 'Tambah Pengguna')

@section('content')
<h1 class="page-title mb-4">Tambah Pengguna</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Form Tambah Pengguna Baru</h5>
        @if (!isset($invite_code))
          <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i data-feather="arrow-left"></i>
            <span>Kembali</span>
          </a>
        @endif
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($invite_code) ? url('/pengguna/tambah/'.$invite_code) : route('users.store') }}">
          @csrf
          <div class="row g-3">

            {{-- Nama Lengkap --}}
            <div class="col-md-6">
              <label for="full_name" class="form-label">Nama Lengkap</label>
              <input type="text" id="full_name" name="name" class="form-control" placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required>
              @error('name')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            {{-- Username --}}
            <div class="col-md-6">
              <label for="username" class="form-label">Nama Pengguna</label>
              <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required>
              @error('username')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            {{-- Email --}}
            <div class="col-md-6">
              <label for="email" class="form-label">Email</label>
              <div class="input-group">
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email aktif" value="{{ old('email') }}" required>
                @if(filter_var(env('EMAIL_OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
                  <button class="btn btn-outline-primary" type="button" id="btn-email-otp">OTP Email</button>
                @endif
              </div>
              @error('email')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
              <div id="email-otp-help" class="small text-muted mt-1"></div>
            </div>

            {{-- No Telepon + OTP --}}
            <div class="col-md-6">
              <label for="phone" class="form-label">No Telepon</label>
            <div class="input-group">
              <input type="text" id="phone" name="phone" class="form-control" placeholder="contoh: 62812xxxxxxx atau 0812xxxxxxx" value="{{ old('phone') }}" required>
              @if(filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
                <button class="btn btn-outline-primary" type="button" id="btn-otp">OTP</button>
              @endif
            </div>
              @error('phone')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
              <div id="otp-help" class="small text-muted mt-1"></div>
            </div>

            {{-- Input OTP Telepon --}}
            @if(filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
              <div class="col-md-6">
                <label for="otp_code" class="form-label">Kode OTP Telepon</label>
                <input type="text" id="otp_code" name="otp_code" class="form-control" placeholder="Masukkan kode OTP Telepon" value="{{ old('otp_code') }}" required>
                @error('otp_code')
                  <div class="text-danger small">{{ $message }}</div>
                @enderror
              </div>
            @endif

            @if(filter_var(env('EMAIL_OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
              <div class="col-md-6">
                <label for="email_otp_code" class="form-label">Kode OTP Email</label>
                <input type="text" id="email_otp_code" name="email_otp_code" class="form-control" placeholder="Masukkan kode OTP Email" value="{{ old('email_otp_code') }}" required>
                @error('email_otp_code')
                  <div class="text-danger small">{{ $message }}</div>
                @enderror
              </div>
            @endif

            {{-- Password --}}
            <div class="col-md-6">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                <button class="btn btn-outline-secondary" type="button" data-toggle="password" data-target="#password" aria-label="Tampilkan password">
                  <i data-feather="eye-off"></i>
                </button>
              </div>
              @error('password')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            {{-- Admin Access --}}
            @if (!isset($invite_code))
              <div class="col-md-6">
                <label class="form-label d-block">Akses Admin</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_admin">Jadikan sebagai Admin</label>
                </div>
                <div class="text-muted small mt-1">Jika dicentang, user ini memiliki akses admin.</div>
              </div>
            @endif

            {{-- Tombol Aksi --}}
            <div class="col-12 text-end mt-4 d-flex justify-content-end gap-2">
              <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i data-feather="user-plus"></i>
                <span>Tambah Pengguna</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Feather Icon Support --}}
<script>
  if (typeof feather !== 'undefined') {
    feather.replace();
  }

  @if(filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
  (function () {
    const btnOtp = document.getElementById('btn-otp');
    const phoneInput = document.getElementById('phone');
    const otpHelp = document.getElementById('otp-help');
    btnOtp.addEventListener('click', async () => {
      const phone = phoneInput.value.trim();
      if (!/^(?:62|0)8\d{7,14}$/.test(phone)) {
        otpHelp.textContent = 'No telepon harus format 628xxx atau 08xxx.';
        otpHelp.classList.add('text-danger');
        return;
      }
      otpHelp.textContent = 'Mengirim OTP...';
      otpHelp.classList.remove('text-danger');
      try {
        const res = await fetch("{{ route('users.otp') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ phone, invite_code: "{{ $invite_code ?? '' }}" })
        });
        const data = await res.json();
        if (!res.ok) {
          otpHelp.textContent = data.message || 'Gagal mengirim OTP.';
          otpHelp.classList.add('text-danger');
          return;
        }
        otpHelp.textContent = data.message || 'OTP terkirim.';
      } catch (e) {
        otpHelp.textContent = 'Gagal mengirim OTP.';
        otpHelp.classList.add('text-danger');
      }
    });
  })();
  @endif

  @if(filter_var(env('EMAIL_OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
  (function () {
    const btnEmailOtp = document.getElementById('btn-email-otp');
    const emailInput = document.getElementById('email');
    const emailOtpHelp = document.getElementById('email-otp-help');
    btnEmailOtp.addEventListener('click', async () => {
      const email = emailInput.value.trim();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        emailOtpHelp.textContent = 'Format email tidak valid.';
        emailOtpHelp.classList.add('text-danger');
        return;
      }
      emailOtpHelp.textContent = 'Mengirim OTP Email...';
      emailOtpHelp.classList.remove('text-danger');
      try {
        const res = await fetch("{{ route('users.email-otp') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ email, invite_code: "{{ $invite_code ?? '' }}" })
        });
        const data = await res.json();
        if (!res.ok) {
          const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
          emailOtpHelp.textContent = firstError || data.message || 'Gagal mengirim OTP Email.';
          emailOtpHelp.classList.add('text-danger');
          return;
        }
        emailOtpHelp.textContent = data.message || 'OTP Email terkirim.';
      } catch (e) {
        emailOtpHelp.textContent = 'Gagal mengirim OTP Email.';
        emailOtpHelp.classList.add('text-danger');
      }
    });
  })();
  @endif
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  @if ($errors->any())
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        html: `{!! implode('<br>', $errors->all()) !!}`
      });
    }
  @endif
</script>

@endsection
