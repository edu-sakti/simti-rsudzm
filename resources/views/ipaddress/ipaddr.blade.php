@extends('layouts.app')

@section('title', 'IP Address List')

@section('content')
<h1 class="page-title mb-4">Daftar IP Address</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Tabel IP Address</h5>
        @permission('ip_address', 'create')
          <a href="{{ route('ipaddr.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
            <i data-feather="plus"></i>
            <span>Tambah IP</span>
          </a>
        @endpermission
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('ipaddr.index') }}" class="mb-3">
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
                <th>IP Address</th>
                <th>Subnet</th>
                <th class="d-flex align-items-center gap-2">
                  <span>Status</span>
                  <div class="dropdown">
                    <a class="text-secondary text-decoration-none fw-bold"
                       href="#"
                       role="button"
                       id="statusMenu"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       style="letter-spacing:1px;">
                      ⇅
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusMenu">
                      <li>
                        <a class="dropdown-item" href="{{ route('ipaddr.index', request()->except('status','page')) }}">Semua</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('ipaddr.index', array_merge(request()->except('page'), ['status' => 'available'])) }}">Available</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('ipaddr.index', array_merge(request()->except('page'), ['status' => 'used'])) }}">Used</a>
                      </li>
                    </ul>
                  </div>
                </th>
                <th>Deskripsi</th>
                <th style="width:160px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ipAddrs as $index => $ip)
                <tr>
                  <td>{{ ($ipAddrs->currentPage() - 1) * $ipAddrs->perPage() + $index + 1 }}</td>
                  <td>{{ $ip->ip_address }}</td>
                  <td>{{ $ip->subnet ?? '-' }}</td>
                  <td>
                    @php($status = strtolower($ip->status))
                    <span class="badge {{ $status === 'available' ? 'bg-primary' : 'bg-danger' }}">
                      {{ ucfirst($status) }}
                    </span>
                  </td>
                  <td>{{ $ip->description ?? '-' }}</td>
                  <td>
                    <div class="d-flex gap-2">
                      @php($encoded = encrypt($ip->id))
                      @permission('ip_address', 'update')
                        <a href="{{ route('ipaddr.edit', $encoded) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                      @endpermission
                      @permission('ip_address', 'delete')
                        <form action="{{ route('ipaddr.destroy', $encoded) }}" method="POST" class="d-inline js-delete-ip">
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
                  <td colspan="6" class="text-center text-muted">Belum ada data IP.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($ipAddrs instanceof \Illuminate\Contracts\Pagination\Paginator && $ipAddrs->hasPages())
          <div class="d-flex justify-content-end mt-0">
            {{ $ipAddrs->links('pagination::bootstrap-4') }}
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
    const errorMessage = @json(session('error'));
    if (success && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: success,
        timer: 1800,
        showConfirmButton: false
      });
    }
    if (errorMessage && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: errorMessage
      });
    }

    document.querySelectorAll('form.js-delete-ip').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') { form.submit(); return; }
        Swal.fire({
          title: 'Hapus IP Address?',
          text: 'Data yang dihapus tidak dapat dikembalikan.',
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
