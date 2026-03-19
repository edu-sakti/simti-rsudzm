@extends('layouts.guest')

@section('title', 'Verifikasi OTP')

@push('styles')
<style>
  .otp-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
  }
  .otp-input {
    width: 48px;
    height: 52px;
    border: 1px solid #d7dce5;
    border-radius: 8px;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 600;
    background: #f8fafc;
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .otp-input:focus {
    outline: none;
    border-color: #3b7ddd;
    box-shadow: 0 0 0 .2rem rgba(59, 125, 221, .15);
    background: #fff;
  }
  .otp-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .15);
  }
</style>
@endpush

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
                    <input type="hidden" name="otp_code" id="otp_code" value="{{ old('otp_code') }}">
                    <div class="otp-inputs">
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="1"
                                class="otp-input @error('otp_code') is-invalid @enderror"
                                autocomplete="one-time-code"
                                aria-label="Digit OTP {{ $i + 1 }}"
                            >
                        @endfor
                    </div>
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
      const firstOtp = document.querySelector('.otp-input');
      if (firstOtp) firstOtp.focus();
    @endif

    const btn = document.getElementById('resendOtpBtn');
    const hiddenInput = document.getElementById('otp_code');
    const otpInputs = Array.from(document.querySelectorAll('.otp-input'));
    let remaining = 60;
    let timerDone = false;

    const getOtpValue = () => otpInputs.map((el) => el.value.trim()).join('');
    const syncHidden = () => {
      if (hiddenInput) hiddenInput.value = getOtpValue();
    };

    if (hiddenInput && hiddenInput.value) {
      const preset = hiddenInput.value.replace(/\D/g, '');
      otpInputs.forEach((el, i) => {
        el.value = preset[i] || '';
      });
    }

    const updateBtn = () => {
      if (!btn) return;
      if (!timerDone) {
        btn.textContent = `Kirim ulang OTP (${remaining})`;
        btn.disabled = true;
        return;
      }
      const isEmpty = getOtpValue().length === 0;
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

    otpInputs.forEach((input, index) => {
      input.addEventListener('input', (e) => {
        const value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
        if (value && otpInputs[index + 1]) {
          otpInputs[index + 1].focus();
        }
        syncHidden();
        updateBtn();
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && otpInputs[index - 1]) {
          otpInputs[index - 1].focus();
        }
      });

      input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        if (!pasted) return;
        otpInputs.forEach((el, i) => {
          el.value = pasted[i] || '';
        });
        if (otpInputs[Math.min(pasted.length, otpInputs.length) - 1]) {
          otpInputs[Math.min(pasted.length, otpInputs.length) - 1].focus();
        }
        syncHidden();
        updateBtn();
      });
    });

    syncHidden();
    if (otpInputs[0]) {
      otpInputs[0].focus();
    }

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
