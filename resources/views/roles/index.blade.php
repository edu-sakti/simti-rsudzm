@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Roles</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="card-title mb-0">Daftar Roles</h5>
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Tambah Role
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Cari role">
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Role</th>
                            <th>Deskripsi</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles ?? [] as $role)
                            <tr>
                                <td>{{ $loop->iteration + (($roles->currentPage() - 1) * $roles->perPage()) }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->description ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-outline-secondary btn-sm">
                                        <i data-feather="edit-2"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada data role.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(!empty($roles))
                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
