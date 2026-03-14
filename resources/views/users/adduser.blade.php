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
              <select id="role" name="role" class="form-select" required {{ isset($invite_role) ? 'disabled' : '' }}>
                @if (!isset($invite_code))
                  <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                @endif
                @if (!isset($invite_code))
                  <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas IT</option>
                  <option value="petugas_helpdesk" {{ old('role') === 'petugas_helpdesk' ? 'selected' : '' }}>Petugas Helpdesk</option>
                  <option value="manajemen" {{ old('role') === 'manajemen' ? 'selected' : '' }}>Manajemen</option>
                  <option value="kepala_ruangan" {{ old('role') === 'kepala_ruangan' ? 'selected' : '' }}>Kepala Ruangan</option>
                @else
                  <option value="manajemen" {{ old('role', $invite_role) === 'manajemen' ? 'selected' : '' }}>Manajemen</option>
                  <option value="kepala_ruangan" {{ old('role', $invite_role) === 'kepala_ruangan' ? 'selected' : '' }}>Kepala Ruangan</option>
                  <option value="petugas" {{ old('role', $invite_role) === 'petugas' ? 'selected' : '' }}>Petugas IT</option>
                  <option value="petugas_helpdesk" {{ old('role', $invite_role) === 'petugas_helpdesk' ? 'selected' : '' }}>Petugas Helpdesk</option>
                @endif
              </select>
              @if(isset($invite_role))
                <input type="hidden" name="role" value="{{ $invite_role }}">
              @endif
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
