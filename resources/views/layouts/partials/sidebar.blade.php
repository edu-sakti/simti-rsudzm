<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="{{ url('/dashboard') }}">
            <span class="align-middle">SIMTI RSUDZM</span>
        </a>

        @php
            $user = auth()->user();
            $roleId = $user->role_id ?? null;
            $hasPermissionTable = \Illuminate\Support\Facades\Schema::hasTable('role_permissions');

            $permissionMap = collect();
            if ($roleId && $hasPermissionTable) {
                $permissionMap = \App\Models\RolePermission::where('role_id', $roleId)->get()->keyBy('menu');
            }

            $canAccess = function (?string $permission = null) use ($permissionMap, $hasPermissionTable): bool {
                if (!$hasPermissionTable) {
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
                        ['type' => 'link', 'label' => 'PNS', 'icon' => 'user-check', 'url' => url('/kepegawaian/pns'), 'patterns' => ['kepegawaian/pns'], 'permission' => 'pegawai_pns'],
                        ['type' => 'link', 'label' => 'PPPK', 'icon' => 'user-plus', 'url' => url('/kepegawaian/pppk'), 'patterns' => ['kepegawaian/pppk'], 'permission' => 'pegawai_pppk'],
                        ['type' => 'link', 'label' => 'Kontrak Tetap', 'icon' => 'user-check', 'url' => url('/kepegawaian/kontrak-tetap'), 'patterns' => ['kepegawaian/kontrak-tetap'], 'permission' => 'data_pegawai'],
                        ['type' => 'link', 'label' => 'Kontrak Tidak Tetap', 'icon' => 'user-minus', 'url' => url('/kepegawaian/kontrak-tidak-tetap'), 'patterns' => ['kepegawaian/kontrak-tidak-tetap'], 'permission' => 'data_pegawai'],
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
                    'items' => [
                        ['type' => 'link', 'label' => 'Pengguna', 'icon' => 'users', 'url' => url('/pengguna'), 'patterns' => ['pengguna']],
                        ['type' => 'link', 'label' => 'Peran', 'icon' => 'user-check', 'url' => url('/peran-pengguna'), 'patterns' => ['peran-pengguna']],
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
