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

          {{-- No Telepon + OTP --}}
          <div class="col-md-6">
            <label for="edit_phone" class="form-label">No Telepon (format internasional)</label>
            <div class="input-group">
              <input type="text" id="edit_phone" name="phone" class="form-control"
                     value="{{ old('phone', $user->phone) }}" placeholder="contoh: 62812xxxxxxx" required>
              @if(filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
                <button class="btn btn-outline-primary" type="button" id="btn-otp-edit">OTP</button>
              @endif
            </div>
            @error('phone')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
            <div id="otp-help-edit" class="small text-muted mt-1"></div>
          </div>

          {{-- Input OTP (muncul jika phone berubah) --}}
          @if(filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
            <div class="col-md-6" id="otp-wrapper-edit" style="display:none;">
              <label for="otp_code_edit" class="form-label">Kode OTP</label>
              <input type="text" id="otp_code_edit" name="otp_code" class="form-control"
                     placeholder="Masukkan kode OTP" value="{{ old('otp_code') }}">
              @error('otp_code')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>
          @endif

          {{-- Role --}}
          <div class="col-md-6">
            <label for="edit_role" class="form-label">Role</label>
            <select id="edit_role" name="role" class="form-select" required {{ ($user->role ?? '') === 'admin' ? 'disabled' : '' }}>
              @php($currentRole = old('role', $user->role ?? 'petugas'))
              @if($currentRole === 'admin')
                <option value="admin" selected>Admin</option>
              @else
                <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="petugas" {{ $currentRole === 'petugas' || $currentRole === 'staff' ? 'selected' : '' }}>Petugas IT</option>
                <option value="petugas_helpdesk" {{ $currentRole === 'petugas_helpdesk' ? 'selected' : '' }}>Petugas Helpdesk</option>
                <option value="manajemen" {{ $currentRole === 'manajemen' ? 'selected' : '' }}>Manajemen</option>
                <option value="kepala_ruangan" {{ $currentRole === 'kepala_ruangan' ? 'selected' : '' }}>Kepala Ruangan</option>
              @endif
            </select>
            @if(($user->role ?? '') === 'admin')
              <input type="hidden" name="role" value="admin">
            @endif
          </div>

          {{-- Ruangan (khusus kepala ruangan) --}}
          <div class="col-md-6" id="room-wrapper" style="display:none;">
            <label for="room_id" class="form-label">Ruangan</label>
            <select id="room_id" name="room_id" class="form-select">
              <option value="">Pilih Ruangan</option>
              @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ old('room_id', $user->room_id) == $room->id ? 'selected' : '' }}>
                  {{ $room->name }}
                </option>
              @endforeach
            </select>
            @error('room_id')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          {{-- Jabatan Manajemen (khusus role manajemen) --}}
          <div class="col-md-6" id="manajemen-wrapper" style="display:none;">
            <label for="manajemen_role" class="form-label">Jabatan Manajemen</label>
            <select id="manajemen_role" name="manajemen_role" class="form-select">
              <option value="">Pilih Jabatan</option>
              <option value="Direktur">DIREKTUR</option>
              <option value="Komite">KOMITE</option>
              <option value="Dewan Pengawas">DEWAN PENGAWAS</option>
              <option value="SPI">SPI</option>
              <option value="Wadir Administrasi Umum">WADIR ADMINITRASI UMUM</option>
              <option value="Kabag Keuangan dan Penyusun Program">KABAG KEUANGAN DAN PENYUSUN PROGRAM</option>
              <option value="Kasubbag Keuangan">KASUBBAG KEUANGAN</option>
              <option value="Kasubbag Perencanaan, Evaluasi dan Pelaporan">KASUBBAG PERENCANAAN, EVALUASI DAN PELAPORAN</option>
              <option value="Kabag Umum dan Kepegawaian">KABAG UMUM DAN KEPEGAWAIAN</option>
              <option value="Kasubbag Tata Usaha dan Kepegawaian">KASUBBAG TATA USAHA DAN KEPEGAWAIAN</option>
              <option value="Kasubbag Hubungan Masyarakat dan Pemasaran">KASUBBAG HUBUNGAN MASYARAKAT DAN PEMASARAN</option>
              <option value="Wadir Pelayanan">WADIR PELAYANAN</option>
              <option value="Kabid Pelayanan Penunjang">KABID PELAYANAN PENUNJANG</option>
              <option value="Kasie Kefarmasian dan Perbekalan Kesehatan">KASIE KEFARMASIAN DAN PERBEKALAN KESEHATAN</option>
              <option value="Kasie Penunjang, Penelitian, Pengembangan dan Upaya Rujukan">KASIE  PENUJANG, PENELITIAN, PENGEMBANGAN DAN UPAYA RUJUKAN</option>
              <option value="Kabid Pelayanan Keperawatan dan Kebidanan">KABID PELAYANAN KEPERAWATAN DAN KEBIDANAN</option>
              <option value="Kasie Asuhan Keperawatan dan Kebidanan">KASIE ASUHAN KEPERAWATAN DAN KEBIDANAN</option>
              <option value="Kasie Etika Profesi, Logistik Keperawatan dan Kebidanan">KASIE ETIKA PROFESI, LOGISTIK KEPERAWATAN DAN KEBIDANAN</option>
              <option value="Kabid Pelayanan Medis dan Penunjang Medis">KABID PELAYANAN MEDIS DAN PENUNJANG MEDIS</option>
              <option value="Kasie Pelayanan Medis dan Penunjang Medis Rawat Darurat, Intensif dan Bedah Sentral">KASIE PELAYANAN MEDIS DAN PENUNJANG MEDIS RAWAT DARURAT, INTENSIF DAN BEDAH SENTRAL</option>
              <option value="Kasie Pelayanan Medis dan Penunjang Medis Rawat Jalan dan Rawat Inap">KASIE PELAYANAN MEDIS DAN PENUNJANG MEDIS RAWAT JALAN DAN RAWAT INAP</option>
            </select>
          </div>

          {{-- (Email dihapus sesuai permintaan) --}}

          {{-- Password selalu terlihat --}}
          <div class="col-md-6">
            <label for="edit_password" class="form-label">Password (opsional)</label>
            <input type="text" id="edit_password" name="password" class="form-control" 
                   placeholder="Biarkan kosong jika tidak diubah" value="{{ old('password') }}">
          </div>

          {{-- Tombol simpan --}}
          <div class="col-12 text-end mt-3">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
              <i data-feather="save"></i>
              <span>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  if (typeof feather !== 'undefined') feather.replace();

  (function () {
    const roleSelect = document.getElementById('edit_role');
    const roomWrapper = document.getElementById('room-wrapper');
    const manajemenWrapper = document.getElementById('manajemen-wrapper');
    const toggleExtras = () => {
      if (roleSelect.value === 'kepala_ruangan') {
        roomWrapper.style.display = '';
      } else {
        roomWrapper.style.display = 'none';
      }
      if (roleSelect.value === 'manajemen') {
        manajemenWrapper.style.display = '';
      } else {
        manajemenWrapper.style.display = 'none';
      }
    };
    roleSelect.addEventListener('change', toggleExtras);
    toggleExtras();
  })();

  @if(filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN))
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
          body: JSON.stringify({ phone })
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
