@extends('layouts.app')

@section('title', 'WA Gateway')

@section('content')
<h1 class="page-title mb-4">WA Gateway</h1>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-light rounded p-3">
          <i data-feather="smartphone"></i>
        </div>
        <div>
          <div class="text-muted">Device</div>
          <div class="fs-4 fw-semibold">{{ $waHasAuth ? 1 : 0 }}</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-light rounded p-3">
          <i data-feather="link"></i>
        </div>
        <div>
          <div class="text-muted">Status</div>
          <div class="fs-5 fw-semibold {{ $waStatus === 'connected' ? 'text-success' : 'text-muted' }}">
            {{ $waStatus === 'connected' ? 'Connected' : 'Disconnected' }}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-light rounded p-3">
          <i data-feather="send"></i>
        </div>
        <div>
          <div class="text-muted">Pesan Terkirim</div>
          <div class="fs-4 fw-semibold">{{ $waSentCount ?? 0 }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Devices</h5>
        <button class="btn btn-sm btn-dark d-inline-flex align-items-center gap-2 js-connect-wa">
          <i data-feather="plus"></i>
          <span>Connect</span>
        </button>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th>Device</th>
                <th>Status</th>
                <th>Phone</th>
                <th>Last Active</th>
                <th style="width:420px">Action</th>
              </tr>
            </thead>
            <tbody>
              @if($waHasAuth)
                <tr>
                  <td>
                    <div class="fw-semibold">SIMTI RSUDZM Gateway</div>
                    <div class="text-muted small">ID: default-baileys</div>
                  </td>
                  <td>
                    <span class="badge {{ $waStatus === 'connected' ? 'bg-success' : 'bg-secondary' }}">
                      {{ $waStatus === 'connected' ? 'Connected' : 'Disconnected' }}
                    </span>
                  </td>
                  <td class="text-muted">{{ $waPhone ? '+' . $waPhone : '-' }}</td>
                  <td class="text-muted">
                    @if(!empty($waLastActiveAt))
                      {{ \Carbon\Carbon::parse($waLastActiveAt)->format('d/m/Y H:i') }}
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    <div class="d-flex flex-wrap gap-2">
                      @if($waStatus === 'connected')
                        <button class="btn btn-sm btn-primary js-reconnect">Reconnect</button>
                        <button class="btn btn-sm btn-danger js-disconnect">Disconnect</button>
                      @else
                        <button class="btn btn-sm btn-secondary js-show-qr">QR</button>
                      @endif
                    </div>
                  </td>
                </tr>
              @else
                <tr>
                  <td colspan="4" class="text-center text-muted">Belum ada device.</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  @if (session('success'))
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: {!! json_encode(session('success')) !!},
        timer: 2000,
        showConfirmButton: false
      });
    }
  @endif
  @if (session('error'))
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: {!! json_encode(session('error')) !!}
      });
    }
  @endif

  (function () {
    const form = document.querySelector('.js-delete-wa');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (typeof Swal === 'undefined') {
        if (confirm('Hapus device ini?')) form.submit();
        return;
      }
      Swal.fire({
        title: 'Hapus Device?',
        text: 'Apakah kamu yakin menghapus device ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) form.submit();
      });
    });
  })();

  (function () {
    const connectBtn = document.querySelector('.js-connect-wa');
    const qrBtn = document.querySelector('.js-show-qr');
    const reconnectBtn = document.querySelector('.js-reconnect');
    const disconnectBtn = document.querySelector('.js-disconnect');
    let qrPollTimer = null;

    const openQrPopup = () => {
      if (typeof Swal === 'undefined') return;
      Swal.fire({
        title: 'Scan QR WhatsApp',
        html: '<div class="text-muted" id="wa-qr-loading">Memuat QR...</div><img id="wa-qr-image" src="" alt="QR WhatsApp" style="max-width:100%; display:none;">',
        showConfirmButton: true,
        confirmButtonText: 'Tutup',
        didClose: () => stopQrPolling()
      });
    };

    const fetchQr = async () => {
      try {
        const res = await fetch("{{ route('wa.gateway.qr') }}");
        const data = await res.json();
        if (data && data.qr) {
          const img = document.getElementById('wa-qr-image');
          const loading = document.getElementById('wa-qr-loading');
          if (img) {
            img.src = data.qr;
            img.style.display = '';
          }
          if (loading) loading.style.display = 'none';
          return true;
        }
      } catch (e) {}
      return false;
    };

    const startQrPolling = () => {
      if (qrPollTimer) clearInterval(qrPollTimer);
      qrPollTimer = setInterval(fetchQr, 2000);
    };

    const stopQrPolling = () => {
      if (qrPollTimer) clearInterval(qrPollTimer);
      qrPollTimer = null;
    };

    if (connectBtn) {
      connectBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        openQrPopup();
        try {
          const res = await fetch("{{ route('wa.gateway.connect') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
          const data = await res.json();
          if (!data.ok) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gateway belum berjalan.' });
            }
            return;
          }
          await fetchQr();
          startQrPolling();
          setTimeout(async () => {
            try { await fetch("{{ route('wa.gateway.status') }}"); } catch (e) {}
          }, 1500);
        } catch (err) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gateway belum berjalan.' });
          }
        }
      });
    }

    if (disconnectBtn) {
      disconnectBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
          const result = await Swal.fire({
            title: 'Putuskan Koneksi?',
            text: 'Session WhatsApp akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Putuskan',
            cancelButtonText: 'Batal'
          });
          if (!result.isConfirmed) return;
        }
        try {
          const res = await fetch("{{ route('wa.gateway.disconnect') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
          const data = await res.json();
          if (!data.ok) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal memutuskan koneksi.' });
            }
            return;
          }
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Koneksi diputus.' });
          }
          setTimeout(() => location.reload(), 800);
        } catch (err) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gateway belum berjalan.' });
          }
        }
      });
    }

    if (reconnectBtn) {
      reconnectBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
          const result = await Swal.fire({
            title: 'Reconnect?',
            text: 'Session lama akan dihapus dan perlu scan QR lagi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal'
          });
          if (!result.isConfirmed) return;
        }
        openQrPopup();
        try {
          const res = await fetch("{{ route('wa.gateway.reconnect') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
          const data = await res.json();
          if (!data.ok) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal reconnect.' });
            }
            return;
          }
          await fetchQr();
          startQrPolling();
        } catch (err) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gateway belum berjalan.' });
          }
        }
      });
    }

    if (qrBtn) {
      qrBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        openQrPopup();
        try {
          await fetch("{{ route('wa.gateway.connect') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }});
        } catch (err) {}
        await fetchQr();
        startQrPolling();
      });
    }
  })();
</script>
@endsection
