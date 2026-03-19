@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
	<h1 class="page-title">Dashboard</h1>

	<div class="row g-3 mb-4">
		<div class="col-12 col-sm-6 col-xl-3">
			<div class="card summary-card shadow-sm">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<div class="text-muted small">Total Tiket</div>
						<div class="h3 mb-0">{{ $totalTickets ?? 0 }}</div>
					</div>
					<div class="summary-icon bg-primary-subtle text-primary">
						<i data-feather="layers"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-sm-6 col-xl-3">
			<div class="card summary-card shadow-sm">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<div class="text-muted small">Tiket Pending</div>
						<div class="h3 mb-0">{{ $pendingTickets ?? 0 }}</div>
					</div>
					<div class="summary-icon bg-warning-subtle text-warning">
						<i data-feather="clock"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-sm-6 col-xl-3">
			<div class="card summary-card shadow-sm">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<div class="text-muted small">Tiket Diproses</div>
						<div class="h3 mb-0">{{ $progressTickets ?? 0 }}</div>
					</div>
					<div class="summary-icon bg-info-subtle text-info">
						<i data-feather="activity"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-sm-6 col-xl-3">
			<div class="card summary-card shadow-sm">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<div class="text-muted small">Tiket Selesai</div>
						<div class="h3 mb-0">{{ $doneTickets ?? 0 }}</div>
					</div>
					<div class="summary-icon bg-success-subtle text-success">
						<i data-feather="check-circle"></i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">Ringkasan</h5>
				</div>
				<div class="card-body">
					<p>Konten bisa kamu tambahkan di sini.</p>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
<style>
	.summary-card .summary-icon {
		width: 44px;
		height: 44px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 12px;
	}
</style>
@endpush
