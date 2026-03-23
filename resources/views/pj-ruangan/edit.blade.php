@extends('layouts.app')

@section('title', 'Edit PJ Ruangan')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Edit PJ Ruangan</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('pj-ruangan.update', $token) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Petugas (Petugas IT)</label>
                    <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                        <option value="">Pilih Petugas</option>
                        @foreach ($petugas as $item)
                            <option value="{{ $item->id }}" {{ old('user_id', $entry->user_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ruangan</label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">Pilih Ruangan</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id', $entry->room_id) == $room->id ? 'selected' : '' }}>
                                {{ $room->room_id }} - {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Keterangan tambahan (opsional)">{{ old('description', $entry->description ?? '') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('rooms.pj') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
