@extends('layouts.app')

@section('title', 'Tambah Ticket')

@section('content')
	<h1 class="page-title">Tambah Ticket</h1>

	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Form Tambah Ticket</h5>
					<a href="{{ url('/helpdesk') }}" class="btn btn-sm btn-outline-secondary">
						<i data-feather="arrow-left"></i> Kembali
					</a>
				</div>
				<div class="card-body">
					<form method="POST" action="{{ route('helpdesk.store') }}" class="row g-3" id="helpdeskForm">
						@csrf
						<div class="col-md-6">
							<label class="form-label">Tanggal</label>
							<input type="date" name="tanggal" class="form-control" value="{{ now()->toDateString() }}">
						</div>
						<div class="col-md-6">
							<label class="form-label">Pelapor</label>
							<input type="text" name="pelapor" class="form-control" placeholder="Nama pelapor">
						</div>
						<div class="col-md-6">
							<label class="form-label">Ruangan</label>
							<select name="room_id" id="roomSelect" class="form-select">
								<option value="">Pilih Ruangan</option>
								@foreach(($rooms ?? []) as $room)
									<option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Kategori</label>
							<select name="kategori" id="kategoriSelect" class="form-select">
								<option value="">Pilih Kategori</option>
								<option value="hardware">Hardware</option>
								<option value="software">Software</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Perangkat</label>
							<select name="device_id" id="deviceSelect" class="form-select" data-selected="{{ old('device_id') ?? '' }}" disabled>
								<option value="">Pilih Perangkat</option>
							</select>
							<small class="text-muted d-block mt-1" id="deviceHelp">Pilih kategori Hardware dan ruangan terlebih dahulu.</small>
						</div>
						<div class="col-md-6">
							<label class="form-label">Kendala</label>
							<textarea name="kendala" class="form-control" rows="3" placeholder="Deskripsi kendala"></textarea>
						</div>
						<div class="col-md-6">
							<label class="form-label">Prioritas</label>
							<select name="prioritas" class="form-select">
								<option value="">Pilih Prioritas</option>
								<option value="rendah">Rendah</option>
								<option value="sedang">Sedang</option>
								<option value="tinggi">Tinggi</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Petugas</label>
							<select name="petugas_id" class="form-select">
								<option value="">Pilih Petugas IT</option>
								@foreach(($petugas ?? []) as $user)
									<option value="{{ $user->id }}">{{ $user->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-6"></div>
						<div class="col-md-6">
							<label class="form-label">Keterangan</label>
							<input type="text" name="keterangan" class="form-control" placeholder="Catatan tambahan">
						</div>
						<div class="col-12 text-end mt-3">
							<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
								<i data-feather="save"></i>
								<span>Buat Ticket</span>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script>
		const form = document.getElementById('helpdeskForm');
		const roomSelect = document.getElementById('roomSelect');
		const deviceSelect = document.getElementById('deviceSelect');
		const kategoriSelect = document.getElementById('kategoriSelect');

		if (form) {
			form.addEventListener('submit', (event) => {
				const requiredSelectors = [
					'input[name="tanggal"]',
					'input[name="pelapor"]',
					'select[name="room_id"]',
					'select[name="kategori"]',
					'textarea[name="kendala"]',
					'select[name="prioritas"]',
				];
				const missing = requiredSelectors.some((selector) => {
					const el = form.querySelector(selector);
					if (!el) return false;
					return !el.value || el.value.trim() === '';
				});

				const kategori = form.querySelector('select[name="kategori"]')?.value;
				const deviceId = form.querySelector('select[name="device_id"]')?.value;
				if (kategori === 'hardware' && !deviceId) {
					event.preventDefault();
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Perangkat wajib dipilih untuk kategori Hardware.',
							confirmButtonText: 'OK'
						});
					}
					return;
				}

				if (missing) {
					event.preventDefault();
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Mohon lengkapi semua kolom wajib.',
							confirmButtonText: 'OK'
						});
					}
				}
			});
		}

		document.addEventListener('DOMContentLoaded', () => {
			async function loadDevices() {
				const roomId = roomSelect.value;
				const kategori = kategoriSelect.value;
				deviceSelect.innerHTML = '<option value="">Pilih Perangkat</option>';
				deviceSelect.disabled = true;
				const help = document.getElementById('deviceHelp');
				if (help) help.textContent = 'Pilih kategori Hardware dan ruangan terlebih dahulu.';

				if (!roomId || kategori !== 'hardware') {
					return;
				}

				try {
					const res = await fetch(`/helpdesk/devices?room_id=${encodeURIComponent(roomId)}`);
					const data = await res.json();
					if (Array.isArray(data)) {
						const selected = deviceSelect.dataset.selected;
						data.forEach((device) => {
							const option = document.createElement('option');
							option.value = device.id;
							option.textContent = device.device_name || `Perangkat #${device.id}`;
							if (selected && String(selected) === String(device.id)) {
								option.selected = true;
							}
							deviceSelect.appendChild(option);
						});
					}
					deviceSelect.disabled = false;
					if (help) help.textContent = data.length ? 'Pilih perangkat yang sesuai.' : 'Tidak ada perangkat di ruangan ini.';
				} catch (e) {
					deviceSelect.disabled = true;
					if (help) help.textContent = 'Gagal memuat perangkat.';
				}
			}

			if (roomSelect) {
				roomSelect.addEventListener('change', loadDevices);
			}
			if (kategoriSelect) {
				kategoriSelect.addEventListener('change', () => {
					if (kategoriSelect.value !== 'hardware') {
						deviceSelect.value = '';
						deviceSelect.dataset.selected = '';
					}
					loadDevices();
				});
			}

			if (roomSelect.value || kategoriSelect.value) {
				loadDevices();
			}
		});
	</script>
@endsection

@push('scripts')
<script>
	if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
