@extends('layouts.app')

@section('title', 'Daftar Pengguna')

@section('content')
<h1 class="page-title mb-4">Daftar Pengguna</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Tabel Pengguna</h5>
        {{-- Tombol Tambah Pengguna --}}
        <div class="d-flex gap-2">
          <a href="{{ url('/pengguna/tambah') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
            <i data-feather="user-plus"></i>
            <span>Tambah</span>
          </a>
          <button type="button" id="btn-generate-form" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
            <i data-feather="link"></i>
            <span>Generate Form</span>
          </button>
        </div>
      </div>

      <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="mb-1">
          <div class="col-md-4 px-0">
            <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control search-rounded" placeholder="Cari Data">
          </div>
        </form>

        {{-- Tabel data --}}
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th style="width:60px">No</th>
                <th>Nama</th>
                <th>Username</th>
                <th style="width:260px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($users as $index => $user)
                <tr>
                  <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                  <td>{{ $user->name }}</td>
                  <td>{{ $user->username }}</td>
                  <td>
                    @php($encoded = encrypt($user->username))
                    <div class="d-flex gap-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-info js-detail-btn"
                        data-name="{{ $user->name }}"
                        data-username="{{ $user->username }}"
                        data-email="{{ $user->email ?? '-' }}"
                        data-phone="{{ $user->phone ?? '-' }}"
                        data-status="{{ $user->is_verified ? 'Tervalidasi' : 'Belum Valid' }}"
                      >
                        <i data-feather="eye"></i> Detail
                      </button>

                      {{-- Tombol Edit --}}
                      @if($user->is_verified)
                        <a href="{{ route('users.edit', $encoded) }}" class="btn btn-sm btn-outline-secondary">
                          <i data-feather="edit-2"></i> Edit
                        </a>
                      @endif

                      @if(!$user->is_verified && auth()->check() && (auth()->user()->is_admin ?? false))
                        <form method="POST" action="{{ route('users.verify', $encoded) }}" class="js-verify-form" data-username="{{ $user->username }}">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-outline-primary">Validasi</button>
                        </form>
                      @endif

                      {{-- Tombol Hapus --}}
                      <form method="POST" action="{{ route('users.destroy', $encoded) }}" class="js-delete-form" data-username="{{ $user->username }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i data-feather="trash-2"></i> Hapus
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted">Belum ada data pengguna.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
          {{ $users->withQueryString()->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

{{-- SweetAlert2 for delete confirmation and Feather icons --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  if (typeof feather !== 'undefined') feather.replace();

  // Tampilkan SweetAlert sukses jika ada flash 'success'
  @if (session('success'))
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: {!! json_encode(session('success')) !!},
        timer: 2000,
        showConfirmButton: false
      });
    }
  @endif

  (function () {
    const buttons = document.querySelectorAll('.js-detail-btn');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const name = btn.getAttribute('data-name') || '-';
        const username = btn.getAttribute('data-username') || '-';
        const email = btn.getAttribute('data-email') || '-';
        const phone = btn.getAttribute('data-phone') || '-';
        const status = btn.getAttribute('data-status') || '-';

        if (typeof Swal === 'undefined') {
          alert(`Nama: ${name}\nUsername: ${username}\nEmail: ${email}\nNo HP: ${phone}\nStatus: ${status}`);
          return;
        }

        Swal.fire({
          title: 'Detail Pengguna',
          icon: 'info',
          html: `
            <div style="text-align:left; max-width:420px; margin:0 auto;">
              <div style="display:grid; grid-template-columns:120px 1fr; row-gap:10px; column-gap:10px; font-size:1rem;">
                <div style="font-weight:600; color:#495057;">Nama</div>
                <div style="color:#212529;">${name}</div>

                <div style="font-weight:600; color:#495057;">Username</div>
                <div style="color:#212529;">${username}</div>

                <div style="font-weight:600; color:#495057;">Email</div>
                <div style="color:#212529; word-break:break-word;">${email}</div>

                <div style="font-weight:600; color:#495057;">No HP</div>
                <div style="color:#212529;">${phone}</div>

                <div style="font-weight:600; color:#495057;">Status</div>
                <div>
                  <span style="
                    display:inline-block;
                    padding:2px 10px;
                    border-radius:999px;
                    font-size:.85rem;
                    font-weight:600;
                    color:#fff;
                    background:${status === 'Tervalidasi' ? '#198754' : '#ffc107'};
                  ">
                    ${status}
                  </span>
                </div>
              </div>
            </div>
          `,
          confirmButtonText: 'Tutup'
        });
      });
    });
  })();

  (function () {
    const forms = document.querySelectorAll('form.js-delete-form');
    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        // Intercept default submit to show SweetAlert confirm
        e.preventDefault();
        const username = form.getAttribute('data-username') || '';
        if (typeof Swal === 'undefined') {
          // Fallback to native confirm if SweetAlert not loaded
          if (confirm(`Apakah Kamu Yakin Menghapus User ${username} ?`)) {
            form.submit();
          }
          return;
        }

        Swal.fire({
          title: 'Hapus Pengguna?',
          text: `Apakah Kamu Yakin Menghapus User ${username} ?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33', // merah untuk hapus
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  })();

  (function () {
    const btnGenerate = document.getElementById('btn-generate-form');
    if (!btnGenerate) return;

    btnGenerate.addEventListener('click', async function () {
      if (typeof Swal === 'undefined') {
        alert('SweetAlert tidak tersedia.');
        return;
      }

      const result = await Swal.fire({
        title: 'Kirim Form Pendaftaran',
        input: 'text',
        inputLabel: 'No HP Tujuan',
        inputPlaceholder: 'Contoh: 62812xxxxxxx / 0812xxxxxxx',
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: async (phone) => {
          const normalized = String(phone || '').trim();
          if (!normalized) {
            Swal.showValidationMessage('No HP wajib diisi.');
            return false;
          }

          try {
            const res = await fetch("{{ route('users.invite.send') }}", {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
              },
              body: JSON.stringify({ phone: normalized })
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.ok) {
              throw new Error(data.message || 'Gagal mengirim link form.');
            }

            return data;
          } catch (error) {
            Swal.showValidationMessage(error.message || 'Terjadi kesalahan.');
            return false;
          }
        },
        allowOutsideClick: () => !Swal.isLoading()
      });

      if (result.isConfirmed && result.value) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: result.value.message || 'Link form berhasil dikirim.'
        });
      }
    });
  })();

  (function () {
    const forms = document.querySelectorAll('form.js-verify-form');
    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const username = form.getAttribute('data-username') || '';
        if (typeof Swal === 'undefined') {
          form.submit();
          return;
        }
        Swal.fire({
          title: 'Validasi Pengguna?',
          text: `Apakah kamu yakin memvalidasi user ${username}?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#0d6efd',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Validasi',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  })();
</script>
@endsection
