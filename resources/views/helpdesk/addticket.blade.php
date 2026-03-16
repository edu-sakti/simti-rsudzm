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
							<select name="room_id" class="form-select">
								<option value="">Pilih Ruangan</option>
								@foreach(($rooms ?? []) as $room)
									<option value="{{ $room->id }}">{{ $room->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Kategori</label>
							<select name="kategori" id="kategoriSelect" class="form-select" onchange="toggleKategoriHardware(this.value)">
								<option value="">Pilih Kategori</option>
								<option value="hardware">Hardware</option>
								<option value="software">Software</option>
							</select>
						</div>
						<div class="col-md-6 d-none" id="hardwareWrap">
							<label class="form-label">Jenis Hardware</label>
							<select name="jenis_hardware" id="hardwareSelect" class="form-select">
								<option value="">Pilih Jenis</option>
								<option value="komputer">Komputer</option>
								<option value="jaringan">Jaringan</option>
								<option value="printer">Printer</option>
								<option value="telepon">Telepon</option>
							</select>
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
								<span>Simpan Ticket</span>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script>
		const form = document.getElementById('helpdeskForm');

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
				const jenisHardware = form.querySelector('select[name="jenis_hardware"]')?.value;

				if (kategori === 'hardware' && !jenisHardware) {
					event.preventDefault();
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'warning',
							title: 'Perhatian',
							text: 'Jenis hardware harus dipilih.',
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

		function toggleKategoriHardware(value) {
			const hardwareWrap = document.getElementById('hardwareWrap');
			const hardwareSelect = document.getElementById('hardwareSelect');
			if (!hardwareWrap) return;

			const isHardware = value === 'hardware';
			hardwareWrap.classList.toggle('d-none', !isHardware);
			if (!isHardware && hardwareSelect) {
				hardwareSelect.value = '';
			}
		}

		document.addEventListener('DOMContentLoaded', () => {
			const kategoriSelect = document.getElementById('kategoriSelect');
			if (kategoriSelect) toggleKategoriHardware(kategoriSelect.value);
		});
	</script>
@endsection

@push('scripts')
<script>
	if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
