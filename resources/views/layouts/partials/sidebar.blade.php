<nav id="sidebar" class="sidebar js-sidebar">
	<div class="sidebar-content js-simplebar">
		@php
			$activeAppRaw = session('active_app');
			$activeApp = in_array($activeAppRaw, ['inventaris', 'jaringan', 'monitoring'], true) ? 'master-data' : $activeAppRaw;
			$portalTitleMap = [
				'helpdesk' => 'Helpdesk',
				'master-data' => 'Master Data',
				'persuratan' => 'Persuratan',
				'pengaduan' => 'Pengaduan',
				'kepegawaian' => 'Kepegawaian',
				'manajemen-pengguna' => 'Pengguna',
				'integrasi' => 'Setting',
			];
			$portalTitle = $portalTitleMap[$activeApp] ?? 'SIMTI RSUDZM';
		@endphp
		<a class="sidebar-brand" href="{{ url('/apps') }}">
			<span class="align-middle">{{ $portalTitle }}</span>
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
				$appMenus = [
					'helpdesk' => ['dashboard', 'helpdesk', 'laporan'],
					'master-data' => ['perangkat', 'ruangan', 'pj_ruangan', 'roles', 'ip_address', 'isp', 'cctv'],
					'persuratan' => ['surat_masuk', 'surat_keluar', 'disposisi', 'arsip_surat'],
					'pengaduan' => ['pengaduan_data'],
					'kepegawaian' => [
						'data_pegawai',
						'pegawai_pns',
						'pegawai_pppk',
						'jabatan',
						'unit_ruangan',
						'riwayat_pegawai',
						'riwayat_pendidikan',
						'riwayat_pangkat',
						'riwayat_mutasi',
						'riwayat_pelatihan',
						'legalitas_sip',
						'legalitas_str',
					],
					'manajemen-pengguna' => ['profil', 'pengguna', 'peran_pengguna', 'hak_akses'],
					'integrasi' => ['wa_gateway', 'log_aktivitas'],
				];
				$showMenu = function (string $key) use ($activeApp, $appMenus) {
					if (!$activeApp || !isset($appMenus[$activeApp])) {
						return true;
					}
					if (in_array($key, ['dashboard', 'laporan'], true)) {
						return true;
					}
					return in_array($key, $appMenus[$activeApp], true);
				};
			@endphp
			@if ($role === 'kepala_ruangan')
				<li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
					@if($canMenu('dashboard') && $showMenu('dashboard'))
						<a class="sidebar-link" href="{{ url('dashboard') }}">
							<i class="align-middle" data-feather="home"></i>
							<span class="align-middle">Dashboard</span>
						</a>
					@endif
				</li>

				<li class="sidebar-item {{ request()->is('helpdesk') ? 'active' : '' }}">
					@if($canMenu('helpdesk') && $showMenu('helpdesk'))
						<a class="sidebar-link" href="{{ url('/helpdesk') }}">
							<i class="align-middle" data-feather="message-circle"></i>
							<span class="align-middle">Tiket</span>
						</a>
					@endif
				</li>

				<li class="sidebar-item {{ request()->is('laporan') ? 'active' : '' }}">
					@if($canMenu('laporan') && $showMenu('laporan'))
						<a class="sidebar-link" href="{{ url('laporan') }}">
							<i class="align-middle" data-feather="clipboard"></i>
							<span class="align-middle">Laporan</span>
						</a>
					@endif
				</li>
			@else

			<li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
				@if($canMenu('dashboard') && $showMenu('dashboard'))
					<a class="sidebar-link" href="{{ url('dashboard') }}">
						<i class="align-middle" data-feather="home"></i>
						<span class="align-middle">Dashboard</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('ip-address') ? 'active' : '' }}">
				@if($canMenu('ip_address') && $showMenu('ip_address'))
					<a class="sidebar-link" href="{{ route('ipaddr.index') }}">
						<i class="align-middle" data-feather="map-pin"></i>
						<span class="align-middle">IP Address</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('perangkat') ? 'active' : '' }}">
				@if($canMenu('perangkat') && $showMenu('perangkat'))
					<a class="sidebar-link" href="{{ route('device.index') }}">
						<i class="align-middle" data-feather="server"></i>
						<span class="align-middle">Perangkat</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('isp') ? 'active' : '' }}">
				@if($canMenu('isp') && $showMenu('isp'))
					<a class="sidebar-link" href="{{ url('isp') }}">
						<i class="align-middle" data-feather="globe"></i>
						<span class="align-middle">ISP</span>
					</a>
				@endif
			</li>

			@php
				$pegawaiActive = request()->is('kepegawaian/data-pegawai')
					|| request()->is('kepegawaian/pns')
					|| request()->is('kepegawaian/pppk');
			@endphp
			<li class="sidebar-item {{ $pegawaiActive ? 'active' : '' }}">
				@if($canMenu('data_pegawai') && $showMenu('data_pegawai'))
					<a class="sidebar-link {{ $pegawaiActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#pegawaiSubmenu" role="button" aria-expanded="{{ $pegawaiActive ? 'true' : 'false' }}" aria-controls="pegawaiSubmenu">
						<i class="align-middle" data-feather="users"></i>
						<span class="align-middle">Data Pegawai</span>
					</a>
					<ul id="pegawaiSubmenu" class="sidebar-dropdown list-unstyled collapse {{ $pegawaiActive ? 'show' : '' }}">
						<li class="sidebar-item {{ request()->is('kepegawaian/pns') ? 'active' : '' }}">
							@if($canMenu('pegawai_pns') && $showMenu('pegawai_pns'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/pns') }}">
									<i class="align-middle" data-feather="user-check"></i>
									<span class="align-middle">PNS</span>
								</a>
							@endif
						</li>
						<li class="sidebar-item {{ request()->is('kepegawaian/pppk') ? 'active' : '' }}">
							@if($canMenu('pegawai_pppk') && $showMenu('pegawai_pppk'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/pppk') }}">
									<i class="align-middle" data-feather="user-plus"></i>
									<span class="align-middle">PPPK</span>
								</a>
							@endif
						</li>
					</ul>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('kepegawaian/jabatan') ? 'active' : '' }}">
				@if($canMenu('jabatan') && $showMenu('jabatan'))
					<a class="sidebar-link" href="{{ url('/kepegawaian/jabatan') }}">
						<i class="align-middle" data-feather="award"></i>
						<span class="align-middle">Jabatan</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('kepegawaian/unit-ruangan') ? 'active' : '' }}">
				@if($canMenu('unit_ruangan') && $showMenu('unit_ruangan'))
					<a class="sidebar-link" href="{{ url('/kepegawaian/unit-ruangan') }}">
						<i class="align-middle" data-feather="grid"></i>
						<span class="align-middle">Unit / Ruangan</span>
					</a>
				@endif
			</li>

			@php
				$riwayatActive = request()->is('kepegawaian/riwayat')
					|| request()->is('kepegawaian/riwayat/pendidikan')
					|| request()->is('kepegawaian/riwayat/pangkat-golongan')
					|| request()->is('kepegawaian/riwayat/mutasi')
					|| request()->is('kepegawaian/riwayat/pelatihan');
			@endphp
			<li class="sidebar-item {{ $riwayatActive ? 'active' : '' }}">
				@if($canMenu('riwayat_pegawai') && $showMenu('riwayat_pegawai'))
					<a class="sidebar-link {{ $riwayatActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#riwayatSubmenu" role="button" aria-expanded="{{ $riwayatActive ? 'true' : 'false' }}" aria-controls="riwayatSubmenu">
						<i class="align-middle" data-feather="clock"></i>
						<span class="align-middle">Riwayat Pegawai</span>
					</a>
					<ul id="riwayatSubmenu" class="sidebar-dropdown list-unstyled collapse {{ $riwayatActive ? 'show' : '' }}">
						<li class="sidebar-item {{ request()->is('kepegawaian/riwayat/pendidikan') ? 'active' : '' }}">
							@if($canMenu('riwayat_pendidikan') && $showMenu('riwayat_pendidikan'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/riwayat/pendidikan') }}">
									<i class="align-middle" data-feather="book-open"></i>
									<span class="align-middle">Pendidikan</span>
								</a>
							@endif
						</li>
						<li class="sidebar-item {{ request()->is('kepegawaian/riwayat/pangkat-golongan') ? 'active' : '' }}">
							@if($canMenu('riwayat_pangkat') && $showMenu('riwayat_pangkat'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/riwayat/pangkat-golongan') }}">
									<i class="align-middle" data-feather="trending-up"></i>
									<span class="align-middle">Pangkat / Golongan</span>
								</a>
							@endif
						</li>
						<li class="sidebar-item {{ request()->is('kepegawaian/riwayat/mutasi') ? 'active' : '' }}">
							@if($canMenu('riwayat_mutasi') && $showMenu('riwayat_mutasi'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/riwayat/mutasi') }}">
									<i class="align-middle" data-feather="shuffle"></i>
									<span class="align-middle">Mutasi</span>
								</a>
							@endif
						</li>
						<li class="sidebar-item {{ request()->is('kepegawaian/riwayat/pelatihan') ? 'active' : '' }}">
							@if($canMenu('riwayat_pelatihan') && $showMenu('riwayat_pelatihan'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/riwayat/pelatihan') }}">
									<i class="align-middle" data-feather="clipboard"></i>
									<span class="align-middle">Pelatihan</span>
								</a>
							@endif
						</li>
					</ul>
				@endif
			</li>

			@php
				$legalitasActive = request()->is('kepegawaian/legalitas/sip')
					|| request()->is('kepegawaian/legalitas/str');
			@endphp
			<li class="sidebar-item {{ $legalitasActive ? 'active' : '' }}">
				@if($canMenu('legalitas_sip') && $showMenu('legalitas_sip'))
					<a class="sidebar-link {{ $legalitasActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#legalitasSubmenu" role="button" aria-expanded="{{ $legalitasActive ? 'true' : 'false' }}" aria-controls="legalitasSubmenu">
						<i class="align-middle" data-feather="shield"></i>
						<span class="align-middle">Legalitas</span>
					</a>
					<ul id="legalitasSubmenu" class="sidebar-dropdown list-unstyled collapse {{ $legalitasActive ? 'show' : '' }}">
						<li class="sidebar-item {{ request()->is('kepegawaian/legalitas/sip') ? 'active' : '' }}">
							@if($canMenu('legalitas_sip') && $showMenu('legalitas_sip'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/legalitas/sip') }}">
									<i class="align-middle" data-feather="file-text"></i>
									<span class="align-middle">SIP</span>
								</a>
							@endif
						</li>
						<li class="sidebar-item {{ request()->is('kepegawaian/legalitas/str') ? 'active' : '' }}">
							@if($canMenu('legalitas_str') && $showMenu('legalitas_str'))
								<a class="sidebar-link" href="{{ url('/kepegawaian/legalitas/str') }}">
									<i class="align-middle" data-feather="file"></i>
									<span class="align-middle">STR</span>
								</a>
							@endif
						</li>
					</ul>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('persuratan/surat-masuk') ? 'active' : '' }}">
				@if($canMenu('surat_masuk') && $showMenu('surat_masuk'))
					<a class="sidebar-link" href="{{ url('/persuratan/surat-masuk') }}">
						<i class="align-middle" data-feather="inbox"></i>
						<span class="align-middle">Surat Masuk</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('persuratan/surat-keluar') ? 'active' : '' }}">
				@if($canMenu('surat_keluar') && $showMenu('surat_keluar'))
					<a class="sidebar-link" href="{{ url('/persuratan/surat-keluar') }}">
						<i class="align-middle" data-feather="send"></i>
						<span class="align-middle">Surat Keluar</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('persuratan/disposisi') ? 'active' : '' }}">
				@if($canMenu('disposisi') && $showMenu('disposisi'))
					<a class="sidebar-link" href="{{ url('/persuratan/disposisi') }}">
						<i class="align-middle" data-feather="corner-up-right"></i>
						<span class="align-middle">Disposisi</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('persuratan/arsip-surat') ? 'active' : '' }}">
				@if($canMenu('arsip_surat') && $showMenu('arsip_surat'))
					<a class="sidebar-link" href="{{ url('/persuratan/arsip-surat') }}">
						<i class="align-middle" data-feather="archive"></i>
						<span class="align-middle">Arsip Surat</span>
					</a>
				@endif
			</li>

			<li class="sidebar-item {{ request()->is('pengaduan/pengaduan') ? 'active' : '' }}">
				@if($canMenu('pengaduan_data') && $showMenu('pengaduan_data'))
					<a class="sidebar-link" href="{{ url('/pengaduan/pengaduan') }}">
						<i class="align-middle" data-feather="message-circle"></i>
						<span class="align-middle">Pengaduan</span>
					</a>
				@endif
			</li>


			<li class="sidebar-item {{ request()->is('cctv') ? 'active' : '' }}">
				@if($canMenu('cctv') && $showMenu('cctv'))
					<a class="sidebar-link" href="{{ url('cctv') }}">
						<i class="align-middle" data-feather="video"></i>
						<span class="align-middle">CCTV</span>
					</a>
				@endif
			</li>



			

			<li class="sidebar-item {{ request()->is('ruangan*') ? 'active' : '' }}">
				@if($canMenu('ruangan') && $showMenu('ruangan'))
					<a class="sidebar-link" href="{{ url('/ruangan') }}">
						<i class="align-middle" data-feather="layout"></i>
						<span class="align-middle">Ruangan</span>
					</a>
				@endif
			</li>
			<li class="sidebar-item {{ request()->is('pj-ruangan') ? 'active' : '' }}">
				@if($canMenu('pj_ruangan') && $showMenu('pj_ruangan'))
					<a class="sidebar-link" href="{{ url('/pj-ruangan') }}">
						<i class="align-middle" data-feather="user-check"></i>
						<span class="align-middle">PJ Ruangan</span>
					</a>
				@endif
			</li>
			<li class="sidebar-item {{ request()->is('roles*') ? 'active' : '' }}">
				@if($canMenu('roles') && $showMenu('roles'))
					<a class="sidebar-link" href="{{ url('/roles') }}">
						<i class="align-middle" data-feather="shield"></i>
						<span class="align-middle">Roles</span>
					</a>
				@endif
			</li>

			@if(auth()->check() && (auth()->user()->is_admin ?? false))
				@if($showMenu('profil'))
					<li class="sidebar-item {{ request()->is('profil') ? 'active' : '' }}">
						<a class="sidebar-link" href="{{ url('/profil') }}">
							<i class="align-middle" data-feather="user"></i>
							<span class="align-middle">Profil</span>
						</a>
					</li>
				@endif
				@if($showMenu('pengguna'))
					<li class="sidebar-item {{ request()->is('pengguna') ? 'active' : '' }}">
						<a class="sidebar-link" href="{{ url('/pengguna') }}">
							<i class="align-middle" data-feather="users"></i>
							<span class="align-middle">Pengguna</span>
						</a>
					</li>
				@endif
				@if($showMenu('peran_pengguna'))
					<li class="sidebar-item {{ request()->is('peran-pengguna') ? 'active' : '' }}">
						<a class="sidebar-link" href="{{ url('/peran-pengguna') }}">
							<i class="align-middle" data-feather="user-check"></i>
							<span class="align-middle">Peran</span>
						</a>
					</li>
				@endif
				@if($showMenu('hak_akses'))
					<li class="sidebar-item {{ request()->is('hak-akses') ? 'active' : '' }}">
						<a class="sidebar-link" href="{{ url('/hak-akses') }}">
							<i class="align-middle" data-feather="shield"></i>
							<span class="align-middle">Hak Akses</span>
						</a>
					</li>
				@endif
			@endif
			
			<li class="sidebar-item {{ request()->is('helpdesk') ? 'active' : '' }}">
				@if($canMenu('helpdesk') && $showMenu('helpdesk'))
					<a class="sidebar-link" href="{{ url('/helpdesk') }}">
						<i class="align-middle" data-feather="message-circle"></i>
						<span class="align-middle">Tiket</span>
					</a>
				@endif
			</li>

			@if(auth()->check() && (auth()->user()->is_admin ?? false))
				@if($showMenu('wa_gateway'))
					<li class="sidebar-item {{ request()->is('whatsapp-gateway') ? 'active' : '' }}">
						<a class="sidebar-link" href="{{ url('whatsapp-gateway') }}">
							<i class="align-middle" data-feather="message-square"></i>
							<span class="align-middle">WA Gateway</span>
						</a>
					</li>
				@endif
				@if($showMenu('log_aktivitas'))
					<li class="sidebar-item {{ request()->is('logs') ? 'active' : '' }}">
						<a class="sidebar-link" href="{{ url('/logs') }}">
							<i class="align-middle" data-feather="activity"></i>
							<span class="align-middle">Log Aktivitas</span>
						</a>
					</li>
				@endif
			@endif
			
			<li class="sidebar-item {{ request()->is('laporan') ? 'active' : '' }}">
				@if($canMenu('laporan') && $showMenu('laporan'))
					<a class="sidebar-link" href="{{ url('laporan') }}">
						<i class="align-middle" data-feather="clipboard"></i>
						<span class="align-middle">Laporan</span>
					</a>
				@endif
			</li>
			@endif

                @auth
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ url('/apps') }}">
                        <i class="align-middle" data-feather="grid"></i>
                        <span class="align-middle">Kembali</span>
                    </a>
                </li>
                @endauth

            </ul>
	</div>
</nav>
