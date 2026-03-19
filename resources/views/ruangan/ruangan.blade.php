@extends('layouts.app')

@section('title', 'Daftar Ruangan')

@section('content')
<h1 class="page-title mb-4">Daftar Ruangan</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Tabel Ruangan</h5>
        @permission('ruangan', 'create')
          <a href="{{ route('rooms.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
            <i data-feather="plus"></i>
            <span>Tambah Ruangan</span>
          </a>
        @endpermission
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('rooms.index') }}" class="mb-3">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <input type="text" name="q" value="{{ request('q') }}" class="form-control search-rounded" placeholder="Cari Data">
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th class="col-no">No</th>
                <th class="col-code">Kode Ruangan</th>
                <th class="d-flex align-items-center gap-2">
                  <span>Kategori</span>
                  <div class="dropdown">
                    <a class="text-secondary text-decoration-none fw-bold"
                       href="#"
                       role="button"
                       id="kategoriMenu"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       style="letter-spacing:1px;">
                      ⇅
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="kategoriMenu">
                      <li>
                        <a class="dropdown-item" href="{{ route('rooms.index', request()->except('kategori','page')) }}">
                          Semua
                        </a>
                      </li>
                      @foreach($categories ?? [] as $cat)
                        <li>
                          <a class="dropdown-item"
                             href="{{ route('rooms.index', array_merge(request()->except('page'), ['kategori' => $cat])) }}">
                            {{ $cat }}
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </th>

                <th class="col-nama">Nama Ruangan</th>
                <th class="col-aksi">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php($start = ($rooms->currentPage() - 1) * $rooms->perPage())
              @forelse($rooms as $index => $room)
                <tr>
                  <td class="col-no">{{ $start + $index + 1 }}</td>
                  <td class="col-code text-nowrap">{{ $room->room_id }}</td>
                  <td class="col-kategori">{{ $room->kategori }}</td>
                  <td class="col-nama">{{ $room->name }}</td>
                  <td class="col-aksi">
                    @php($encoded = encrypt($room->room_id))
                    <div class="d-flex gap-2">
                      @permission('ruangan', 'update')
                        <a href="{{ route('rooms.edit', $encoded) }}" class="btn btn-sm btn-outline-secondary">
                          <i data-feather="edit-2"></i>
                          Edit
                        </a>
                      @endpermission
                      @permission('ruangan', 'delete')
                        <form method="POST" action="{{ route('rooms.destroy', $encoded) }}" class="js-delete-room">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i data-feather="trash-2"></i>
                            Hapus
                          </button>
                        </form>
                      @endpermission
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">Belum ada data ruangan.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($rooms->hasPages())
          <div class="d-flex justify-content-end mt-0">
            {{ $rooms->withQueryString()->links('pagination::bootstrap-4') }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  if (typeof feather !== 'undefined') feather.replace();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  (function () {
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
    const errorMessage = @json(session('error'));
    if (errorMessage && typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Gagal', text: errorMessage });
    }

    document.querySelectorAll('form.js-delete-room').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') { form.submit(); return; }
        Swal.fire({
          title: 'Hapus Ruangan?',
          text: 'Apakah Kamu Yakin Menghapus Ruangan ini?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then((result) => { if (result.isConfirmed) form.submit(); });
      });
    });

  })();
</script>
@endsection



