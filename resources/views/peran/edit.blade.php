@extends('layouts.app')

@section('title', 'Edit Peran Pengguna')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Edit Peran Pengguna</h1>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Edit Peran</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('peran.update', $peran->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Pengguna</label>
                        <input type="hidden" name="user_id" value="{{ old('user_id', $peran->user_id) }}">
                        <select id="user_id" class="form-select @error('user_id') is-invalid @enderror" disabled>
                            <option value="">Pilih Pengguna</option>
                            @foreach(($users ?? []) as $user)
                                <option value="{{ $user->id }}" @selected((string) old('user_id', $peran->user_id) === (string) $user->id)>
                                   {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pengguna tidak dapat diubah di form edit.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Peran</label>
                        <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">Pilih Peran</option>
                            @foreach(($roles ?? []) as $role)
                                <option value="{{ $role->id }}" data-role-name="{{ strtolower($role->name) }}" @selected((string) old('role_id', $peran->role_id) === (string) $role->id)>
                                   {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 d-none" id="roomField">
                        <label for="room_id" class="form-label">Ruangan</label>
                        <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror">
                            <option value="">Pilih Ruangan</option>
                            @foreach(($rooms ?? []) as $room)
                                <option value="{{ $room->id }}" @selected((string) old('room_id', $selectedRoomId ?? '') === (string) $room->id)>
                                    {{ $room->name }} ({{ $room->room_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Simpan
                    </button>
                    <a href="{{ route('peran.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  if (typeof feather !== 'undefined') feather.replace();

  const roleSelect = document.getElementById('role_id');
  const roomField = document.getElementById('roomField');
  const roomSelect = document.getElementById('room_id');

  function shouldShowRoom(roleName) {
    if (!roleName) return false;
    const value = roleName.toLowerCase().trim();
    return value === 'kepala' || value === 'kepala ruang' || value === 'petugas';
  }

  function toggleRoomField() {
    const selected = roleSelect?.selectedOptions?.[0];
    const roleName = selected?.dataset?.roleName || '';
    const show = shouldShowRoom(roleName);
    if (roomField) roomField.classList.toggle('d-none', !show);
    if (!show && roomSelect) roomSelect.value = '';
  }

  if (roleSelect) {
    roleSelect.addEventListener('change', toggleRoomField);
    toggleRoomField();
  }

  @if ($errors->any())
    Swal.fire({
      icon: 'error',
      title: 'Gagal menyimpan',
      text: @json($errors->first())
    });
  @endif
</script>
@endpush
