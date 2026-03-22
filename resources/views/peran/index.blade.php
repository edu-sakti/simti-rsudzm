@extends('layouts.app')

@section('title', 'Peran Pengguna')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Peran Pengguna</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="card-title mb-0">Tabel Peran</h5>
            <a href="{{ route('peran.create') }}" class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Tambah Peran
            </a>
        </div>
        <div class="card-body">
            <form class="row mb-3" method="GET" action="{{ route('peran.index') }}">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari Peran" value="{{ $search ?? '' }}">
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Pengguna</th>
                            <th>Peran</th>
                            <th>Keterangan</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($peranPengguna ?? []) as $index => $item)
                            <tr>
                                <td>{{ (($peranPengguna->currentPage() - 1) * $peranPengguna->perPage()) + $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->user_name }}</div>
                                </td>
                                <td>{{ $item->role_name }}</td>
                                <td>{{ $item->keterangan ?: '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('peran.edit', peran_token_encode((string) $item->id)) }}" class="btn btn-sm btn-outline-secondary">
                                            <i data-feather="edit-2"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('peran.destroy', $item->id) }}" class="js-delete-peran-form" data-name="{{ $item->user_name }}">
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
                                <td colspan="5" class="text-center text-muted">Belum ada data peran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($peranPengguna) && method_exists($peranPengguna, 'links'))
                <div class="mt-3">
                    {{ $peranPengguna->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  if (typeof feather !== 'undefined') feather.replace();

  @if (session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: @json(session('success')),
      timer: 1800,
      showConfirmButton: false
    });
  @endif

  @if ($errors->any())
    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: @json($errors->first())
    });
  @endif

  (function () {
    const forms = document.querySelectorAll('form.js-delete-peran-form');
    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const name = form.getAttribute('data-name') || 'pengguna ini';
        if (typeof Swal === 'undefined') {
          if (confirm(`Hapus peran untuk ${name}?`)) form.submit();
          return;
        }
        Swal.fire({
          title: 'Hapus Peran?',
          text: `Peran untuk ${name} akan dihapus.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d'
        }).then((result) => {
          if (result.isConfirmed) form.submit();
        });
      });
    });
  })();
</script>
@endpush
