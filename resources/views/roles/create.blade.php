@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Tambah Role</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Role</label>
                    <input type="text" name="name" class="form-control" maxlength="100" required value="{{ old('name') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
