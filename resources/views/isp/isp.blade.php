@extends('layouts.app')

@section('title', 'Daftar ISP')

@section('content')
	<h1 class="page-title">Daftar ISP</h1>

	<div class="row">
		<div class="col-12">
			<div class="card">  
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Data ISP</h5>
					@permission('isp', 'create')
						<a href="/isp/tambah" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
							<i data-feather="plus"></i>
							<span>Tambah ISP</span>
						</a>
					@endpermission
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-hover align-middle">
							<thead>
								<tr>
									<th style="width:60px">No</th>
									<th>Nama ISP</th>
									<th>No Pelanggan</th>
									<th>Jenis Koneksi</th>
									<th>Bandwidth</th>
									<th>IP Address</th>
									<th>Ruang Instalasi</th>
									<th>PIC ISP</th>
									<th>No Telepon</th>
									<th>Status</th>
									<th>Keterangan</th>
									<th style="width:160px">Aksi</th>
								</tr>
							</thead>
							<tbody>
								@forelse($isps as $index => $isp)
									@php($encoded = encrypt($isp->id))
									<tr>
										<td>{{ $index + 1 }}</td>
										<td>{{ $isp->nama_isp }}</td>
										<td>{{ $isp->no_pelanggan ?? '-' }}</td>
										<td>{{ $isp->jenis_koneksi }}</td>
										<td>{{ $isp->bandwidth }}</td>
										<td>{{ $isp->ip_address }}</td>
										<td>{{ $isp->room_name ?? '-' }}</td>
										<td>{{ $isp->pic_isp }}</td>
										<td>{{ $isp->no_telepon }}</td>
										<td>{{ $isp->status }}</td>
										<td>{{ $isp->keterangan ?? '-' }}</td>
										<td>
											<div class="d-flex gap-2">
												@permission('isp', 'update')
													<a href="{{ route('isp.edit', $encoded) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
												@endpermission
												@permission('isp', 'delete')
													<form method="POST" action="{{ route('isp.destroy', $encoded) }}" class="js-delete-isp">
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
										<td colspan="12" class="text-center text-muted">Belum ada data ISP.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

<script>
  if (typeof feather !== 'undefined') feather.replace();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    document.querySelectorAll('form.js-delete-isp').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (typeof Swal === 'undefined') { form.submit(); return; }
        Swal.fire({
          title: 'Hapus ISP?',
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
