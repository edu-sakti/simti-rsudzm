@extends('layouts.app')

@section('title', 'Daftar Perangkat')

@section('content')
<h1 class="page-title mb-4">Daftar Perangkat</h1>

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Tabel Perangkat</h5>
        @permission('perangkat', 'create')
          <a href="{{ route('device.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
            <i data-feather="plus"></i>
            <span>Tambah Perangkat</span>
          </a>
        @endpermission
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('device.index') }}" class="mb-3">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <input type="text" name="q" value="{{ request('q') }}" class="form-control search-rounded" placeholder="Cari Data">
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th style="width:60px">No</th>
                <th>Nama</th>
                <th>Ruangan</th>
                <th class="d-flex align-items-center gap-2">
                  <span>Tipe</span>
                  <div class="dropdown">
                    <a class="text-secondary text-decoration-none fw-bold"
                       href="#"
                       role="button"
                       id="deviceTypeMenu"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       style="letter-spacing:1px;">
                      ⇅
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="deviceTypeMenu">
                      <li>
                        <a class="dropdown-item" href="{{ route('device.index', request()->except('device_type','page')) }}">Semua</a>
                      </li>
                      @foreach($deviceTypes ?? [] as $type)
                        <li>
                          <a class="dropdown-item" href="{{ route('device.index', array_merge(request()->except('page'), ['device_type' => $type])) }}">
                            {{ $type }}
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </th>
                <th>Merek</th>
                <th>Model</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th style="width:160px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php($start = ($devices->currentPage() - 1) * $devices->perPage())
              @forelse($devices as $index => $device)
                <tr>
                  <td>{{ $start + $index + 1 }}</td>
                  <td>{{ $device->device_name }}</td>
                  <td>{{ $device->room->name ?? '-' }}</td>
                  <td>{{ $device->device_type ?? '-' }}</td>
                  <td>{{ $device->brand ?? '-' }}</td>
                  <td>{{ $device->model ?? '-' }}</td>
                  <td>{{ $device->condition ?? '-' }}</td>
                  <td>{{ $device->status ?? '-' }}</td>
                  <td>{{ $device->notes ?? '-' }}</td>
                  <td>
                    <div class="d-flex gap-2">
                      @php($encoded = encrypt($device->id))
                      <button type="button"
                              class="btn btn-sm btn-outline-info btn-detail"
                              data-id="{{ $device->id }}"
                              data-spec='@json($device->spec)'
                              data-ip="{{ $device->ip_info->ip_address ?? '' }}"
                              data-subnet="{{ $device->ip_info->subnet ?? '' }}"
                              data-name="{{ $device->device_name }}"
                              data-type="{{ $device->device_type }}">
                        Detail
                      </button>
                      @permission('perangkat', 'update')
                        <a href="{{ route('device.edit', $encoded) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                      @endpermission
                      @permission('perangkat', 'delete')
                        <form action="{{ route('device.destroy', $device->id) }}" method="POST" class="d-inline js-delete-device">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                      @endpermission
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted">Belum ada data perangkat.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($devices instanceof \Illuminate\Contracts\Pagination\Paginator && $devices->hasPages())
          <div class="d-flex justify-content-end mt-0">
            {{ $devices->links('pagination::bootstrap-4') }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<form id="deleteSpecForm" method="POST" style="display:none;">
  @csrf
  @method('DELETE')
</form>

<script>
  if (typeof feather !== 'undefined') feather.replace();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script>
  (function () {
    const success = @json(session('success'));
    const errorMessage = @json(session('error'));
    if (success && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: success,
        timer: 1800,
        showConfirmButton: false
      });
    }
    if (errorMessage && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: errorMessage
      });
    }

    // Modal for add spec
    const specModalEl = document.getElementById('specModal');
    const specModal = specModalEl ? new bootstrap.Modal(specModalEl) : null;

    let currentDeviceId = null;

    const canCreateSpec = @json(\App\Support\Permission::can(auth()->user(), 'spesifikasi_perangkat', 'create'));
    const canEditSpec = @json(\App\Support\Permission::can(auth()->user(), 'spesifikasi_perangkat', 'update'));
    const canDeleteSpec = @json(\App\Support\Permission::can(auth()->user(), 'spesifikasi_perangkat', 'delete'));

    document.querySelectorAll('.btn-detail').forEach(btn => {
      btn.addEventListener('click', () => {
        const spec = btn.dataset.spec ? JSON.parse(btn.dataset.spec) : null;
        const name = btn.dataset.name || 'Perangkat';
        const type = (btn.dataset.type || '').toLowerCase();
        const canAddSpec = ['cpu','pc aio','laptop','aio'].includes(type);
        currentDeviceId = btn.dataset.id;

        if (spec) {
          const ipAddress = (spec.ip_address ?? btn.dataset.ip) || '-';
          const subnet = (spec.subnet ?? btn.dataset.subnet) || '-';
          const html = `
            <table class="table table-sm text-start">
              <tr><th>Processor</th><td>${spec.processor ?? '-'}</td></tr>
              <tr><th>RAM</th><td>${spec.ram ?? '-'}</td></tr>
              <tr><th>Storage</th><td>${spec.storage_type ?? '-'} ${spec.storage_capacity ?? ''}</td></tr>
              <tr><th>IP Address</th><td>${ipAddress}</td></tr>
              <tr><th>Subnet</th><td>${subnet}</td></tr>
              <tr><th>GPU</th><td>${spec.gpu ?? '-'}</td></tr>
              <tr><th>OS</th><td>${spec.os ?? '-'}</td></tr>
              <tr><th>Detail</th><td>${spec.details ?? '-'}</td></tr>
            </table>
          `;
          Swal.fire({
            icon: 'info',
            title: `Spesifikasi ${name}`,
            html,
            width: 600,
            showCancelButton: !!canEditSpec,
            cancelButtonText: 'Edit',
            confirmButtonText: 'OK',
            showDenyButton: !!canDeleteSpec,
            denyButtonText: 'Hapus',
            reverseButtons: false,
            didOpen: () => {
              const actions = Swal.getActions();
              const cancelBtn = Swal.getCancelButton();
              const denyBtn = Swal.getDenyButton();
              const confirmBtn = Swal.getConfirmButton();
              actions.innerHTML = '';
              if (cancelBtn) actions.append(cancelBtn);  // Edit
              actions.append(confirmBtn); // OK
              if (denyBtn) actions.append(denyBtn);    // Hapus
            }
          }).then((res) => {
            if (res.dismiss === Swal.DismissReason.cancel) {
              window.location.href = "{{ url('/perangkat/spesifikasi-perangkat') }}/" + btn.dataset.id;
            } else if (res.isDenied) {
              const form = document.getElementById('deleteSpecForm');
              form.action = "{{ url('/perangkat/spesifikasi-perangkat') }}/" + btn.dataset.id;
              form.submit();
            }
          });
          return;
        }

        if (!canAddSpec) {
          const lowerType = type.trim();
          if (['monitor','printer'].includes(lowerType)) {
            Swal.fire({
              icon: 'info',
              title: 'Spesifikasi tidak diperlukan',
              text: 'Perangkat ini tidak memiliki spesifikasi khusus.'
            });
          } else {
            Swal.fire({
              icon: 'warning',
              title: 'Belum ada spesifikasi',
              text: 'Spesifikasi belum ditambahkan untuk perangkat ini.'
            });
          }
          return;
        }

        if (!canCreateSpec) {
          Swal.fire({
            icon: 'warning',
            title: 'Akses ditolak',
            text: 'Anda tidak memiliki izin menambah spesifikasi perangkat.'
          });
          return;
        }

        if (specModal) {
          specModalEl.querySelector('#specTitle').innerText = `Tambah Spesifikasi (manual) - ${name}`;
          specModalEl.querySelector('#spec_proc').value = '';
          specModalEl.querySelector('#spec_ram').value = '';
          specModalEl.querySelector('#spec_stype').value = 'HDD';
          specModalEl.querySelector('#spec_scap').value = '';
          specModalEl.querySelector('#spec_gpu').value = '';
          specModalEl.querySelector('#spec_os').value = '';
          specModalEl.querySelector('#spec_detail').value = '';
          specModal.show();
        } else {
          Swal.fire({
            icon: 'info',
            title: 'Spesifikasi belum ditambahkan',
            text: 'Spesifikasi device belum ditambahkan, segera ditambahkan!',
            confirmButtonText: 'Tambah',
            allowOutsideClick: true
          }).then(() => {
            window.location.href = "{{ route('device.spec.form') }}";
          });
        }
      });
    });

    // Validasi di modal spesifikasi (client-side)
    const btnSave = document.getElementById('btnSpecSave');
    if (specModalEl && btnSave) {
      btnSave.addEventListener('click', (e) => {
        e.preventDefault();
        const proc = specModalEl.querySelector('#spec_proc').value.trim();
        const ram = specModalEl.querySelector('#spec_ram').value.trim();
        const stype = specModalEl.querySelector('#spec_stype').value;
        const scap = specModalEl.querySelector('#spec_scap').value.trim();
        const gpu = specModalEl.querySelector('#spec_gpu').value.trim();
        const os = specModalEl.querySelector('#spec_os').value.trim();
        const detail = specModalEl.querySelector('#spec_detail').value.trim();

        const errors = [];
        if (!proc) errors.push('Processor wajib diisi.');
        if (!ram) errors.push('RAM wajib dipilih.');
        if (!scap) errors.push('Storage Capacity wajib dipilih.');
        if (!stype) errors.push('Storage Type wajib dipilih.');
        if (!os) errors.push('OS wajib diisi.');

        if (errors.length) {
          Swal.fire({
            icon: 'error',
            title: 'Input belum valid',
            html: errors.join('<br>')
          });
          return;
        }

        if (!currentDeviceId) {
          Swal.fire({icon:'error',title:'Gagal',text:'ID perangkat tidak ditemukan.'});
          return;
        }

        const token = document.querySelector('meta[name=\"csrf-token\"]')?.content;
        const getCookie = (name) => {
          const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
          return match ? decodeURIComponent(match[2]) : null;
        };
        const xsrf = getCookie('XSRF-TOKEN');
        // submit normal form
        const specForm = document.getElementById('specForm');
        specForm?.submit();
      });
    }

    document.querySelectorAll('form.js-delete-device').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') { form.submit(); return; }
        Swal.fire({
          title: 'Hapus Perangkat?',
          text: 'Data yang dihapus tidak dapat dikembalikan.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal'
        }).then((result) => { if (result.isConfirmed) form.submit(); });
      });
    });
  })();
</script>
@endsection
