@extends('layouts.app')

@section('title', 'Detail Ticket')

@section('content')
<h1 class="page-title mb-4">Detail Ticket</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Informasi Ticket</h5>
        <a href="{{ route('helpdesk.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
          <i data-feather="arrow-left"></i>
          <span>Kembali</span>
        </a>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-muted">No Ticket</label>
            <div class="fw-semibold">{{ $ticket->no_ticket }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Tanggal</label>
            <div class="fw-semibold">{{ \Carbon\Carbon::parse($ticket->tanggal)->format('d/m/Y') }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Pelapor</label>
            <div class="fw-semibold">{{ $ticket->pelapor }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Ruangan</label>
            <div class="fw-semibold">{{ $ticket->room_name ?? '-' }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Kategori</label>
            <div class="fw-semibold">{{ ucfirst($ticket->kategori) }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Perangkat</label>
            <div class="fw-semibold">
              @if($ticket->device_name)
                {{ $ticket->device_name }} ({{ $ticket->device_id }})
              @else
                -
              @endif
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Prioritas</label>
            <div class="fw-semibold">{{ ucfirst($ticket->prioritas) }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Petugas</label>
            <div class="fw-semibold">{{ $ticket->petugas_name ?? '-' }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Status</label>
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
            <div class="fw-semibold">
              <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label text-muted">Kendala</label>
            <div class="fw-semibold">{{ $ticket->kendala }}</div>
          </div>
          <div class="col-12">
            <label class="form-label text-muted">Keterangan</label>
            <div class="fw-semibold">{{ $ticket->keterangan ?? '-' }}</div>
          </div>
          @guest
            @if(($ticket->status ?? 'open') === 'open' && !empty($token))
              <div class="col-12 d-flex justify-content-end">
                <form action="{{ route('helpdesk.progress.guest', $token) }}" method="POST" class="m-0">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
                    <i data-feather="play"></i>
                    <span>Proses</span>
                  </button>
                </form>
              </div>
            @endif
          @endguest
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
