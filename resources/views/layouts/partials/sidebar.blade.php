<nav id="sidebar" class="sidebar js-sidebar">
	<div class="sidebar-content js-simplebar">
		<a class="sidebar-brand" href="{{ url('/') }}">
			<span class="align-middle">SIMTI RSUDZM</span>
		</a>

		<ul class="sidebar-nav">
			@php
				$role = auth()->user()->role ?? null;
				$isAdmin = (bool) (auth()->user()->is_admin ?? false);
				if ($role === 'admin') {
					$isAdmin = true;
				}
				$permissionMap = collect();
				if ($role && !$isAdmin) {
					$permissionMap = \App\Models\RolePermission::where('role', $role)->get()->keyBy('menu');
				}
				$canMenu = function (string $key) use ($isAdmin, $permissionMap) {
					if ($isAdmin) {
						return true;
					}
					return (bool) ($permissionMap[$key]->can_read ?? false);
				};
			@endphp
			@if ($role === 'kepala_ruangan')
				<li class="sidebar-item {{ request()->is('home') ? 'active' : '' }}">
					@if($canMenu('dashboard'))
						<a class="sidebar-link" href="{{ url('home') }}">
							<i class="align-middle" data-feather="home"></i>
							<span class="align-middle">Dashboard</span>
						</a>
					@endif
				</li>

				<li class="sidebar-item {{ request()->is('helpdesk') ? 'active' : '' }}">
					@if($canMenu('helpdesk'))
						<a class="sidebar-link" href="{{ url('/helpdesk') }}">
							<i class="align-middle" data-feather="message-circle"></i>
							<span class="align-middle">Helpdesk</span>
						</a>
					@endif
				</li>

				<li class="sidebar-item {{ request()->is('laporan') ? 'active' : '' }}">
					@if($canMenu('laporan'))
						<a class="sidebar-link" href="{{ url('laporan') }}">
							<i class="align-middle" data-feather="clipboard"></i>
							<span class="align-middle">Laporan</span>
						</a>
					@endif
				</li>
			@else

			<li class="sidebar-item {{ request()->is('home') ? 'active' : '' }}">
				@if($canMenu('dashboard'))
					<a class="sidebar-link" href="{{ url('home') }}">
						<i class="align-middle" data-feather="home"></i>
						<span class="align-middle">Dashboard</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('ip-address') ? 'active' : '' }}">
				@if($canMenu('ip_address'))
					<a class="sidebar-link" href="{{ route('ipaddr.index') }}">
						<i class="align-middle" data-feather="map-pin"></i>
						<span class="align-middle">IP Address</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('perangkat') ? 'active' : '' }}">
				@if($canMenu('perangkat'))
					<a class="sidebar-link" href="{{ route('device.index') }}">
						<i class="align-middle" data-feather="server"></i>
						<span class="align-middle">Perangkat</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('isp') ? 'active' : '' }}">
				@if($canMenu('isp'))
					<a class="sidebar-link" href="{{ url('isp') }}">
						<i class="align-middle" data-feather="globe"></i>
						<span class="align-middle">ISP</span>
					</a>
				@endif
			</li>


			<li class="sidebar-item {{ request()->is('cctv') ? 'active' : '' }}">
				@if($canMenu('cctv'))
					<a class="sidebar-link" href="{{ url('cctv') }}">
						<i class="align-middle" data-feather="video"></i>
						<span class="align-middle">CCTV</span>
					</a>
				@endif
			</li>



			

			<li class="sidebar-item {{ request()->is('ruangan*') ? 'active' : '' }}">
				@if($canMenu('ruangan'))
					<a class="sidebar-link" href="{{ url('/ruangan') }}">
						<i class="align-middle" data-feather="layout"></i>
						<span class="align-middle">Ruangan</span>
					</a>
				@endif
			</li>

			@if(auth()->check() && (auth()->user()->is_admin ?? false))
				<li class="sidebar-item {{ request()->is('pengguna') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ url('/pengguna') }}">
						<i class="align-middle" data-feather="user"></i>
						<span class="align-middle">Pengguna</span>
					</a>
				</li>
				<li class="sidebar-item {{ request()->is('hak-akses') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ url('/hak-akses') }}">
						<i class="align-middle" data-feather="shield"></i>
						<span class="align-middle">Hak Akses</span>
					</a>
				</li>
			@endif
			
			<li class="sidebar-item {{ request()->is('helpdesk') ? 'active' : '' }}">
				@if($canMenu('helpdesk'))
					<a class="sidebar-link" href="{{ url('/helpdesk') }}">
						<i class="align-middle" data-feather="message-circle"></i>
						<span class="align-middle">Helpdesk</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('laporan') ? 'active' : '' }}">
				@if($canMenu('laporan'))
					<a class="sidebar-link" href="{{ url('laporan') }}">
						<i class="align-middle" data-feather="clipboard"></i>
						<span class="align-middle">Laporan</span>
					</a>
				@endif
			</li>

			@if(auth()->check() && (auth()->user()->is_admin ?? false))
				<li class="sidebar-item {{ request()->is('whatsapp-gateway') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ url('whatsapp-gateway') }}">
						<i class="align-middle" data-feather="message-square"></i>
						<span class="align-middle">WA Gateway</span>
					</a>
				</li>
			@endif
			@endif

                @auth
                <li class="sidebar-item">
                    <a class="sidebar-link" href="#" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                        <i class="align-middle" data-feather="log-out"></i>
                        <span class="align-middle">Logout</span>
                    </a>
                    <form id="sidebar-logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                        @csrf
                    </form>
                </li>
                @endauth

            </ul>
	</div>
</nav>
