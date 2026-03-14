@extends('layouts.guest')

@section('title', 'Verifikasi OTP')

@section('content')
<div class="text-center mt-4">
    <h1 class="h2">Verifikasi OTP</h1>
    <p class="lead">Masukkan kode OTP untuk melanjutkan</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="m-sm-3">
            <form method="POST" action="{{ route('auth.otp.verify') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kode OTP</label>
                    <input class="form-control form-control-lg @error('otp_code') is-invalid @enderror"
                           type="text"
                           name="otp_code"
                           placeholder="Masukkan kode OTP"
                           value="{{ old('otp_code') }}"
                           required
                           autofocus>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-lg btn-primary">Verifikasi</button>
                </div>
            </form>
            <div class="mt-3 text-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="resendOtpBtn" disabled>
                    Kirim ulang OTP (60)
                </button>
                <div class="text-muted small mt-2">Tombol aktif jika OTP belum diisi selama 1 menit.</div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    Tidak menerima kode?
   <span style="color: blue;"> Hubungi Admin</span>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    @if ($errors->any())
      const message = {!! json_encode($errors->first()) !!};
      Swal.fire({
        icon: 'error',
        title: 'Verifikasi gagal',
        text: message,
        confirmButtonText: 'OK'
      });
      document.querySelector('input[name="otp_code"]').focus();
    @endif

    const btn = document.getElementById('resendOtpBtn');
    const input = document.querySelector('input[name="otp_code"]');
    let remaining = 60;
    let timerDone = false;

    const updateBtn = () => {
      if (!btn) return;
      if (!timerDone) {
        btn.textContent = `Kirim ulang OTP (${remaining})`;
        btn.disabled = true;
        return;
      }
      const isEmpty = !input.value.trim();
      btn.disabled = !isEmpty;
      btn.textContent = isEmpty ? 'Kirim ulang OTP' : 'OTP sudah diisi';
    };

    const tick = () => {
      if (remaining <= 0) {
        timerDone = true;
        updateBtn();
        return;
      }
      remaining -= 1;
      updateBtn();
      setTimeout(tick, 1000);
    };

    updateBtn();
    setTimeout(tick, 1000);

    input.addEventListener('input', updateBtn);

    btn.addEventListener('click', async () => {
      if (btn.disabled) return;
      try {
        const res = await fetch("{{ route('auth.otp.resend') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        if (!res.ok) {
          throw new Error(data.message || 'Gagal mengirim OTP.');
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message || 'OTP berhasil dikirim ulang.' });
        }
        remaining = 60;
        timerDone = false;
        updateBtn();
        setTimeout(tick, 1000);
      } catch (e) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal mengirim OTP.' });
        }
      }
    });
  });
  </script>
@endpush
@endsection
