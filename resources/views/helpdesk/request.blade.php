@extends('layouts.app')

@section('title', 'Request Ticket')

@section('content')
    <h1 class="page-title">Request Ticket</h1>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Form Request Ticket</h5>
                    <a href="{{ route('helpdesk.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
                        <i data-feather="arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('helpdesk.request.store') }}" class="row g-3" id="requestTicketForm">
                        @csrf

                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->toDateString()) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pelapor</label>
                            <input type="text" name="pelapor" class="form-control" value="{{ old('pelapor', auth()->user()->name) }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ruangan</label>
                            <select name="room_id" id="roomSelect" class="form-select">
                                <option value="">Pilih Ruangan</option>
                                @foreach(($rooms ?? []) as $room)
                                    <option value="{{ $room->id }}" {{ (string) old('room_id') === (string) $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Ruangan hanya sesuai penempatan Anda di `room_users`.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" id="kategoriSelect" class="form-select">
                                <option value="">Pilih Kategori</option>
                                <option value="hardware" {{ old('kategori') === 'hardware' ? 'selected' : '' }}>Hardware</option>
                                <option value="software" {{ old('kategori') === 'software' ? 'selected' : '' }}>Software</option>
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
                            <textarea name="kendala" class="form-control" rows="3" placeholder="Deskripsi kendala">{{ old('kendala') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Prioritas</label>
                            <select name="prioritas" class="form-select">
                                <option value="">Pilih Prioritas</option>
                                <option value="rendah" {{ old('prioritas') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="sedang" {{ old('prioritas') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="tinggi" {{ old('prioritas') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Catatan tambahan" value="{{ old('keterangan') }}">
                        </div>

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i data-feather="send"></i>
                                <span>Kirim Request</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    if (typeof feather !== 'undefined') feather.replace();

    const form = document.getElementById('requestTicketForm');
    const roomSelect = document.getElementById('roomSelect');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const deviceSelect = document.getElementById('deviceSelect');
    const deviceHelp = document.getElementById('deviceHelp');

    async function loadDevicesByRoom() {
        const roomId = roomSelect?.value || '';
        const kategori = kategoriSelect?.value || '';

        deviceSelect.innerHTML = '<option value="">Pilih Perangkat</option>';
        deviceSelect.disabled = true;
        if (deviceHelp) deviceHelp.textContent = 'Pilih kategori Hardware dan ruangan terlebih dahulu.';

        if (!roomId || kategori !== 'hardware') {
            return;
        }

        try {
            const res = await fetch(`/helpdesk/devices?room_id=${encodeURIComponent(roomId)}`);
            const data = await res.json();
            const selected = deviceSelect.dataset.selected || '';

            if (Array.isArray(data)) {
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
            if (deviceHelp) {
                deviceHelp.textContent = Array.isArray(data) && data.length
                    ? 'Pilih perangkat yang sesuai.'
                    : 'Tidak ada perangkat di ruangan ini.';
            }
        } catch (error) {
            deviceSelect.disabled = true;
            if (deviceHelp) deviceHelp.textContent = 'Gagal memuat perangkat.';
        }
    }

    roomSelect?.addEventListener('change', loadDevicesByRoom);
    kategoriSelect?.addEventListener('change', () => {
        if (kategoriSelect.value !== 'hardware') {
            deviceSelect.value = '';
            deviceSelect.dataset.selected = '';
        }
        loadDevicesByRoom();
    });

    if ((roomSelect?.value || '') || (kategoriSelect?.value || '')) {
        loadDevicesByRoom();
    }

    form?.addEventListener('submit', (event) => {
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
            return !el || !el.value || el.value.trim() === '';
        });

        const kategori = form.querySelector('select[name="kategori"]')?.value;
        const device = form.querySelector('select[name="device_id"]')?.value;

        if (missing || (kategori === 'hardware' && !device)) {
            event.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: missing
                        ? 'Mohon lengkapi semua kolom wajib.'
                        : 'Perangkat wajib dipilih untuk kategori Hardware.',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
</script>

@if($errors->any())
<script>
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: @json($errors->first()),
            confirmButtonText: 'OK'
        });
    }
</script>
@endif
@endpush
