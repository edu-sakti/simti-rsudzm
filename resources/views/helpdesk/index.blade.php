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
						@if(\App\Support\Permission::can(auth()->user(), 'helpdesk', 'create'))
							@php
								$roleKey = auth()->user()->role_key ?? '';
							@endphp
							@if(in_array($roleKey, ['petugas', 'kepala_ruang', 'kepala', 'petugas_it'], true))
								<a href="{{ route('helpdesk.request') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
									<i data-feather="send"></i>
									<span>Request</span>
								</a>
							@else
								<a href="{{ url('/helpdesk/tambah-ticket') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
									<i data-feather="plus"></i>
									<span>Tambah</span>
								</a>
								<a href="{{ route('helpdesk.request') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
									<i data-feather="send"></i>
									<span>Request</span>
								</a>
							@endif
						@endif
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
									<th>Perangkat</th>
									<th>Kendala</th>
									<th>Prioritas</th>
									<th>Petugas</th>
									<th>Status</th>
									<th>Keterangan</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								@forelse(($tickets ?? []) as $ticket)
									<tr>
										<td>{{ $ticket->no_ticket }}</td>
										<td>{{ \Carbon\Carbon::parse($ticket->tanggal)->format('d/m/Y') }}</td>
										<td>{{ $ticket->pelapor }}</td>
										<td>{{ $ticket->room_name ?? '-' }}</td>
										<td>{{ ucfirst($ticket->kategori) }}</td>
										<td>
											@if($ticket->device_name)
												{{ $ticket->device_name }} ({{ $ticket->device_id }})
											@else
												-
											@endif
										</td>
										<td>{{ $ticket->kendala }}</td>
										<td>{{ ucfirst($ticket->prioritas) }}</td>
										<td>{{ $ticket->petugas_name ?? '-' }}</td>
										<td>
											@php
												$statusKey = $ticket->status ?? 'open';
												$statusLabel = 'Open';
												$statusClass = 'bg-warning text-dark';

												if (in_array($statusKey, ['assigned', 'in_progress', 'progress'], true)) {
													$statusLabel = 'Progress';
													$statusClass = 'bg-primary';
												} elseif (in_array($statusKey, ['resolved', 'done'], true)) {
													$statusLabel = 'Done';
													$statusClass = 'bg-success';
												} elseif ($statusKey === 'closed') {
													$statusLabel = 'Closed';
													$statusClass = 'bg-secondary';
												}
											@endphp
											<span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
										</td>
										<td>{{ $ticket->keterangan ?? '-' }}</td>
										<td>
											<div class="d-flex flex-wrap gap-2">
												<a href="{{ route('helpdesk.show.internal', $ticket->no_ticket) }}" class="btn btn-sm btn-outline-info d-inline-flex align-items-center gap-1">
													<i data-feather="info"></i> Detail
												</a>
												@if(\App\Support\Permission::can(auth()->user(), 'helpdesk', 'update'))
													<a href="#" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
														<i data-feather="edit-2"></i> Edit
													</a>
													@php
														$userRole = auth()->user()->role_key ?? '';
													@endphp
													@if((auth()->user()->is_admin ?? false) || in_array($userRole, ['petugas_it'], true))
														<a href="#" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
															<i data-feather="play"></i> Proses
														</a>
													@endif
													<a href="#" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1">
														<i data-feather="check-circle"></i> Selesai
													</a>
												@endif
												@if(\App\Support\Permission::can(auth()->user(), 'helpdesk', 'delete'))
													<form method="POST" action="{{ route('helpdesk.destroy', $ticket->id) }}" class="d-inline">
														@csrf
														@method('DELETE')
														<button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 js-delete-ticket">
															<i data-feather="trash-2"></i> Hapus
														</button>
													</form>
												@endif
											</div>
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="12" class="text-center text-muted">Belum ada data tiket.</td>
									</tr>
								@endforelse
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

	document.querySelectorAll('.js-delete-ticket').forEach((btn) => {
		btn.addEventListener('click', (event) => {
			event.preventDefault();
			if (typeof Swal === 'undefined') {
				btn.closest('form')?.submit();
				return;
			}

			Swal.fire({
				title: 'Hapus tiket?',
				text: 'Data tiket akan dihapus permanen.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Hapus',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					btn.closest('form')?.submit();
				}
			});
		});
	});
</script>
@if(session('success'))
<script>
	if (typeof Swal !== 'undefined') {
		Swal.fire({
			icon: 'success',
			title: 'Berhasil',
			text: @json(session('success')),
			confirmButtonText: 'OK'
		});
	}
</script>
@endif
@endpush
