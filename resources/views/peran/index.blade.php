@extends('layouts.app')

@section('title', 'Peran Pengguna')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Peran Pengguna</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="card-title mb-0">Tabel Peran</h5>
            <button class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Tambah Peran
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Cari Peran">
                </div>
            </div>
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
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data peran.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
