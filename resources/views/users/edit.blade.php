@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<h1 class="page-title mb-4">Edit Pengguna</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Form Edit Pengguna</h5>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
          <i data-feather="arrow-left"></i> Kembali
        </a>
      </div>

      <div class="card-body">
        <form method="POST" action="{{ route('users.update', $encoded ?? encrypt($user->username)) }}" class="row g-3">
          @csrf
          @method('PUT')

          {{-- Nama --}}
          <div class="col-md-6">
            <label for="edit_name" class="form-label">Nama Lengkap</label>
            <input type="text" id="edit_name" name="name" class="form-control" 
                   value="{{ old('name', $user->name) }}" required>
          </div>

          {{-- Username --}}
          <div class="col-md-6">
            <label for="edit_username" class="form-label">Username</label>
            <input type="text" id="edit_username" name="username" class="form-control" 
                   value="{{ old('username', $user->username) }}" required>
          </div>

          {{-- Email + OTP Email --}}
          <div class="col-md-6">
            <label for="edit_email" class="form-label">Email</label>
            <div class="input-group">
              <input type="email" id="edit_email" name="email" class="form-control"
                     value="{{ old('email', $user->email) }}" placeholder="Masukkan email aktif" required>
              @if(filter_var(env('OTP_EMAIL_ENABLED', env('EMAIL_OTP_ENABLED', true)), FILTER_VALIDATE_BOOLEAN))
                <button class="btn btn-outline-primary" type="button" id="btn-email-otp-edit">OTP Email</button>
              @endif
            </div>
            @error('email')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
            <div id="email-otp-help-edit" class="small text-muted mt-1"></div>
          </div>

          {{-- No Telepon + OTP --}}
          <div class="col-md-6">
            <label for="edit_phone" class="form-label">No Telepon</label>
            <div class="input-group">
              <input type="text" id="edit_phone" name="phone" class="form-control"
                     value="{{ old('phone', $user->phone) }}" placeholder="contoh: 62812xxxxxxx atau 0812xxxxxxx" required>
              @if(filter_var(env('OTP_PHONE_ENABLED', env('OTP_ENABLED', true)), FILTER_VALIDATE_BOOLEAN))
                <button class="btn btn-outline-primary" type="button" id="btn-otp-edit">OTP</button>
              @endif
            </div>
            @error('phone')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
            <div id="otp-help-edit" class="small text-muted mt-1"></div>
          </div>

          {{-- Input OTP Telepon (muncul jika phone berubah) --}}
          @if(filter_var(env('OTP_PHONE_ENABLED', env('OTP_ENABLED', true)), FILTER_VALIDATE_BOOLEAN))
            <div class="col-md-6" id="otp-wrapper-edit" style="display:none;">
              <label for="otp_code_edit" class="form-label">Kode OTP Telepon</label>
              <input type="text" id="otp_code_edit" name="otp_code" class="form-control"
                     placeholder="Masukkan kode OTP Telepon" value="{{ old('otp_code') }}">
              @error('otp_code')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>
          @endif

          {{-- Input OTP Email (muncul jika email berubah) --}}
          @if(filter_var(env('OTP_EMAIL_ENABLED', env('EMAIL_OTP_ENABLED', true)), FILTER_VALIDATE_BOOLEAN))
            <div class="col-md-6" id="otp-email-wrapper-edit" style="display:none;">
              <label for="email_otp_code_edit" class="form-label">Kode OTP Email</label>
              <input type="text" id="email_otp_code_edit" name="email_otp_code" class="form-control"
                     placeholder="Masukkan kode OTP Email" value="{{ old('email_otp_code') }}">
              @error('email_otp_code')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>
          @endif

          {{-- Admin Access --}}
          <div class="col-md-6">
            <label class="form-label d-block">Akses Admin</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin', ($user->is_admin ?? false)) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_admin">Jadikan sebagai Admin</label>
            </div>
            <div class="text-muted small mt-1">Jika dicentang, user ini memiliki akses admin.</div>
          </div>

          {{-- (Email dihapus sesuai permintaan) --}}

          {{-- Password selalu terlihat --}}
          <div class="col-md-6">
            <label for="edit_password" class="form-label">Password (opsional)</label>
            <div class="input-group">
              <input type="password" id="edit_password" name="password" class="form-control" 
                     placeholder="Biarkan kosong jika tidak diubah" value="{{ old('password') }}">
              <button class="btn btn-outline-secondary" type="button" data-toggle="password" data-target="#edit_password" aria-label="Tampilkan password">
                <i data-feather="eye-off"></i>
              </button>
            </div>
          </div>

          {{-- Tombol simpan --}}
          <div class="col-12 text-end mt-3">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
              <i data-feather="save"></i>
              <span>Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  if (typeof feather !== 'undefined') feather.replace();

  @if(filter_var(env('OTP_PHONE_ENABLED', env('OTP_ENABLED', true)), FILTER_VALIDATE_BOOLEAN))
  (function () {
    const originalPhone = "{{ $user->phone ?? '' }}";
    const phoneInput = document.getElementById('edit_phone');
    const otpWrapper = document.getElementById('otp-wrapper-edit');
    const btnOtp = document.getElementById('btn-otp-edit');
    const otpHelp = document.getElementById('otp-help-edit');

    const toggleOtp = () => {
      const changed = phoneInput.value.trim() !== (originalPhone || '').trim();
      if (changed) {
        otpWrapper.style.display = '';
        btnOtp.disabled = false;
        btnOtp.classList.remove('disabled');
      } else {
        otpWrapper.style.display = 'none';
        btnOtp.disabled = true;
        btnOtp.classList.add('disabled');
      }
    };
    phoneInput.addEventListener('input', toggleOtp);
    toggleOtp();

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
          body: JSON.stringify({
            phone,
            current_username: "{{ $user->username }}"
          })
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

  @if(filter_var(env('OTP_EMAIL_ENABLED', env('EMAIL_OTP_ENABLED', true)), FILTER_VALIDATE_BOOLEAN))
  (function () {
    const originalEmail = "{{ strtolower((string) ($user->email ?? '')) }}";
    const username = "{{ $user->username }}";
    const emailInput = document.getElementById('edit_email');
    const otpWrapper = document.getElementById('otp-email-wrapper-edit');
    const btnEmailOtp = document.getElementById('btn-email-otp-edit');
    const emailOtpHelp = document.getElementById('email-otp-help-edit');

    const toggleEmailOtp = () => {
      const changed = emailInput.value.trim().toLowerCase() !== originalEmail;
      if (changed) {
        otpWrapper.style.display = '';
        btnEmailOtp.disabled = false;
        btnEmailOtp.classList.remove('disabled');
      } else {
        otpWrapper.style.display = 'none';
        btnEmailOtp.disabled = true;
        btnEmailOtp.classList.add('disabled');
      }
    };

    emailInput.addEventListener('input', toggleEmailOtp);
    toggleEmailOtp();

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
          body: JSON.stringify({
            email,
            current_username: username
          })
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
  @if (session('error'))
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: {!! json_encode(session('error')) !!}
      });
    }
  @endif
</script>
@endsection


