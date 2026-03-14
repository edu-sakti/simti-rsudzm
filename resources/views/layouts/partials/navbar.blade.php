<nav class="navbar navbar-expand navbar-light navbar-bg py-1">
	<a class="sidebar-toggle js-sidebar-toggle">
		<i class="hamburger align-self-center"></i>
	</a>

	<div class="navbar-collapse collapse">
		<ul class="navbar-nav navbar-align">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#">
    <img src="{{ asset('adminkit/img/avatars/avatar.jpg') }}" 
         class="avatar img-fluid rounded-circle me-2" 
         alt="User" />
    <div class="d-flex flex-column lh-1">
        @auth
          @php
            $roleLabelMap = [
              'admin' => 'Admin',
              'petugas' => 'Petugas IT',
              'staff' => 'Petugas',
              'manajemen' => 'Manajemen',
              'kepala_ruangan' => 'Kepala Ruangan',
            ];
            $role = Auth::user()->role ?? 'staff';
            $roleLabel = $roleLabelMap[$role] ?? ucfirst($role);
            if ($role === 'kepala_ruangan') {
              $roomName = Auth::user()->room->name ?? '';
              $roleLabel = trim('KARU ' . $roomName);
            }
          @endphp
          <span class="fw-semibold text-dark">{{ Auth::user()->name }}</span>
          <small class="text-muted mt-1" style="font-size: 12px;">{{ $roleLabel }}</small>
        @else
          <span class="fw-semibold text-dark">Tamu</span>
          <small class="text-muted mt-3" style="font-size: 12px;">Guest</small>
        @endauth
    </div>
                </a>
    
			</li>
		</ul>
	</div>
</nav>

@push('styles')
<style>
  /* Compact navbar height */
  .navbar.navbar-bg { padding-top: .25rem; padding-bottom: .25rem; }
  .navbar .nav-link { padding-top: .25rem; padding-bottom: .25rem; }
  .navbar .avatar { width: 36px; height: 36px; }
</style>
@endpush
