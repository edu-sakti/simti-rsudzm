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
          <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
              <i data-feather="link"></i>
              <span>Generate Form</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item js-generate-invite" href="#" data-role="manajemen">Manajemen</a></li>
              <li><a class="dropdown-item js-generate-invite" href="#" data-role="kepala_ruangan">Kepala Ruangan</a></li>
              <li><a class="dropdown-item js-generate-invite" href="#" data-role="petugas_it">Petugas IT</a></li>
              <li><a class="dropdown-item js-generate-invite" href="#" data-role="petugas_helpdesk">Petugas Helpdesk</a></li>
            </ul>
          </div>
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
                <th>No HP</th>
                <th>Role</th>
                <th>Status</th>
                <th style="width:160px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($users as $index => $user)
                <tr>
                  <td>{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                  <td>{{ $user->name }}</td>
                  <td>{{ $user->username }}</td>
                  <td>{{ $user->phone ?? '-' }}</td>
                  @php
                    $roleLabelMap = [
                      'admin' => ['label' => 'ADMIN', 'class' => 'primary'],
                      'petugas_it' => ['label' => 'PETUGAS IT', 'class' => 'secondary'],
                      'petugas_helpdesk' => ['label' => 'PETUGAS HELPDESK', 'class' => 'secondary'],
                      'petugas' => ['label' => 'PETUGAS IT', 'class' => 'secondary'],
                      'manajemen' => ['label' => 'MANAJEMEN', 'class' => 'info'],
                      'kepala_ruangan' => ['label' => 'KEPALA RUANGAN', 'class' => 'warning'],
                    ];
                    $roleMeta = $roleLabelMap[$user->role] ?? ['label' => strtoupper($user->role), 'class' => 'secondary'];
                    if ($user->role === 'kepala_ruangan') {
                      $roomName = $user->room->name ?? 'Tanpa Ruangan';
                      $roleMeta['label'] = 'KARU ' . $roomName;
                    }
                  @endphp
                  <td>
                    <span class="badge bg-{{ $roleMeta['class'] }} text-uppercase">{{ $roleMeta['label'] }}</span>
                  </td>
                  <td>
                    @if($user->is_verified)
                      <span class="badge bg-success">Tervalidasi</span>
                    @else
                      <span class="badge bg-warning text-dark">Belum Valid</span>
                    @endif
                  </td>
                  <td>
                    @php($encoded = encrypt($user->username))
                    <div class="d-flex gap-2">
                      {{-- Tombol Edit --}}
                      @if($user->is_verified)
                        <a href="{{ route('users.edit', $encoded) }}" class="btn btn-sm btn-outline-secondary">
                          <i data-feather="edit-2"></i> Edit
                        </a>
                      @endif

                      @if(!$user->is_verified && auth()->check() && auth()->user()->role === 'admin')
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
                  <td colspan="6" class="text-center text-muted">Belum ada data pengguna.</td>
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
    const links = document.querySelectorAll('.js-generate-invite');
    if (!links.length) return;
    const copyLink = async (role) => {
      try {
        const res = await fetch("{{ route('users.invite') }}?role=" + encodeURIComponent(role), {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        const link = data.link || '';
        if (!link) throw new Error('Link kosong');
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(link);
        } else {
          const temp = document.createElement('textarea');
          temp.value = link;
          document.body.appendChild(temp);
          temp.select();
          document.execCommand('copy');
          temp.remove();
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Link berhasil disalin.' });
        }
      } catch (e) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menyalin link.' });
        }
      }
    };
    links.forEach((el) => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        const role = el.getAttribute('data-role');
        if (role) copyLink(role);
      });
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
