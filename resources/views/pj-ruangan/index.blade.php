@extends('layouts.app')

@section('title', 'PJ Ruangan')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">PJ Ruangan</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="card-title mb-0">Data PJ Ruangan</h5>
            <a href="{{ url('/pj-ruangan/tambah') }}" class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Tambah PJ
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('rooms.pj') }}">
                        <input type="text" name="q" class="form-control" placeholder="Cari PJ Ruangan" value="{{ $search ?? '' }}">
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Petugas</th>
                            <th>Ruangan</th>
                            <th>Keterangan</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $index => $item)
                                @php
                                    $token = pj_ruangan_token_encode((string) $item->id);
                                    $petugasLabel = trim(($item->user_name ?? '') . ($item->username ? " ({$item->username})" : ''));
                                    $roomLabel = trim((string) ($item->room_name ?? ''));
                                @endphp
                            <tr>
                                <td>{{ ($entries->currentPage() - 1) * $entries->perPage() + $index + 1 }}</td>
                                <td>{{ $petugasLabel ?: '-' }}</td>
                                <td>{{ $roomLabel ?: '-' }}</td>
                                <td>{{ $item->description ?: '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('pj-ruangan.edit', $token) }}" class="btn btn-outline-secondary btn-sm">
                                            <i data-feather="edit"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('pj-ruangan.destroy', $token) }}" class="js-delete-pj">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i data-feather="trash-2"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data PJ Ruangan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($entries))
                <div class="mt-3">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-delete-pj').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.Swal) {
                    return;
                }
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus PJ Ruangan?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
        @if (session('success'))
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success'))
                });
            }
        @elseif (session('error'))
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json(session('error'))
                });
            }
        @elseif ($errors->any())
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json($errors->first())
                });
            }
        @endif
    });
</script>
@endpush
