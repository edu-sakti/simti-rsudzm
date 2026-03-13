@extends('layouts.app')

@section('title', 'Tambah ISP')

@section('content')
	<h1 class="page-title">Tambah ISP</h1>

	<div class="row">
		<div class="col-12">
			<div class="card">  
				<div class="card-header">
					<h5 class="card-title mb-0">Tambah Data ISP</h5>
				</div>
				<div class="card-body">
					@if($errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0 ps-3">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form method="POST" action="{{ route('isp.store') }}">
						@csrf

						<div class="mb-3">
							<label class="form-label fw-semibold">Nama ISP</label>
							<input type="text" name="nama_isp" class="form-control" value="{{ old('nama_isp') }}" required>
						</div>

						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label fw-semibold">Jenis Koneksi</label>
								<select name="jenis_koneksi" class="form-select" required>
									<option value="">Pilih Jenis</option>
									<option value="fiber" {{ old('jenis_koneksi') == 'fiber' ? 'selected' : '' }}>Fiber</option>
									<option value="radio" {{ old('jenis_koneksi') == 'radio' ? 'selected' : '' }}>Radio</option>
								</select>
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">Bandwidth</label>
								<input type="text" name="bandwidth" class="form-control" value="{{ old('bandwidth') }}" placeholder="contoh: 100 Mbps" required>
							</div>
						</div>

						<div class="row g-3 mt-0">
							<div class="col-md-6">
								<label class="form-label fw-semibold">IP Address</label>
								<input type="text" name="ip_address" class="form-control" value="{{ old('ip_address') }}" placeholder="contoh: 10.10.1.1" required>
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">Ruang Instalasi</label>
								<select name="ruang_installasi" class="form-select" required>
									<option value="">Pilih Ruangan</option>
									@foreach($rooms as $room)
										<option value="{{ $room->id }}" {{ old('ruang_installasi') == $room->id ? 'selected' : '' }}>
											{{ $room->room_id }} - {{ $room->name }}
										</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="row g-3 mt-0">
							<div class="col-md-6">
								<label class="form-label fw-semibold">PIC ISP</label>
								<input type="text" name="pic_isp" class="form-control" value="{{ old('pic_isp') }}" required>
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">No Telepon</label>
								<input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon') }}" required>
							</div>
						</div>

						<div class="row g-3 mt-0">
							<div class="col-md-6">
								<label class="form-label fw-semibold">Status</label>
								<select name="status" class="form-select" required>
									<option value="">Pilih Status</option>
									<option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
									<option value="backup" {{ old('status') == 'backup' ? 'selected' : '' }}>Backup</option>
								</select>
							</div>
						</div>

						<div class="mb-3 mt-3">
							<label class="form-label fw-semibold">Keterangan</label>
							<textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
						</div>

						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">Simpan</button>
							<a href="{{ route('isp.index') }}" class="btn btn-outline-secondary">Batal</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection
	