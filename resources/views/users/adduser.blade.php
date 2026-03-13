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

            {{-- No Telepon + OTP --}}
            <div class="col-md-6">
              <label for="phone" class="form-label">No Telepon (format internasional)</label>
              <div class="input-group">
                <input type="text" id="phone" name="phone" class="form-control" placeholder="contoh: 62812xxxxxxx" value="{{ old('phone') }}" required>
                <button class="btn btn-outline-primary" type="button" id="btn-otp">OTP</button>
              </div>
              @error('phone')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
              <div id="otp-help" class="small text-muted mt-1"></div>
            </div>

            {{-- Input OTP --}}
            <div class="col-md-6">
              <label for="otp_code" class="form-label">Kode OTP</label>
              <input type="text" id="otp_code" name="otp_code" class="form-control" placeholder="Masukkan kode OTP" value="{{ old('otp_code') }}" required>
              @error('otp_code')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            {{-- Password --}}
            <div class="col-md-6">
              <label for="password" class="form-label">Password</label>
              <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
              @error('password')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            {{-- Role --}}
            <div class="col-md-6">
              <label for="role" class="form-label">Role</label>
              <select id="role" name="role" class="form-select" required>
                @if (!isset($invite_code))
                  <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                @endif
                @if (!isset($invite_code))
                  <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                  <option value="manajemen" {{ old('role') === 'manajemen' ? 'selected' : '' }}>Manajemen</option>
                  <option value="kepala_ruangan" {{ old('role') === 'kepala_ruangan' ? 'selected' : '' }}>Kepala Ruangan</option>
                @else
                  <option value="manajemen" {{ old('role') === 'manajemen' ? 'selected' : '' }}>Manajemen</option>
                  <option value="kepala_ruangan" {{ old('role') === 'kepala_ruangan' ? 'selected' : '' }}>Kepala Ruangan</option>
                @endif
              </select>
              @error('role')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            {{-- Ruangan (khusus kepala ruangan) --}}
            <div class="col-md-6" id="room-wrapper" style="display:none;">
              <label for="room_id" class="form-label">Ruangan</label>
              <select id="room_id" name="room_id" class="form-select">
                <option value="">Pilih Ruangan</option>
                @foreach($rooms as $room)
                  <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->name }}
                  </option>
                @endforeach
              </select>
              @error('room_id')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>


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

  (function () {
    const roleSelect = document.getElementById('role');
    const roomWrapper = document.getElementById('room-wrapper');
    const toggleRoom = () => {
      if (roleSelect.value === 'kepala_ruangan') {
        roomWrapper.style.display = '';
      } else {
        roomWrapper.style.display = 'none';
      }
    };
    roleSelect.addEventListener('change', toggleRoom);
    toggleRoom();
  })();

  (function () {
    const btnOtp = document.getElementById('btn-otp');
    const phoneInput = document.getElementById('phone');
    const otpHelp = document.getElementById('otp-help');
    btnOtp.addEventListener('click', async () => {
      const phone = phoneInput.value.trim();
      if (!/^62\d{8,15}$/.test(phone)) {
        otpHelp.textContent = 'No telepon harus format internasional (contoh: 62812xxxxxxx).';
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
