@extends('layouts.app')

@section('title', 'Edit Peran')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Edit Peran</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('roles.update', $role->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Peran</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="100" required value="{{ old('name', $role->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
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

@push('scripts')
<script>
  @if ($errors->has('name'))
    if (window.Swal) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: {!! json_encode($errors->first('name')) !!}
      });
    }
  @endif
</script>
@endpush
