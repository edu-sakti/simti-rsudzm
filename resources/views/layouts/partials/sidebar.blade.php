<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="{{ url('/dashboard') }}">
            <span class="align-middle">SIMTI RSUDZM</span>
        </a>

        @php
            $user = auth()->user();
            $isAdmin = (bool) ($user->is_admin ?? false);
            $roleId = $user->role_id ?? null;

            $permissionMap = collect();
            if ($roleId && !$isAdmin) {
                $permissionMap = \App\Models\RolePermission::where('role_id', $roleId)->get()->keyBy('menu');
            }

            $canAccess = function (?string $permission = null, bool $adminOnly = false) use ($isAdmin, $permissionMap): bool {
                if ($adminOnly) {
                    return $isAdmin;
                }

                if ($isAdmin) {
                    return true;
                }

                if (empty($permission)) {
                    return true;
                }

                return (bool) ($permissionMap[$permission]->can_read ?? false);
            };

            $isActive = function (array|string $patterns): bool {
                foreach ((array) $patterns as $pattern) {
                    if (request()->is($pattern)) {
                        return true;
                    }
                }

                return false;
            };

            $sections = [
                [
                    'header' => 'Umum',
                    'items' => [
                        ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'home', 'url' => url('/dashboard'), 'patterns' => ['dashboard'], 'permission' => 'dashboard'],
                    ],
                ],
                [
                    'header' => 'Profil',
                    'items' => [
                        [
                            'type' => 'collapse',
                            'id' => 'profilSubmenu',
                            'label' => 'Profil',
                            'icon' => 'user',
                            'patterns' => ['profile', 'profile/*'],
                            'children' => [
                                ['label' => 'Data Pribadi', 'icon' => 'file-text', 'url' => url('/profile'), 'patterns' => ['profile']],
                                ['label' => 'Kontak Darurat', 'icon' => 'phone', 'url' => 'javascript:void(0)', 'patterns' => []],
                            ],
                        ],
                        [
                            'type' => 'collapse',
                            'id' => 'riwayatProfilSubmenu',
                            'label' => 'Riwayat',
                            'icon' => 'clock',
                            'patterns' => ['profile/riwayat*'],
                            'children' => [
                                ['label' => 'Pendidikan', 'icon' => 'book-open', 'url' => 'javascript:void(0)', 'patterns' => []],
                                ['label' => 'Riwayat Kerja', 'icon' => 'briefcase', 'url' => 'javascript:void(0)', 'patterns' => []],
                            ],
                        ],
                        ['type' => 'link', 'label' => 'Keprofesian', 'icon' => 'award', 'url' => 'javascript:void(0)', 'patterns' => []],
                        ['type' => 'link', 'label' => 'Data Pendukung', 'icon' => 'folder', 'url' => 'javascript:void(0)', 'patterns' => []],
                    ],
                ],
                [
                    'header' => 'Helpdesk',
                    'items' => [
                        ['type' => 'link', 'label' => 'Tiket', 'icon' => 'message-circle', 'url' => url('/helpdesk'), 'patterns' => ['helpdesk'], 'permission' => 'helpdesk'],
                    ],
                ],
                [
                    'header' => 'Persuratan',
                    'items' => [
                        ['type' => 'link', 'label' => 'Surat Masuk', 'icon' => 'inbox', 'url' => url('/persuratan/surat-masuk'), 'patterns' => ['persuratan/surat-masuk'], 'permission' => 'surat_masuk'],
                        ['type' => 'link', 'label' => 'Surat Keluar', 'icon' => 'send', 'url' => url('/persuratan/surat-keluar'), 'patterns' => ['persuratan/surat-keluar'], 'permission' => 'surat_keluar'],
                        ['type' => 'link', 'label' => 'Disposisi', 'icon' => 'corner-up-right', 'url' => url('/persuratan/disposisi'), 'patterns' => ['persuratan/disposisi'], 'permission' => 'disposisi'],
                        ['type' => 'link', 'label' => 'Arsip Surat', 'icon' => 'archive', 'url' => url('/persuratan/arsip-surat'), 'patterns' => ['persuratan/arsip-surat'], 'permission' => 'arsip_surat'],
                    ],
                ],
                [
                    'header' => 'Kepegawaian',
                    'items' => [
                        [
                            'type' => 'collapse',
                            'id' => 'pegawaiSubmenu',
                            'label' => 'Data Pegawai',
                            'icon' => 'users',
                            'permission' => 'data_pegawai',
                            'patterns' => ['kepegawaian/data-pegawai', 'kepegawaian/pns', 'kepegawaian/pppk'],
                            'children' => [
                                ['label' => 'PNS', 'icon' => 'user-check', 'url' => url('/kepegawaian/pns'), 'patterns' => ['kepegawaian/pns'], 'permission' => 'pegawai_pns'],
                                ['label' => 'PPPK', 'icon' => 'user-plus', 'url' => url('/kepegawaian/pppk'), 'patterns' => ['kepegawaian/pppk'], 'permission' => 'pegawai_pppk'],
                            ],
                        ],
                        [
                            'type' => 'collapse',
                            'id' => 'riwayatSubmenu',
                            'label' => 'Riwayat Pegawai',
                            'icon' => 'clock',
                            'permission' => 'riwayat_pegawai',
                            'patterns' => [
                                'kepegawaian/riwayat',
                                'kepegawaian/riwayat/pendidikan',
                                'kepegawaian/riwayat/pangkat-golongan',
                                'kepegawaian/riwayat/mutasi',
                                'kepegawaian/riwayat/pelatihan',
                            ],
                            'children' => [
                                ['label' => 'Pendidikan', 'icon' => 'book-open', 'url' => url('/kepegawaian/riwayat/pendidikan'), 'patterns' => ['kepegawaian/riwayat/pendidikan'], 'permission' => 'riwayat_pendidikan'],
                                ['label' => 'Pangkat / Golongan', 'icon' => 'trending-up', 'url' => url('/kepegawaian/riwayat/pangkat-golongan'), 'patterns' => ['kepegawaian/riwayat/pangkat-golongan'], 'permission' => 'riwayat_pangkat'],
                                ['label' => 'Mutasi', 'icon' => 'shuffle', 'url' => url('/kepegawaian/riwayat/mutasi'), 'patterns' => ['kepegawaian/riwayat/mutasi'], 'permission' => 'riwayat_mutasi'],
                                ['label' => 'Pelatihan', 'icon' => 'clipboard', 'url' => url('/kepegawaian/riwayat/pelatihan'), 'patterns' => ['kepegawaian/riwayat/pelatihan'], 'permission' => 'riwayat_pelatihan'],
                            ],
                        ],
                        ['type' => 'link', 'label' => 'Jabatan Pegawai', 'icon' => 'award', 'url' => url('/kepegawaian/jabatan'), 'patterns' => ['kepegawaian/jabatan'], 'permission' => 'jabatan'],
                        [
                            'type' => 'collapse',
                            'id' => 'legalitasSubmenu',
                            'label' => 'Legalitas',
                            'icon' => 'shield',
                            'permission' => 'legalitas_sip',
                            'patterns' => ['kepegawaian/legalitas/sip', 'kepegawaian/legalitas/str'],
                            'children' => [
                                ['label' => 'SIP', 'icon' => 'file-text', 'url' => url('/kepegawaian/legalitas/sip'), 'patterns' => ['kepegawaian/legalitas/sip'], 'permission' => 'legalitas_sip'],
                                ['label' => 'STR', 'icon' => 'file', 'url' => url('/kepegawaian/legalitas/str'), 'patterns' => ['kepegawaian/legalitas/str'], 'permission' => 'legalitas_str'],
                            ],
                        ],
                    ],
                ],
                [
                    'header' => 'Pengajuan',
                    'items' => [
                        ['type' => 'link', 'label' => 'Pengajuan', 'icon' => 'file-plus', 'url' => url('/pengajuan'), 'patterns' => ['pengajuan'], 'permission' => 'pengajuan'],
                    ],
                ],
                [
                    'header' => 'Pengaduan',
                    'items' => [
                        ['type' => 'link', 'label' => 'Pengaduan', 'icon' => 'message-circle', 'url' => url('/pengaduan/pengaduan'), 'patterns' => ['pengaduan/pengaduan'], 'permission' => 'pengaduan_data'],
                    ],
                ],
                [
                    'header' => 'Pengguna',
                    'adminOnly' => true,
                    'items' => [
                        ['type' => 'link', 'label' => 'Pengguna', 'icon' => 'users', 'url' => url('/pengguna'), 'patterns' => ['pengguna']],
                        ['type' => 'link', 'label' => 'Peran', 'icon' => 'user-check', 'url' => url('/peran-pengguna'), 'patterns' => ['peran-pengguna']],
                        ['type' => 'link', 'label' => 'Hak Akses', 'icon' => 'shield', 'url' => url('/hak-akses'), 'patterns' => ['hak-akses']],
                    ],
                ],
                [
                    'header' => 'Master Data',
                    'items' => [
                        ['type' => 'link', 'label' => 'IP Address', 'icon' => 'map-pin', 'url' => route('ipaddr.index'), 'patterns' => ['ip-address'], 'permission' => 'ip_address'],
                        ['type' => 'link', 'label' => 'Perangkat', 'icon' => 'server', 'url' => route('device.index'), 'patterns' => ['perangkat'], 'permission' => 'perangkat'],
                        ['type' => 'link', 'label' => 'ISP', 'icon' => 'globe', 'url' => url('/isp'), 'patterns' => ['isp'], 'permission' => 'isp'],
                        ['type' => 'link', 'label' => 'CCTV', 'icon' => 'video', 'url' => url('/cctv'), 'patterns' => ['cctv'], 'permission' => 'cctv'],
                        ['type' => 'link', 'label' => 'Ruangan', 'icon' => 'layout', 'url' => url('/ruangan'), 'patterns' => ['ruangan*'], 'permission' => 'ruangan'],
                        ['type' => 'link', 'label' => 'PJ Ruangan', 'icon' => 'user-check', 'url' => url('/pj-ruangan'), 'patterns' => ['pj-ruangan*'], 'permission' => 'pj_ruangan'],
                        ['type' => 'link', 'label' => 'Peran', 'icon' => 'shield', 'url' => url('/roles'), 'patterns' => ['roles*'], 'permission' => 'roles'],
                    ],
                ],
                [
                    'header' => 'Setting',
                    'adminOnly' => true,
                    'items' => [
                        ['type' => 'link', 'label' => 'WA Gateway', 'icon' => 'message-square', 'url' => url('/whatsapp-gateway'), 'patterns' => ['whatsapp-gateway']],
                        ['type' => 'link', 'label' => 'Log Aktivitas', 'icon' => 'activity', 'url' => url('/logs'), 'patterns' => ['logs']],
                    ],
                ],
                [
                    'header' => 'Laporan',
                    'items' => [
                        ['type' => 'link', 'label' => 'Laporan', 'icon' => 'clipboard', 'url' => url('/laporan'), 'patterns' => ['laporan'], 'permission' => 'laporan'],
                    ],
                ],
                [
                    'header' => 'Keluar',
                    'items' => [
                        ['type' => 'logout', 'label' => 'Keluar', 'icon' => 'log-out', 'url' => route('logout'), 'patterns' => []],
                    ],
                ],
            ];
        @endphp

        <ul class="sidebar-nav">
            @foreach ($sections as $section)
                @if (!empty($section['adminOnly']) && !$canAccess(null, true))
                    @continue
                @endif

                @php
                    $renderItems = [];
                    foreach ($section['items'] as $item) {
                        if (!$canAccess($item['permission'] ?? null)) {
                            continue;
                        }

                        if (($item['type'] ?? 'link') === 'collapse') {
                            $children = [];
                            foreach (($item['children'] ?? []) as $child) {
                                if ($canAccess($child['permission'] ?? null)) {
                                    $children[] = $child;
                                }
                            }

                            if (empty($children)) {
                                continue;
                            }

                            $item['children'] = $children;
                        }

                        $renderItems[] = $item;
                    }
                @endphp

                @if (empty($renderItems))
                    @continue
                @endif

                <li class="sidebar-header">{{ $section['header'] }}</li>

                @foreach ($renderItems as $item)
                    @php
                        $type = $item['type'] ?? 'link';
                        $active = $isActive($item['patterns'] ?? []);
                    @endphp

                    @if ($type === 'collapse')
                        <li class="sidebar-item {{ $active ? 'active' : '' }}">
                            <a class="sidebar-link {{ $active ? '' : 'collapsed' }}"
                               data-bs-toggle="collapse"
                               href="#{{ $item['id'] }}"
                               role="button"
                               aria-expanded="{{ $active ? 'true' : 'false' }}"
                               aria-controls="{{ $item['id'] }}">
                                <i class="align-middle" data-feather="{{ $item['icon'] }}"></i>
                                <span class="align-middle">{{ $item['label'] }}</span>
                            </a>

                            <ul id="{{ $item['id'] }}" class="sidebar-dropdown list-unstyled collapse {{ $active ? 'show' : '' }}">
                                @foreach ($item['children'] as $child)
                                    <li class="sidebar-item {{ $isActive($child['patterns'] ?? []) ? 'active' : '' }}">
                                        <a class="sidebar-link" href="{{ $child['url'] }}">
                                            <i class="align-middle" data-feather="{{ $child['icon'] }}"></i>
                                            <span class="align-middle">{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @elseif ($type === 'logout')
                        <li class="sidebar-item">
                            <form method="POST" action="{{ $item['url'] }}" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="sidebar-link border-0 bg-transparent w-100 text-start">
                                    <i class="align-middle" data-feather="{{ $item['icon'] }}"></i>
                                    <span class="align-middle">{{ $item['label'] }}</span>
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="sidebar-item {{ $active ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ $item['url'] }}">
                                <i class="align-middle" data-feather="{{ $item['icon'] }}"></i>
                                <span class="align-middle">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            @endforeach
        </ul>
    </div>
</nav>
