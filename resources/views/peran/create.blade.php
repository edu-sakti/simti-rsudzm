@extends('layouts.app')

@section('title', 'Tambah Peran Pengguna')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Tambah Peran Pengguna</h1>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Tambah Peran</h5>
        </div>
        <div class="card-body">
            @php($hasAvailableUsers = isset($users) && count($users) > 0)

            @unless($hasAvailableUsers)
                <div class="alert alert-info mb-3">
                    Semua pengguna sudah memiliki peran. Silakan ubah lewat menu <strong>Edit</strong>.
                </div>
            @endunless

            <form method="POST" action="{{ route('peran.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Pengguna</label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required @disabled(!$hasAvailableUsers)>
                            <option value="">{{ $hasAvailableUsers ? 'Pilih Pengguna' : 'Tidak ada pengguna yang bisa ditambahkan' }}</option>
                            @foreach(($users ?? []) as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                    {{ $user->name }} 
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Peran</label>
                        <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">Pilih Peran</option>
                            @foreach(($roles ?? []) as $role)
                                <option value="{{ $role->id }}" data-role-name="{{ strtolower($role->name) }}" @selected(old('role_id') == $role->id)>
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
                                <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" @disabled(!$hasAvailableUsers)>
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
    return value === 'kepala' || value === 'petugas';
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
