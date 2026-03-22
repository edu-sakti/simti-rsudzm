@extends('layouts.app')

@section('title', 'Jabatan')

@push('styles')
<style>
  .jabatan-pagination .pagination {
    justify-content: center;
    margin-bottom: 0;
  }
  .jabatan-pagination .page-link {
    border-radius: 8px;
  }
  .jabatan-pagination .page-item + .page-item {
    margin-left: 4px;
  }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Jabatan</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="card-title mb-0">Daftar Jabatan</h5>
            <a href="{{ route('jabatan.create') }}" class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Tambah Jabatan
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Cari jabatan">
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Jabatan</th>
                            <th>Deskripsi</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jabatans ?? [] as $jabatan)
                            <tr>
                                <td>{{ $loop->iteration + (($jabatans->currentPage() - 1) * $jabatans->perPage()) }}</td>
                                <td>{{ $jabatan->name }}</td>
                                <td>{{ $jabatan->description ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('jabatan.edit', $jabatan->id) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                            <i data-feather="edit-2"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('jabatan.destroy', $jabatan->id) }}" class="jabatan-delete-form m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1">
                                                <i data-feather="trash-2"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada data jabatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(!empty($jabatans))
                <div class="mt-3 jabatan-pagination">
                    {{ $jabatans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.jabatan-delete-form').forEach((form) => {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      const submitForm = () => form.submit();
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Hapus jabatan?',
          text: 'Jabatan yang dihapus tidak bisa dikembalikan.',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal',
        }).then((result) => {
          if (result.isConfirmed) submitForm();
        });
      } else if (confirm('Hapus jabatan ini?')) {
        submitForm();
      }
    });
  });

  @if(session('success'))
    if (window.Swal) {
      Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')) });
    } else {
      alert(@json(session('success')));
    }
  @endif
</script>
@endpush
