@extends('layouts.app')

@section('title', 'PJ Ruangan')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">PJ Ruangan</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h5 class="card-title mb-0">Data PJ Ruangan</h5>
            <button class="btn btn-primary btn-sm">
                <i data-feather="plus"></i> Tambah PJ
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Cari PJ Ruangan">
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
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data PJ Ruangan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
