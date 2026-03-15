@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
	<h1 class="page-title">Helpdesk</h1>

	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Data Helpdesk</h5>
					<div class="d-flex gap-2">
						<a href="#" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
							<i data-feather="plus"></i>
							<span>Tambah</span>
						</a>
						<a href="#" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
							<i data-feather="send"></i>
							<span>Request</span>
						</a>
					</div>
				</div>
				<div class="card-body">
					<div class="mb-3 col-md-4 px-0">
						<input type="text" class="form-control search-rounded" placeholder="Cari Ticket">
					</div>
					<div class="table-responsive">
						<table class="table table-striped table-hover align-middle">
							<thead>
								<tr>
									<th>No Ticket</th>
									<th>Tanggal</th>
									<th>Pelapor</th>
									<th>Ruangan</th>
									<th>Kategori</th>
									<th>Kendala</th>
									<th>Prioritas</th>
									<th>Petugas</th>
									<th>Status</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="9" class="text-center text-muted">Belum ada data tiket.</td>
									<td>
										<div class="d-flex flex-wrap gap-2">
											<button class="btn btn-sm btn-outline-info">
												<i data-feather="info"></i> Detail
											</button>
											<button class="btn btn-sm btn-outline-secondary">
												<i data-feather="edit-2"></i> Edit
											</button>
											<button class="btn btn-sm btn-outline-primary">
												<i data-feather="play"></i> Proses
											</button>
											<button class="btn btn-sm btn-outline-success">
												<i data-feather="check-circle"></i> Selesai
											</button>
											<button class="btn btn-sm btn-outline-danger">
												<i data-feather="trash-2"></i> Hapus
											</button>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
<script>
	if (typeof feather !== 'undefined') feather.replace();
</script>
@endpush
