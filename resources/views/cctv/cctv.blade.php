@extends('layouts.app')

@section('title', 'CCTV')

@section('content')
<h1 class="page-title mb-4">Daftar CCTV</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Tabel CCTV</h5>
        @permission('cctv', 'create')
          <a href="{{ url('/cctv/tambah') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
            <i data-feather="plus"></i>
            <span>Tambah CCTV</span>
          </a>
        @endpermission
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('cctv.index') }}" class="mb-3">
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
                <th style="width:60px">No</th>
                <th>Lokasi DVR</th>
                <th class="d-flex align-items-center gap-2">
                  <span>Status</span>
                  <div class="dropdown">
                    <a class="text-secondary text-decoration-none fw-bold" href="#" role="button" id="statusMenu" data-bs-toggle="dropdown" aria-expanded="false" style="letter-spacing:1px;">
                      ⇅
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusMenu">
                      <li><a class="dropdown-item" href="{{ route('cctv.index') }}">Semua</a></li>
                      <li><a class="dropdown-item" href="{{ route('cctv.index', array_merge(request()->except('page'), ['status' => 'aktif'])) }}">Aktif</a></li>
                      <li><a class="dropdown-item" href="{{ route('cctv.index', array_merge(request()->except('page'), ['status' => 'non_aktif'])) }}">Non Aktif</a></li>
                    </ul>
                  </div>
                </th>
                <th>Keterangan</th>
                <th style="width:160px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($cctvs as $index => $cctv)
                <tr>
                  <td>{{ ($cctvs->currentPage() - 1) * $cctvs->perPage() + $index + 1 }}</td>
                  <td>{{ $cctv->room->name ?? $cctv->room_id }}</td>
                  <td>{{ ucfirst(str_replace('_', ' ', $cctv->status)) }}</td>
                  <td>{{ $cctv->keterangan ?? '-' }}</td>
                  <td>
                    <div class="d-flex gap-2">
                      <a class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1"
                         href="http://{{ $cctv->ip }}"
                         target="_blank" rel="noopener">
                        <i data-feather="video"></i>
                        <span>Live</span>
                      </a>
                      @permission('cctv', 'update')
                        <a href="{{ route('cctv.edit', encrypt($cctv->id)) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                      @endpermission
                      @permission('cctv', 'delete')
                        <form action="{{ route('cctv.destroy', encrypt($cctv->id)) }}" method="POST" class="d-inline js-delete-cctv">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                      @endpermission
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">Belum ada data CCTV.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          @if($cctvs->hasPages())
            <div class="d-flex justify-content-end mt-0">
              {{ $cctvs->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
          @endif
        </div>
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

    document.querySelectorAll('form.js-delete-cctv').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') { form.submit(); return; }
        Swal.fire({
          title: 'Hapus CCTV?',
          text: 'Apakah Kamu yakin menghapus data CCTV ini?',
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

