<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Room;
use App\Models\Cctv;
use App\Models\IpAddr;
use App\Models\Device;
use App\Models\DeviceSpec;
use App\Models\HelpdeskTicket;
use App\Models\RolePermission;
use App\Models\Role;

if (!function_exists('ticket_token_encode')) {
    function ticket_token_encode(string $value): string
    {
        $encrypted = Crypt::encryptString($value);
        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }
}

if (!function_exists('ticket_token_decode')) {
    function ticket_token_decode(string $token): string
    {
        $token = strtr($token, '-_', '+/');
        $pad = strlen($token) % 4;
        if ($pad > 0) {
            $token .= str_repeat('=', 4 - $pad);
        }
        return Crypt::decryptString($token);
    }
}

if (!function_exists('profile_token_encode')) {
    function profile_token_encode(string $value): string
    {
        return ticket_token_encode($value);
    }
}

if (!function_exists('profile_token_decode')) {
    function profile_token_decode(string $token): string
    {
        return ticket_token_decode($token);
    }
}

if (!function_exists('peran_token_encode')) {
    function peran_token_encode(string $value): string
    {
        return ticket_token_encode($value);
    }
}

if (!function_exists('peran_token_decode')) {
    function peran_token_decode(string $token): string
    {
        return ticket_token_decode($token);
    }
}

if (!function_exists('role_maps')) {
    function role_maps(): array
    {
        static $byKey = null;
        static $byId = null;
        if ($byKey === null || $byId === null) {
            $byKey = [];
            $byId = [];
            foreach (Role::query()->get() as $role) {
                $key = Str::slug($role->name, '_');
                $byKey[$key] = $role->id;
                $byId[$role->id] = $key;
            }
        }

        return [$byKey, $byId];
    }
}

if (!function_exists('role_id_by_key')) {
    function role_id_by_key(string $key): ?int
    {
        [$byKey] = role_maps();
        return $byKey[$key] ?? null;
    }
}

if (!function_exists('role_key_from_id')) {
    function role_key_from_id(?int $roleId): ?string
    {
        if (!$roleId) {
            return null;
        }
        [, $byId] = role_maps();
        return $byId[$roleId] ?? null;
    }
}

if (!function_exists('normalize_id_phone')) {
    function normalize_id_phone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $raw = preg_replace('/\D+/', '', $phone);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, '0')) {
            $raw = '62' . substr($raw, 1);
        } elseif (str_starts_with($raw, '8')) {
            $raw = '62' . $raw;
        }

        return $raw;
    }
}

if (!function_exists('is_valid_id_phone')) {
    function is_valid_id_phone(?string $phone): bool
    {
        return is_string($phone) && (bool) preg_match('/^62\d{8,15}$/', $phone);
    }
}

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    $totalTickets = \App\Models\HelpdeskTicket::count();
    $pendingTickets = \App\Models\HelpdeskTicket::where('status', 'open')->count();
    $progressTickets = \App\Models\HelpdeskTicket::whereIn('status', ['assigned', 'in_progress'])->count();
    $doneTickets = \App\Models\HelpdeskTicket::whereIn('status', ['resolved', 'closed'])->count();

    return view('dashboard', compact('totalTickets', 'pendingTickets', 'progressTickets', 'doneTickets'));
})->middleware(['auth', 'permission:dashboard,read']);

// ---------------- Persuratan ----------------
Route::get('/persuratan/surat-masuk', function () {
    return view('persuratan.surat-masuk');
})->middleware(['auth', 'permission:surat_masuk,read']);

Route::get('/persuratan/surat-keluar', function () {
    return view('persuratan.surat-keluar');
})->middleware(['auth', 'permission:surat_keluar,read']);

Route::get('/persuratan/disposisi', function () {
    return view('persuratan.disposisi');
})->middleware(['auth', 'permission:disposisi,read']);

Route::get('/persuratan/arsip-surat', function () {
    return view('persuratan.arsip-surat');
})->middleware(['auth', 'permission:arsip_surat,read']);

// ---------------- Pengaduan ----------------
Route::get('/pengaduan/pengaduan', function () {
    return view('pengaduan.index');
})->middleware(['auth', 'permission:pengaduan_data,read']);


// ---------------- Kepegawaian ----------------
Route::get('/kepegawaian/data-pegawai', function () {
    return view('kepegawaian.data-pegawai');
})->middleware(['auth', 'permission:data_pegawai,read']);

Route::get('/kepegawaian/pns', function () {
    return view('kepegawaian.pns');
})->middleware(['auth', 'permission:pegawai_pns,read']);

Route::get('/kepegawaian/pppk', function () {
    return view('kepegawaian.pppk');
})->middleware(['auth', 'permission:pegawai_pppk,read']);

Route::get('/kepegawaian/jabatan', function () {
    return view('kepegawaian.jabatan');
})->middleware(['auth', 'permission:jabatan,read']);

Route::get('/kepegawaian/unit-ruangan', function () {
    return view('kepegawaian.unit-ruangan');
})->middleware(['auth', 'permission:unit_ruangan,read']);

Route::get('/kepegawaian/riwayat', function () {
    return view('kepegawaian.riwayat.index');
})->middleware(['auth', 'permission:riwayat_pegawai,read']);

Route::get('/kepegawaian/riwayat/pendidikan', function () {
    return view('kepegawaian.riwayat.pendidikan');
})->middleware(['auth', 'permission:riwayat_pendidikan,read']);

Route::get('/kepegawaian/riwayat/pangkat-golongan', function () {
    return view('kepegawaian.riwayat.pangkat-golongan');
})->middleware(['auth', 'permission:riwayat_pangkat,read']);

Route::get('/kepegawaian/riwayat/mutasi', function () {
    return view('kepegawaian.riwayat.mutasi');
})->middleware(['auth', 'permission:riwayat_mutasi,read']);

Route::get('/kepegawaian/riwayat/pelatihan', function () {
    return view('kepegawaian.riwayat.pelatihan');
})->middleware(['auth', 'permission:riwayat_pelatihan,read']);

Route::get('/kepegawaian/legalitas/sip', function () {
    return view('kepegawaian.legalitas.sip');
})->middleware(['auth', 'permission:legalitas_sip,read']);

Route::get('/kepegawaian/legalitas/str', function () {
    return view('kepegawaian.legalitas.str');
})->middleware(['auth', 'permission:legalitas_str,read']);

// ---------------- Logs ----------------
Route::get('/logs', function () {
    return view('logs.index');
})->middleware(['auth', 'permission:log_aktivitas,read']);

Route::get('/apps', function () {
    return view('apps');
})->name('apps')->middleware('auth');

Route::get('/api/wilayah/{type}/{id?}', function (string $type, ?string $id = null) {
    $allowed = ['provinces', 'regencies', 'districts', 'villages'];
    if (!in_array($type, $allowed, true)) {
        return response()->json(['data' => ['data' => []]], 404);
    }

    $base = 'https://wilayah.id/api';
    $url = $type === 'provinces'
        ? "{$base}/provinces.json"
        : "{$base}/{$type}/{$id}.json";

    try {
        $response = Http::timeout(10)->get($url);
        if (!$response->successful()) {
            return response()->json(['data' => ['data' => []]], $response->status());
        }
        return response()->json($response->json());
    } catch (\Throwable $e) {
        return response()->json(['data' => ['data' => []]], 500);
    }
})->middleware('auth');

Route::get('/apps/launch/{app}', function (Request $request, string $app) {
    $app = strtolower($app);
    if (in_array($app, ['inventaris', 'jaringan', 'monitoring'], true)) {
        $app = 'master-data';
    }
    if (in_array($app, ['manajemen-pengguna', 'pengguna'], true)) {
        $app = 'manajemen-pengguna';
    }
    if ($app === 'setting') {
        $app = 'integrasi';
    }

    $request->session()->put('active_app', $app);
    return redirect('/dashboard');
    $apps = [
        'helpdesk' => [
            ['menu' => 'dashboard', 'url' => url('/dashboard')],
            ['menu' => 'helpdesk', 'url' => url('/helpdesk')],
            ['menu' => 'laporan', 'url' => url('/laporan')],
        ],
        'master-data' => [
            ['menu' => 'perangkat', 'url' => url('/perangkat')],
            ['menu' => 'ruangan', 'url' => url('/ruangan')],
            ['menu' => 'pj_ruangan', 'url' => url('/pj-ruangan')],
            ['menu' => 'roles', 'url' => url('/roles')],
            ['menu' => 'ip_address', 'url' => url('/ip-address')],
            ['menu' => 'isp', 'url' => url('/isp')],
            ['menu' => 'cctv', 'url' => url('/cctv')],
        ],
        'persuratan' => [
            ['menu' => 'surat_masuk', 'url' => url('/persuratan/surat-masuk')],
            ['menu' => 'surat_keluar', 'url' => url('/persuratan/surat-keluar')],
            ['menu' => 'disposisi', 'url' => url('/persuratan/disposisi')],
            ['menu' => 'arsip_surat', 'url' => url('/persuratan/arsip-surat')],
        ],
        'pengaduan' => [
            ['menu' => 'pengaduan_data', 'url' => url('/pengaduan/pengaduan')],
        ],
        'kepegawaian' => [
            ['menu' => 'data_pegawai', 'url' => url('/kepegawaian/data-pegawai')],
            ['menu' => 'pegawai_pns', 'url' => url('/kepegawaian/pns')],
            ['menu' => 'pegawai_pppk', 'url' => url('/kepegawaian/pppk')],
            ['menu' => 'jabatan', 'url' => url('/kepegawaian/jabatan')],
            ['menu' => 'riwayat_pegawai', 'url' => url('/kepegawaian/riwayat')],
            ['menu' => 'riwayat_pendidikan', 'url' => url('/kepegawaian/riwayat/pendidikan')],
            ['menu' => 'riwayat_pangkat', 'url' => url('/kepegawaian/riwayat/pangkat-golongan')],
            ['menu' => 'riwayat_mutasi', 'url' => url('/kepegawaian/riwayat/mutasi')],
            ['menu' => 'riwayat_pelatihan', 'url' => url('/kepegawaian/riwayat/pelatihan')],
            ['menu' => 'legalitas_sip', 'url' => url('/kepegawaian/legalitas/sip')],
            ['menu' => 'legalitas_str', 'url' => url('/kepegawaian/legalitas/str')],
        ],
        'manajemen-pengguna' => [
            ['menu' => 'profil', 'url' => url('/profile'), 'admin_only' => true],
            ['menu' => 'pengguna', 'url' => url('/pengguna'), 'admin_only' => true],
            ['menu' => 'peran_pengguna', 'url' => url('/peran-pengguna'), 'admin_only' => true],
            ['menu' => 'hak_akses', 'url' => url('/hak-akses'), 'admin_only' => true],
        ],
        'integrasi' => [
            ['menu' => 'wa_gateway', 'url' => url('/whatsapp-gateway'), 'admin_only' => true],
            ['menu' => 'log_aktivitas', 'url' => url('/logs'), 'admin_only' => true],
        ],
    ];

    if (!isset($apps[$app])) {
        abort(404);
    }

    $user = auth()->user();
    $isAdmin = (bool) ($user->is_admin ?? false);
    $can = function (string $menu) use ($user) {
        return \App\Support\Permission::can($user, $menu, 'read');
    };

    foreach ($apps[$app] as $item) {
        if (($item['admin_only'] ?? false) && !$isAdmin) {
            continue;
        }
        if ($isAdmin || $can($item['menu'])) {
            $request->session()->put('active_app', $app);
            return redirect()->to($item['url']);
        }
    }

    return redirect()->route('apps')->with('error', 'Anda tidak memiliki akses ke aplikasi tersebut.');
})->middleware('auth');

$roomCategories = [
    'Rawat Jalan' => 'RJ',
    'Rawat Inap' => 'RI',
    'Ruang Khusus' => 'RK',
    'Penunjang Medis' => 'PM',
    'Administrasi' => 'AM',
    'Penunjang Umum' => 'PU',
    'Sanitasi & Limbah' => 'SL',
];

if (!function_exists('generateRoomCode')) {
    function generateRoomCode(string $category, array $roomCategories): string
    {
        if (!isset($roomCategories[$category])) {
            abort(422, 'Kategori ruangan tidak valid.');
        }

        $prefix = $roomCategories[$category];
        $last = Room::where('room_id', 'like', $prefix . '-%')
            ->orderByDesc('room_id')
            ->first();

        $next = 1;
        if ($last && preg_match('/' . preg_quote($prefix, '/') . '-(\d{2})$/', $last->room_id, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return sprintf('%s-%02d', $prefix, $next);
    }
}

if (!function_exists('hakAksesMenuGroups')) {
    function hakAksesMenuGroups(): array
    {
        return [
            'Umum' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'actions' => ['read']],
                ['key' => 'laporan', 'label' => 'Laporan', 'actions' => ['read']],
                ['key' => 'logout', 'label' => 'Logout', 'actions' => ['read']],
            ],
            'Helpdesk' => [
                ['key' => 'helpdesk', 'label' => 'Tiket', 'actions' => ['read','create','update','delete']],
            ],
            'Persuratan' => [
                ['key' => 'surat_masuk', 'label' => 'Surat Masuk', 'actions' => ['read','create','update','delete']],
                ['key' => 'surat_keluar', 'label' => 'Surat Keluar', 'actions' => ['read','create','update','delete']],
                ['key' => 'disposisi', 'label' => 'Disposisi', 'actions' => ['read','create','update','delete']],
                ['key' => 'arsip_surat', 'label' => 'Arsip Surat', 'actions' => ['read','create','update','delete']],
            ],
            'Pengaduan' => [
                ['key' => 'pengaduan_data', 'label' => 'Pengaduan', 'actions' => ['read','create','update','delete']],
            ],
            'Pengajuan' => [
                ['key' => 'pengajuan', 'label' => 'Pengajuan', 'actions' => ['read','create','update','delete']],
            ],
            'Master Data' => [
                ['key' => 'ip_address', 'label' => 'IP Address', 'actions' => ['read','create','update','delete']],
                ['key' => 'perangkat', 'label' => 'Perangkat', 'actions' => ['read','create','update','delete']],
                ['key' => 'isp', 'label' => 'ISP', 'actions' => ['read','create','update','delete']],
                ['key' => 'cctv', 'label' => 'CCTV', 'actions' => ['read','create','update','delete']],
                ['key' => 'ruangan', 'label' => 'Ruangan', 'actions' => ['read','create','update','delete']],
                ['key' => 'pj_ruangan', 'label' => 'PJ Ruangan', 'actions' => ['read','create','update','delete']],
                ['key' => 'roles', 'label' => 'Peran', 'actions' => ['read','create','update','delete']],
            ],
            'Kepegawaian' => [
                ['key' => 'data_pegawai', 'label' => 'Data Pegawai', 'actions' => ['read','create','update','delete']],
                ['key' => 'pegawai_pns', 'label' => 'PNS', 'actions' => ['read','create','update','delete']],
                ['key' => 'pegawai_pppk', 'label' => 'PPPK', 'actions' => ['read','create','update','delete']],
                ['key' => 'jabatan', 'label' => 'Jabatan Pegawai', 'actions' => ['read','create','update','delete']],
                ['key' => 'riwayat_pegawai', 'label' => 'Riwayat Pegawai', 'actions' => ['read','create','update','delete']],
                ['key' => 'riwayat_pendidikan', 'label' => 'Pendidikan', 'actions' => ['read','create','update','delete']],
                ['key' => 'riwayat_pangkat', 'label' => 'Pangkat / Golongan', 'actions' => ['read','create','update','delete']],
                ['key' => 'riwayat_mutasi', 'label' => 'Mutasi', 'actions' => ['read','create','update','delete']],
                ['key' => 'riwayat_pelatihan', 'label' => 'Pelatihan', 'actions' => ['read','create','update','delete']],
                ['key' => 'legalitas_sip', 'label' => 'SIP', 'actions' => ['read','create','update','delete']],
                ['key' => 'legalitas_str', 'label' => 'STR', 'actions' => ['read','create','update','delete']],
            ],
            'Pengguna' => [
                ['key' => 'profil', 'label' => 'Profil', 'actions' => ['read','update']],
                ['key' => 'pengguna', 'label' => 'Pengguna', 'actions' => ['read','create','update','delete']],
                ['key' => 'peran_pengguna', 'label' => 'Peran Pengguna', 'actions' => ['read','create','update','delete']],
                ['key' => 'hak_akses', 'label' => 'Hak Akses', 'actions' => ['read','update']],
            ],
            'Setting' => [
                ['key' => 'wa_gateway', 'label' => 'WA Gateway', 'actions' => ['read','update']],
                ['key' => 'log_aktivitas', 'label' => 'Log Aktivitas', 'actions' => ['read']],
            ],
        ];
    }
}

// ---------------- Pengajuan ----------------
Route::get('/pengajuan', function () {
    return view('pengajuan.index');
})->name('pengajuan.index')->middleware(['auth', 'permission:pengajuan,read']);

// ---------------- Pengguna ----------------
Route::get('/pengguna', function (Request $request) {
    $search = $request->query('q');
    $users = User::query()
        ->with(['room', 'role'])
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        })
        ->orderBy('name')
        ->paginate(10)
        ->withQueryString();

    return view('users.index', compact('users', 'search'));
})->name('users.index')->middleware(['auth', 'admin']);

Route::get('/peran-pengguna', function (Request $request) {
    $search = trim((string) $request->query('q', ''));

    $peranPengguna = DB::table('users as u')
        ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
        ->whereNotNull('u.role_id')
        ->select([
            'u.id',
            'u.id as user_id',
            'u.role_id',
            'r.description as keterangan',
            'u.name as user_name',
            'u.username as user_username',
            'r.name as role_name',
        ])
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                    ->orWhere('u.username', 'like', "%{$search}%")
                    ->orWhere('r.name', 'like', "%{$search}%")
                    ->orWhere('r.description', 'like', "%{$search}%");
            });
        })
        ->orderBy('u.id')
        ->paginate(10)
        ->withQueryString();

    return view('peran.index', compact('peranPengguna', 'search'));
})->name('peran.index')->middleware(['auth', 'admin']);

Route::get('/peran/create', function () {
    $users = DB::table('users')
        ->select('id', 'name', 'username')
        ->whereNull('role_id')
        ->orderBy('name')
        ->get();

    if ($users->isEmpty()) {
        return redirect()
            ->route('peran.index')
            ->withErrors(['user_id' => 'Semua pengguna sudah memiliki peran. Silakan ubah lewat menu Edit.']);
    }

    $roles = DB::table('roles')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    $rooms = DB::table('rooms')
        ->select('id', 'name', 'room_id')
        ->orderBy('name')
        ->get();

    return view('peran.create', compact('users', 'roles', 'rooms'));
})->name('peran.create')->middleware(['auth', 'admin']);

Route::post('/peran', function (Request $request) {
    $data = $request->validate([
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'role_id' => ['required', 'integer', 'exists:roles,id'],
    ]);

    $existingUser = DB::table('users')
        ->select('id', 'role_id')
        ->where('id', $data['user_id'])
        ->first();

    if (!$existingUser) {
        return back()->withErrors(['user_id' => 'Pengguna tidak ditemukan.'])->withInput();
    }

    if (!is_null($existingUser->role_id)) {
        return redirect()
            ->route('peran.edit', peran_token_encode((string) $existingUser->id))
            ->withErrors(['user_id' => 'Pengguna sudah memiliki peran. Silakan ubah pada form edit.']);
    }

    $roleName = Str::lower(trim((string) DB::table('roles')->where('id', $data['role_id'])->value('name')));
    $needsRoom = in_array($roleName, ['kepala', 'kepala ruang', 'petugas'], true);
    if ($needsRoom) {
        $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
        ]);
    }

    $updated = DB::table('users')
        ->where('id', $data['user_id'])
        ->update([
            'role_id' => $data['role_id'],
            'updated_at' => now(),
        ]);

    if (!$updated) {
        return back()
            ->withErrors(['user_id' => 'Pengguna sudah memiliki peran. Silakan gunakan menu Edit.'])
            ->withInput();
    }

    if ($needsRoom) {
        $roomId = (int) $request->input('room_id');
        if (in_array($roleName, ['kepala', 'kepala ruang'], true)) {
            DB::table('room_petugas')
                ->where('room_id', $roomId)
                ->where('is_kepala', true)
                ->update(['is_kepala' => false]);
        }

        DB::table('room_petugas')->where('user_id', $data['user_id'])->delete();

        DB::table('room_petugas')->insert([
            'user_id' => $data['user_id'],
            'room_id' => $roomId,
            'is_kepala' => in_array($roleName, ['kepala', 'kepala ruang'], true),
        ]);
    } else {
        DB::table('room_petugas')->where('user_id', $data['user_id'])->delete();
    }

    return redirect()->route('peran.index')->with('success', 'Peran pengguna berhasil ditambahkan.');
})->name('peran.store')->middleware(['auth', 'admin']);

Route::get('/peran/{id}/edit', function (int $id) {
    return redirect()->route('peran.edit', peran_token_encode((string) $id));
})->whereNumber('id')->middleware(['auth', 'admin']);

Route::get('/peran/{token}/edit', function (string $token) {
    try {
        $id = peran_token_decode($token);
    } catch (\Throwable $e) {
        abort(404);
    }
    $peran = DB::table('users as u')
        ->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
        ->where('u.id', $id)
        ->select([
            'u.id',
            'u.id as user_id',
            'u.role_id',
            'r.description as keterangan',
        ])
        ->first();

    if (!$peran) {
        abort(404);
    }

    $users = DB::table('users')
        ->select('id', 'name', 'username')
        ->orderBy('name')
        ->get();

    $roles = DB::table('roles')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    $rooms = DB::table('rooms')
        ->select('id', 'name', 'room_id')
        ->orderBy('name')
        ->get();
    $selectedRoomId = DB::table('room_petugas')
        ->where('user_id', $peran->user_id)
        ->where('is_kepala', true)
        ->value('room_id');

    return view('peran.edit', compact('peran', 'users', 'roles', 'rooms', 'selectedRoomId', 'token'));
})->name('peran.edit')->middleware(['auth', 'admin']);

Route::put('/peran/{id}', function (Request $request, string $id) {
    $data = $request->validate([
        'user_id' => ['required', 'integer', Rule::in([(int) $id])],
        'role_id' => ['required', 'integer', 'exists:roles,id'],
    ], [
        'user_id.in' => 'Pengguna pada data ini tidak bisa diubah. Silakan edit peran pengguna yang sesuai.',
    ]);

    $roleName = Str::lower(trim((string) DB::table('roles')->where('id', $data['role_id'])->value('name')));
    $needsRoom = in_array($roleName, ['kepala', 'kepala ruang', 'petugas'], true);
    if ($needsRoom) {
        $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
        ]);
    }

    DB::table('users')
        ->where('id', $id)
        ->update([
            'role_id' => $data['role_id'],
            'updated_at' => now(),
        ]);

    if ($needsRoom) {
        $roomId = (int) $request->input('room_id');
        if (in_array($roleName, ['kepala', 'kepala ruang'], true)) {
            DB::table('room_petugas')
                ->where('room_id', $roomId)
                ->where('is_kepala', true)
                ->update(['is_kepala' => false]);
        }

        DB::table('room_petugas')->where('user_id', $id)->delete();

        DB::table('room_petugas')->insert([
            'user_id' => (int) $id,
            'room_id' => $roomId,
            'is_kepala' => in_array($roleName, ['kepala', 'kepala ruang'], true),
        ]);
    } else {
        DB::table('room_petugas')->where('user_id', $id)->delete();
    }

    return redirect()->route('peran.index')->with('success', 'Peran pengguna berhasil diperbarui.');
})->name('peran.update')->middleware(['auth', 'admin']);

Route::delete('/peran/{id}', function (string $id) {
    $user = DB::table('users')
        ->select('id', 'role_id')
        ->where('id', $id)
        ->first();

    if (!$user) {
        return redirect()->route('peran.index')->withErrors(['user_id' => 'Data pengguna tidak ditemukan.']);
    }

    if (is_null($user->role_id)) {
        return redirect()->route('peran.index')->withErrors(['user_id' => 'Pengguna belum memiliki peran.']);
    }

    DB::table('users')
        ->where('id', $id)
        ->update([
            'role_id' => null,
            'updated_at' => now(),
        ]);

    DB::table('room_petugas')->where('user_id', $id)->delete();

    return redirect()->route('peran.index')->with('success', 'Peran pengguna berhasil dihapus.');
})->name('peran.destroy')->middleware(['auth', 'admin']);

Route::get('/pengguna/tambah-link', function (Request $request) {
    $code = bin2hex(random_bytes(16));
    Cache::put('user_invite_' . $code, [
        'valid' => true,
    ], now()->addMinutes(15));
    $link = url('/pengguna/tambah/' . $code);
    if (request()->expectsJson()) {
        return response()->json(['link' => $link]);
    }
    return redirect()->to($link);
})->name('users.invite')->middleware(['auth', 'admin']);

Route::post('/pengguna/tambah-link/kirim', function (Request $request) {
    $data = $request->validate([
        'phone' => ['required', 'string'],
    ]);

    $rawPhone = normalize_id_phone($data['phone'] ?? null);
    if (!is_valid_id_phone($rawPhone)) {
        return response()->json([
            'ok' => false,
            'message' => 'No HP tidak valid. Gunakan format 62xxxxxxxxxx atau 08xxxxxxxxxx.',
        ], 422);
    }

    $code = bin2hex(random_bytes(16));
    Cache::put('user_invite_' . $code, [
        'valid' => true,
    ], now()->addMinutes(15));

    $link = url('/pengguna/tambah/' . $code);
    $message = "Silakan isi form pendaftaran pengguna baru melalui tautan berikut:\n{$link}";

    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $response = Http::withHeaders([
            'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
        ])->post($baseUrl . '/send', [
            'phone' => $rawPhone,
            'message' => $message,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengirim link ke WhatsApp.',
            ], 500);
        }
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Gateway WhatsApp belum berjalan.',
        ], 500);
    }

    return response()->json([
        'ok' => true,
        'message' => 'Link form berhasil dikirim ke WhatsApp.',
        'link' => $link,
        'phone' => $rawPhone,
    ]);
})->name('users.invite.send')->middleware(['auth', 'admin']);

Route::get('/pengguna/tambah', function () {
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('users.create', compact('rooms'));
})->name('users.create')->middleware(['auth', 'admin']);

Route::get('/pengguna/tambah/{token}', function (string $token) {
    $invite = Cache::get('user_invite_' . $token);
    if (!$invite || empty($invite['valid'])) {
        abort(403);
    }
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('users.create', [
        'rooms' => $rooms,
        'invite_code' => $token,
    ]);
})->name('users.create.invite');

Route::post('/pengguna/otp', function (Request $request) {
    if (!filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
        return response()->json(['message' => 'OTP sedang dinonaktifkan.'], 400);
    }
    $data = $request->validate([
        'phone' => ['required', 'string'],
        'invite_code' => ['nullable', 'string'],
    ]);

    $normalizedPhone = normalize_id_phone($data['phone'] ?? null);
    if (!is_valid_id_phone($normalizedPhone)) {
        return response()->json(['message' => 'No telepon harus format 628xxx atau 08xxx.'], 422);
    }
    if (!Auth::check()) {
        $invite = !empty($data['invite_code']) ? Cache::get('user_invite_' . $data['invite_code']) : null;
        if (!$invite || empty($invite['valid'])) {
            return response()->json(['message' => 'Akses tidak valid.'], 403);
        }
    }

    $length = (int) (env('OTP_LENGTH') ?: 6);
    $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

    $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
    $message = "Kode OTP SIMTI RSUDZM: {$code}. Berlaku {$expireMinutes} menit.";
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $response = Http::withHeaders([
            'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
        ])->post($baseUrl . '/send', [
            'phone' => $normalizedPhone,
            'message' => $message,
        ]);
        if (!$response->successful()) {
            return response()->json(['message' => 'Gagal mengirim OTP.'], 500);
        }
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Gateway belum berjalan.'], 500);
    }

    $request->session()->put([
        'otp_code' => $code,
        'otp_phone' => $normalizedPhone,
        'otp_expires' => now()->addMinutes($expireMinutes)->timestamp,
    ]);

    return response()->json(['message' => 'OTP berhasil dikirim.']);
})->name('users.otp');

Route::post('/pengguna', function (Request $request) {
    $otpEnabled = filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
        'is_admin' => ['nullable', 'boolean'],
        'phone' => ['required', 'string'],
        'otp_code' => $otpEnabled ? ['required', 'digits:' . (env('OTP_LENGTH') ?: 6)] : ['nullable'],
        'password' => ['required', 'string', 'min:8'],
    ], [
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

    $data['phone'] = normalize_id_phone($data['phone'] ?? null);
    if (!is_valid_id_phone($data['phone'])) {
        return back()->withErrors(['phone' => 'No telepon harus format 628xxx atau 08xxx.'])->withInput();
    }
    if (User::where('phone', $data['phone'])->exists()) {
        return back()->withErrors(['phone' => 'No HP Telah Terdaftar, Silahkan Hubungi Admin'])->withInput();
    }

    if ($otpEnabled) {
        $sessionCode = $request->session()->get('otp_code');
        $sessionPhone = $request->session()->get('otp_phone');
        $sessionExpires = $request->session()->get('otp_expires');
        if (!$sessionCode || !$sessionPhone || !$sessionExpires) {
            return back()->withErrors(['otp_code' => 'Kode OTP belum dikirim atau sudah kadaluarsa.'])->withInput();
        }
        if ((int) $sessionExpires < now()->timestamp) {
            $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
            return back()->withErrors(['otp_code' => "Kode OTP sudah kadaluarsa (maksimal {$expireMinutes} menit)."])->withInput();
        }
        if ($sessionPhone !== $data['phone']) {
            return back()->withErrors(['phone' => 'No telepon tidak sesuai dengan OTP yang dikirim.'])->withInput();
        }
        if ($sessionCode !== $data['otp_code']) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid.'])->withInput();
        }
    }

    $data['is_admin'] = filter_var($data['is_admin'] ?? false, FILTER_VALIDATE_BOOLEAN);
    unset($data['otp_code']);
    $data['is_verified'] = false;
    $user = new User($data);

    if (Schema::hasColumn('users', 'email')) {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $data['username']));
        $email = $base . '@simti.xyz';
        $i = 1;
        while (User::where('email', $email)->exists()) {
            $email = $base . "+$i@simti.xyz";
            $i++;
        }
        $user->email = $email;
    }

    $user->save();

    if ($otpEnabled) {
        $request->session()->forget(['otp_code', 'otp_phone', 'otp_expires']);
    }
    return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
})->name('users.store')->middleware(['auth', 'admin']);

Route::post('/pengguna/tambah/{kode}', function (Request $request, string $kode) {
    $invite = Cache::get('user_invite_' . $kode);
    if (!$invite || empty($invite['valid'])) {
        abort(403);
    }
    $request->merge(['invite_code' => $kode]);
    $otpEnabled = filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
        'phone' => ['required', 'string'],
        'otp_code' => $otpEnabled ? ['required', 'digits:' . (env('OTP_LENGTH') ?: 6)] : ['nullable'],
        'password' => ['required', 'string', 'min:8'],
        'invite_code' => ['required', 'string'],
    ], [
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

    $data['phone'] = normalize_id_phone($data['phone'] ?? null);
    if (!is_valid_id_phone($data['phone'])) {
        return back()->withErrors(['phone' => 'No telepon harus format 628xxx atau 08xxx.'])->withInput();
    }
    if (User::where('phone', $data['phone'])->exists()) {
        return back()->withErrors(['phone' => 'No HP Telah Terdaftar, Silahkan Hubungi Admin'])->withInput();
    }

    if ($otpEnabled) {
        $sessionCode = $request->session()->get('otp_code');
        $sessionPhone = $request->session()->get('otp_phone');
        $sessionExpires = $request->session()->get('otp_expires');
        if (!$sessionCode || !$sessionPhone || !$sessionExpires) {
            return back()->withErrors(['otp_code' => 'Kode OTP belum dikirim atau sudah kadaluarsa.'])->withInput();
        }
        if ((int) $sessionExpires < now()->timestamp) {
            $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
            return back()->withErrors(['otp_code' => "Kode OTP sudah kadaluarsa (maksimal {$expireMinutes} menit)."])->withInput();
        }
        if ($sessionPhone !== $data['phone']) {
            return back()->withErrors(['phone' => 'No telepon tidak sesuai dengan OTP yang dikirim.'])->withInput();
        }
        if ($sessionCode !== $data['otp_code']) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid.'])->withInput();
        }
    }

    $data['role_id'] = null;
    $data['is_admin'] = false;
    unset($data['otp_code'], $data['invite_code']);
    $data['is_verified'] = false;
    $user = new User($data);
    if (Schema::hasColumn('users', 'email')) {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $data['username']));
        $email = $base . '@simti.xyz';
        $i = 1;
        while (User::where('email', $email)->exists()) {
            $email = $base . "+$i@simti.xyz";
            $i++;
        }
        $user->email = $email;
    }
    $user->save();

    if ($otpEnabled) {
        $request->session()->forget(['otp_code', 'otp_phone', 'otp_expires']);
    }
    Cache::forget('user_invite_' . $kode);
    return redirect('/auth/login')->with('success', 'Pendaftaran berhasil. Silakan login.');
});

Route::get('/pengguna/{encoded}/edit', function (string $encoded) {
    try {
        $username = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $user = User::findOrFail($username);
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('users.edit', ['user' => $user, 'encoded' => $encoded, 'rooms' => $rooms]);
})->name('users.edit')->middleware(['auth', 'admin']);

Route::put('/pengguna/{encoded}', function (Request $request, string $encoded) {
    try {
        $username = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $user = User::findOrFail($username);

    $otpEnabled = filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->username, 'username')],
        'is_admin' => ['nullable', 'boolean'],
        'phone' => ['required', 'string'],
        'otp_code' => $otpEnabled ? ['nullable', 'digits:' . (env('OTP_LENGTH') ?: 6)] : ['nullable'],
        'password' => ['nullable', 'string', 'min:8'],
    ], [
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

    $data['phone'] = normalize_id_phone($data['phone'] ?? null);
    if (!is_valid_id_phone($data['phone'])) {
        return back()->withErrors(['phone' => 'No telepon harus format 628xxx atau 08xxx.'])->withInput();
    }
    $phoneExists = User::where('phone', $data['phone'])
        ->where('username', '!=', $user->username)
        ->exists();
    if ($phoneExists) {
        return back()->withErrors(['phone' => 'No HP Telah Terdaftar, Silahkan Hubungi Admin'])->withInput();
    }

    if (empty($data['password'])) {
        unset($data['password']);
    }
    $data['is_admin'] = filter_var($data['is_admin'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $wasAdminUser = (bool) ($user->is_admin ?? false);
    $willBeAdmin = $data['is_admin'];
    if ($wasAdminUser && !$willBeAdmin && auth()->check() && auth()->user()->username === $user->username) {
        return back()->withInput()->with('error', 'Tidak bisa menonaktifkan admin pada akun yang sedang login.');
    }
    if ($wasAdminUser && !$willBeAdmin) {
        $adminCount = User::where('is_admin', true)->count();
        if ($adminCount <= 1) {
            return back()->withInput()->with('error', 'Tidak bisa menonaktifkan admin terakhir.');
        }
    }
    $phoneChanged = isset($data['phone']) && $data['phone'] !== $user->phone;
    if ($phoneChanged && $otpEnabled) {
        $sessionCode = $request->session()->get('otp_code');
        $sessionPhone = $request->session()->get('otp_phone');
        $sessionExpires = $request->session()->get('otp_expires');
        if (!$sessionCode || !$sessionPhone || !$sessionExpires) {
            return back()->withErrors(['otp_code' => 'Kode OTP belum dikirim atau sudah kadaluarsa.'])->withInput();
        }
    if ((int) $sessionExpires < now()->timestamp) {
        $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
        return back()->withErrors(['otp_code' => "Kode OTP sudah kadaluarsa (maksimal {$expireMinutes} menit)."])->withInput();
    }
        if ($sessionPhone !== $data['phone']) {
            return back()->withErrors(['phone' => 'No telepon tidak sesuai dengan OTP yang dikirim.'])->withInput();
        }
        if ($sessionCode !== ($data['otp_code'] ?? null)) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid.'])->withInput();
        }
    }

    $user->update($data);

    return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
})->name('users.update')->middleware(['auth', 'admin']);

Route::delete('/pengguna/{encoded}', function (string $encoded) {
    try {
        $username = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $user = User::findOrFail($username);
    $user->delete();
    return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
})->name('users.destroy')->middleware(['auth', 'admin']);

Route::post('/pengguna/{encoded}/validasi', function (string $encoded, Request $request) {
    if (!auth()->check() || !(auth()->user()->is_admin ?? false)) {
        abort(403);
    }
    try {
        $username = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $user = User::findOrFail($username);
    $user->is_verified = true;
    $user->save();
    if ($user->phone) {
        try {
            $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
            Http::withHeaders([
                'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
            ])->post($baseUrl . '/send', [
                'phone' => $user->phone,
                'message' => "Terima kasih telah mendaftar di SIMTI RSUDZM.\nPendaftaran Anda telah berhasil diverifikasi oleh Admin.\nSilakan masuk ke sistem melalui tautan berikut:\nhttps://rsudzm.simti.xyz/auth/login",
            ]);
        } catch (\Throwable $e) {
            // abaikan jika gagal kirim WA
        }
    }
    return redirect()->route('users.index')->with('success', 'Pengguna berhasil divalidasi.');
})->name('users.verify')->middleware(['auth', 'admin']);

// ---------------- Ruangan ----------------
Route::get('/ruangan', function (Request $request) use ($roomCategories) {
    $search = $request->query('q');
    $kategori = $request->query('kategori');
    $rooms = Room::query()
        ->when($search, function ($query, $search) {
            $query->where('room_id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
        })
        ->when($kategori, function ($query, $kategori) {
            $query->where('kategori', $kategori);
        })
        ->orderBy('room_id')
        ->paginate(20)
        ->withQueryString();

    $categories = array_keys($roomCategories);

    return view('ruangan.index', [
        'rooms' => $rooms,
        'search' => $search,
        'selectedKategori' => $kategori,
        'categories' => $categories,
    ]);
})->name('rooms.index')->middleware(['auth', 'permission:ruangan,read']);

Route::get('/pj-ruangan', function () {
    return view('pj-ruangan.index');
})->name('rooms.pj')->middleware(['auth', 'permission:pj_ruangan,read']);

// ---------------- Jabatan (Master Data) ----------------
Route::get('/jabatan', function (Request $request) {
    $search = $request->query('q');
    $jabatans = DB::table('jabatans')
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
        ->orderBy('name')
        ->paginate(20)
        ->withQueryString();

    return view('jabatan.index', compact('jabatans', 'search'));
})->name('jabatan.index')->middleware(['auth', 'permission:jabatan,read']);

Route::get('/jabatan/create', function () {
    return view('jabatan.create');
})->name('jabatan.create')->middleware(['auth', 'permission:jabatan,create']);

Route::post('/jabatan', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:150', 'unique:jabatans,name'],
        'description' => ['nullable', 'string'],
    ]);

    DB::table('jabatans')->insert([
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil ditambahkan.');
})->name('jabatan.store')->middleware(['auth', 'permission:jabatan,create']);

Route::get('/jabatan/{id}/edit', function (string $id) {
    $jabatan = DB::table('jabatans')->where('id', $id)->first();
    if (!$jabatan) {
        abort(404);
    }
    return view('jabatan.edit', compact('jabatan'));
})->name('jabatan.edit')->middleware(['auth', 'permission:jabatan,update']);

Route::put('/jabatan/{id}', function (Request $request, string $id) {
    $jabatan = DB::table('jabatans')->where('id', $id)->first();
    if (!$jabatan) {
        abort(404);
    }
    $data = $request->validate([
        'name' => ['required', 'string', 'max:150', Rule::unique('jabatans', 'name')->ignore($id)],
        'description' => ['nullable', 'string'],
    ]);

    DB::table('jabatans')->where('id', $id)->update([
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'updated_at' => now(),
    ]);

    return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
})->name('jabatan.update')->middleware(['auth', 'permission:jabatan,update']);

Route::delete('/jabatan/{id}', function (string $id) {
    $jabatan = DB::table('jabatans')->where('id', $id)->first();
    if (!$jabatan) {
        abort(404);
    }
    DB::table('jabatans')->where('id', $id)->delete();
    return redirect()->route('jabatan.index')->with('success', 'Jabatan berhasil dihapus.');
})->name('jabatan.destroy')->middleware(['auth', 'permission:jabatan,delete']);

// ---------------- Peran (Master Data) ----------------
Route::get('/roles', function (Request $request) {
    $search = $request->query('q');
    $roles = DB::table('roles')
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
        ->orderBy('name')
        ->paginate(20)
        ->withQueryString();

    return view('roles.index', compact('roles', 'search'));
})->name('roles.index')->middleware(['auth', 'permission:roles,read']);

Route::get('/roles/create', function () {
    return view('roles.create');
})->name('roles.create')->middleware(['auth', 'permission:roles,create']);

Route::post('/roles', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
        'description' => ['nullable', 'string'],
    ], [
        'name.required' => 'Nama role wajib diisi.',
        'name.unique' => 'Nama role sudah ada, gunakan nama lain.',
    ]);

    DB::table('roles')->insert([
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('roles.index')->with('success', 'Peran berhasil ditambahkan.');
})->name('roles.store')->middleware(['auth', 'permission:roles,create']);

Route::get('/roles/{id}/edit', function (string $id) {
    $role = DB::table('roles')->where('id', $id)->first();
    if (!$role) {
        abort(404);
    }
    return view('roles.edit', compact('role'));
})->name('roles.edit')->middleware(['auth', 'permission:roles,update']);

Route::put('/roles/{id}', function (Request $request, string $id) {
    $role = DB::table('roles')->where('id', $id)->first();
    if (!$role) {
        abort(404);
    }
    $data = $request->validate([
        'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($id)],
        'description' => ['nullable', 'string'],
    ], [
        'name.required' => 'Nama role wajib diisi.',
        'name.unique' => 'Nama role sudah ada, gunakan nama lain.',
    ]);

    DB::table('roles')->where('id', $id)->update([
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'updated_at' => now(),
    ]);

    return redirect()->route('roles.index')->with('success', 'Peran berhasil diperbarui.');
})->name('roles.update')->middleware(['auth', 'permission:roles,update']);

Route::delete('/roles/{id}', function (string $id) {
    $role = DB::table('roles')->where('id', $id)->first();
    if (!$role) {
        abort(404);
    }
    DB::table('roles')->where('id', $id)->delete();
    return redirect()->route('roles.index')->with('success', 'Peran berhasil dihapus.');
})->name('roles.destroy')->middleware(['auth', 'permission:roles,delete']);

Route::get('/ruangan/tambah', function () use ($roomCategories) {
    return view('ruangan.create', ['categories' => $roomCategories]);
})->name('rooms.create')->middleware(['auth', 'permission:ruangan,create']);

Route::post('/ruangan', function (Request $request) use ($roomCategories) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'kategori' => ['required', 'string', Rule::in(array_keys($roomCategories))],
    ]);

    $roomId = generateRoomCode($data['kategori'], $roomCategories);

    Room::create([
        'room_id' => $roomId,
        'kategori' => $data['kategori'],
        'name' => $data['name'],
    ]);

    return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
})->name('rooms.store')->middleware(['auth', 'permission:ruangan,create']);

Route::get('/ruangan/{encoded}/edit', function (string $encoded) use ($roomCategories) {
    try {
        $roomId = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $room = Room::findOrFail($roomId);
    return view('ruangan.edit', ['room' => $room, 'encoded' => $encoded, 'categories' => $roomCategories]);
})->name('rooms.edit')->middleware(['auth', 'permission:ruangan,update']);

Route::put('/ruangan/{encoded}', function (Request $request, string $encoded) use ($roomCategories) {
    try {
        $roomId = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $room = Room::findOrFail($roomId);

    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'kategori' => ['required', 'string', Rule::in(array_keys($roomCategories))],
    ]);

    if ($room->kategori !== $data['kategori']) {
        $data['room_id'] = generateRoomCode($data['kategori'], $roomCategories);
    }

    $room->update($data);

    return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil diperbarui.');
})->name('rooms.update')->middleware(['auth', 'permission:ruangan,update']);

Route::delete('/ruangan/{encoded}', function (string $encoded) {
    try {
        $roomId = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }

    $room = Room::findOrFail($roomId);
    try {
        $room->delete();
    } catch (QueryException $e) {
        if ($e->getCode() === '23000') {
            return redirect()->route('rooms.index')->with('error', 'Ruangan tidak dapat dihapus karena sedang digunakan. Silakan ubah terlebih dahulu.');
        }
        throw $e;
    }

    return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
})->name('rooms.destroy')->middleware(['auth', 'permission:ruangan,delete']);

// ---------------- Hak Akses ----------------
Route::get('/hak-akses', function () {
    $menuGroups = hakAksesMenuGroups();
    $roles = Role::orderBy('id')->get();
    return view('hak-akses.index', compact('menuGroups', 'roles'));
})->name('hakakses.index')->middleware(['auth', 'admin']);

Route::get('/hak-akses/permissions', function (Request $request) {
    $data = $request->validate([
        'role_id' => ['required', 'integer', 'exists:roles,id'],
    ]);

    $menuGroups = hakAksesMenuGroups();
    $menus = collect($menuGroups)->flatten(1)->pluck('key')->values();
    $existing = RolePermission::where('role_id', $data['role_id'])->get()->keyBy('menu');

    $permissions = [];
    foreach ($menus as $menu) {
        $row = $existing->get($menu);
        $permissions[$menu] = [
            'can_read' => (bool) ($row->can_read ?? false),
            'can_create' => (bool) ($row->can_create ?? false),
            'can_update' => (bool) ($row->can_update ?? false),
            'can_delete' => (bool) ($row->can_delete ?? false),
        ];
    }

    return response()->json(['permissions' => $permissions]);
})->name('hakakses.permissions')->middleware(['auth', 'admin']);

Route::post('/hak-akses/permissions', function (Request $request) {
    $data = $request->validate([
        'role_id' => ['required', 'integer', 'exists:roles,id'],
        'menu' => ['required', 'string'],
        'action' => ['required', 'in:read,create,update,delete'],
        'value' => ['required', 'boolean'],
    ]);
    $menuGroups = hakAksesMenuGroups();
    $menuKeys = collect($menuGroups)->flatten(1)->pluck('key')->values()->all();
    if (!in_array($data['menu'], $menuKeys, true)) {
        return response()->json(['message' => 'Menu tidak valid.'], 422);
    }

    $permission = RolePermission::firstOrNew([
        'role_id' => $data['role_id'],
        'menu' => $data['menu'],
    ]);

    $field = 'can_' . $data['action'];
    $permission->{$field} = (bool) $data['value'];
    $permission->save();

    return response()->json(['ok' => true]);
})->name('hakakses.permissions.save')->middleware(['auth', 'admin']);

Route::post('/hak-akses/permissions/bulk', function (Request $request) {
    $data = $request->validate([
        'role_id' => ['required', 'integer', 'exists:roles,id'],
        'value' => ['required', 'boolean'],
    ]);
    $menuGroups = hakAksesMenuGroups();
    foreach ($menuGroups as $menus) {
        foreach ($menus as $menu) {
            $actions = $menu['actions'] ?? [];
            RolePermission::updateOrCreate(
                ['role_id' => $data['role_id'], 'menu' => $menu['key']],
                [
                    'can_read' => in_array('read', $actions, true) ? (bool) $data['value'] : false,
                    'can_create' => in_array('create', $actions, true) ? (bool) $data['value'] : false,
                    'can_update' => in_array('update', $actions, true) ? (bool) $data['value'] : false,
                    'can_delete' => in_array('delete', $actions, true) ? (bool) $data['value'] : false,
                ]
            );
        }
    }

    return response()->json(['ok' => true]);
})->name('hakakses.permissions.bulk')->middleware(['auth', 'admin']);

Route::post('/hak-akses/permissions/save', function (Request $request) {
    $data = $request->validate([
        'role_id' => ['required', 'integer', 'exists:roles,id'],
        'permissions' => ['required', 'array'],
    ]);
    $menuGroups = hakAksesMenuGroups();
    foreach ($menuGroups as $menus) {
        foreach ($menus as $menu) {
            $payload = $data['permissions'][$menu['key']] ?? [];
            $actions = $menu['actions'] ?? [];

            RolePermission::updateOrCreate(
                ['role_id' => $data['role_id'], 'menu' => $menu['key']],
                [
                    'can_read' => in_array('read', $actions, true) ? (bool) ($payload['read'] ?? false) : false,
                    'can_create' => in_array('create', $actions, true) ? (bool) ($payload['create'] ?? false) : false,
                    'can_update' => in_array('update', $actions, true) ? (bool) ($payload['update'] ?? false) : false,
                    'can_delete' => in_array('delete', $actions, true) ? (bool) ($payload['delete'] ?? false) : false,
                ]
            );
        }
    }

    return response()->json(['ok' => true]);
})->name('hakakses.permissions.saveall')->middleware(['auth', 'admin']);

// ---------------- CCTV ----------------
Route::get('/cctv', function (Request $request) {
    $status = $request->query('status');
    $q = $request->query('q');

    $cctvs = Cctv::with('room')
        ->when($status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($q, function ($query, $q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('ip', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%")
                    ->orWhereHas('room', function ($r) use ($q) {
                        $r->where('name', 'like', "%{$q}%")
                          ->orWhere('room_id', 'like', "%{$q}%");
                    });
            });
        })
        ->orderBy('id')
        ->paginate(20)
        ->withQueryString();

    return view('cctv.index', compact('cctvs', 'status', 'q'));
})->name('cctv.index')->middleware(['auth', 'permission:cctv,read']);

Route::get('/cctv/tambah', function () {
    $rooms = Room::orderBy('room_id')->get(['room_id', 'name']);
    return view('cctv.create', ['rooms' => $rooms]);
})->name('cctv.create')->middleware(['auth', 'permission:cctv,create']);

Route::post('/cctv/tambah', function (Request $request) {
    $data = $request->validate([
        'lokasi' => ['required', 'string', Rule::exists('rooms', 'room_id')],
        'status' => ['required', Rule::in(['aktif', 'non_aktif'])],
        'keterangan' => ['nullable', 'string'],
        'ip' => ['required', 'ip'],
    ]);

    Cctv::create([
        'room_id' => $data['lokasi'],
        'status' => $data['status'],
        'keterangan' => $data['keterangan'] ?? null,
        'ip' => $data['ip'],
    ]);

    return redirect()->route('cctv.index')->with('success', 'CCTV berhasil ditambahkan.');
})->name('cctv.store')->middleware(['auth', 'permission:cctv,create']);

Route::get('/cctv/{encoded}/edit', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $cctv = Cctv::findOrFail($id);
    $rooms = Room::orderBy('name')->get(['room_id', 'name']);
    return view('cctv.edit', [
        'cctv' => $cctv,
        'rooms' => $rooms,
        'encoded' => $encoded,
    ]);
})->name('cctv.edit')->middleware(['auth', 'permission:cctv,update']);

Route::put('/cctv/{encoded}', function (Request $request, string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $cctv = Cctv::findOrFail($id);
    $data = $request->validate([
        'lokasi' => ['required', 'string', Rule::exists('rooms', 'room_id')],
        'status' => ['required', Rule::in(['aktif', 'non_aktif'])],
        'keterangan' => ['nullable', 'string'],
        'ip' => ['required', 'ip'],
    ]);

    $cctv->update([
        'room_id' => $data['lokasi'],
        'status' => $data['status'],
        'keterangan' => $data['keterangan'] ?? null,
        'ip' => $data['ip'],
    ]);

    return redirect()->route('cctv.index')->with('success', 'CCTV berhasil diperbarui.');
})->name('cctv.update')->middleware(['auth', 'permission:cctv,update']);

Route::delete('/cctv/{encoded}', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $cctv = Cctv::findOrFail($id);
    $cctv->delete();
    return redirect()->route('cctv.index')->with('success', 'CCTV berhasil dihapus.');
})->name('cctv.destroy')->middleware(['auth', 'permission:cctv,delete']);

// ---------------- ISP ----------------
Route::get('/isp', function () {
    $isps = DB::table('isps')
        ->leftJoin('rooms', 'isps.ruang_installasi', '=', 'rooms.id')
        ->select('isps.*', 'rooms.name as room_name', 'rooms.room_id as room_code')
        ->orderBy('isps.nama_isp')
        ->get();
    return view('isp.index', compact('isps'));
})->name('isp.index')->middleware(['auth', 'permission:isp,read']);

Route::get('/isp/tambah', function () {
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('isp.create', compact('rooms'));
})->name('isp.create')->middleware(['auth', 'permission:isp,create']);

Route::post('/isp/tambah', function (Request $request) {
    $data = $request->validate([
        'nama_isp' => ['required', 'string', 'max:255'],
        'no_pelanggan' => ['required', 'string', 'max:100'],
        'jenis_koneksi' => ['required', Rule::in(['fiber','radio'])],
        'bandwidth' => ['required', 'string', 'max:50', 'regex:/^\d+\s?(Mbps|Gbps)$/i'],
        'ip_address' => ['required', 'ip'],
        'ruang_installasi' => ['required', 'integer', 'exists:rooms,id'],
        'pic_isp' => ['required', 'string', 'max:255'],
        'no_telepon' => ['required', 'string', 'regex:/^\d{8,15}$/'],
        'status' => ['required', Rule::in(['aktif','backup'])],
        'keterangan' => ['nullable', 'string'],
    ], [
        'bandwidth.regex' => 'Bandwidth harus berupa angka dengan satuan Mbps atau Gbps. Contoh: 100 Mbps.',
        'no_telepon.regex' => 'No telepon harus berupa angka 8-15 digit.',
    ]);

    DB::table('isps')->insert(array_merge($data, [
        'created_at' => now(),
        'updated_at' => now(),
    ]));

    return redirect()->route('isp.index')->with('success', 'Data ISP berhasil ditambahkan.');
})->name('isp.store')->middleware(['auth', 'permission:isp,create']);

Route::get('/isp/{encoded}/edit', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $isp = DB::table('isps')->where('id', $id)->first();
    if (!$isp) {
        abort(404);
    }
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('isp.edit', ['isp' => $isp, 'encoded' => $encoded, 'rooms' => $rooms]);
})->name('isp.edit')->middleware(['auth', 'permission:isp,update']);

Route::put('/isp/{encoded}', function (Request $request, string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $data = $request->validate([
        'nama_isp' => ['required', 'string', 'max:255'],
        'no_pelanggan' => ['required', 'string', 'max:100'],
        'jenis_koneksi' => ['required', Rule::in(['fiber','radio'])],
        'bandwidth' => ['required', 'string', 'max:50', 'regex:/^\d+\s?(Mbps|Gbps)$/i'],
        'ip_address' => ['required', 'ip'],
        'ruang_installasi' => ['required', 'integer', 'exists:rooms,id'],
        'pic_isp' => ['required', 'string', 'max:255'],
        'no_telepon' => ['required', 'string', 'regex:/^\d{8,15}$/'],
        'status' => ['required', Rule::in(['aktif','backup'])],
        'keterangan' => ['nullable', 'string'],
    ], [
        'bandwidth.regex' => 'Bandwidth harus berupa angka dengan satuan Mbps atau Gbps. Contoh: 100 Mbps.',
        'no_telepon.regex' => 'No telepon harus berupa angka 8-15 digit.',
    ]);

    DB::table('isps')->where('id', $id)->update(array_merge($data, [
        'updated_at' => now(),
    ]));

    return redirect()->route('isp.index')->with('success', 'Data ISP berhasil diperbarui.');
})->name('isp.update')->middleware(['auth', 'permission:isp,update']);

Route::delete('/isp/{encoded}', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    DB::table('isps')->where('id', $id)->delete();
    return redirect()->route('isp.index')->with('success', 'Data ISP berhasil dihapus.');
})->name('isp.destroy')->middleware(['auth', 'permission:isp,delete']);

// ---------------- Helpdesk ----------------
Route::get('/helpdesk', function () {
    $tickets = DB::table('helpdesk_tickets')
        ->leftJoin('rooms', 'rooms.id', '=', 'helpdesk_tickets.room_id')
        ->leftJoin('devices', 'devices.id', '=', 'helpdesk_tickets.device_id')
        ->leftJoin('users', 'users.id', '=', 'helpdesk_tickets.petugas_id')
        ->select(
            'helpdesk_tickets.id',
            'helpdesk_tickets.no_ticket',
            'helpdesk_tickets.tanggal',
            'helpdesk_tickets.pelapor',
            'helpdesk_tickets.kategori',
            'helpdesk_tickets.kendala',
            'helpdesk_tickets.prioritas',
            'helpdesk_tickets.status',
            'helpdesk_tickets.keterangan',
            'rooms.name as room_name',
            'devices.device_name as device_name',
            'users.name as petugas_name'
        )
        ->orderByDesc('helpdesk_tickets.tanggal')
        ->orderByDesc('helpdesk_tickets.id')
        ->get();

    return view('helpdesk.index', [
        'tickets' => $tickets,
    ]);
})->name('helpdesk.index')->middleware(['auth', 'permission:helpdesk,read']);

Route::get('/helpdesk/request', function () {
    return view('helpdesk.request');
})->name('helpdesk.request')->middleware(['auth', 'permission:helpdesk,create']);

Route::get('/helpdesk/tambah-ticket', function () {
    $rooms = \App\Models\Room::query()
        ->select('id', 'name', 'room_id')
        ->orderBy('name')
        ->get();
    $petugasItId = role_id_by_key('petugas_it');
    $petugas = \App\Models\User::query()
        ->select('id', 'name')
        ->when($petugasItId, fn ($q) => $q->where('role_id', $petugasItId))
        ->orderBy('name')
        ->get();

    return view('helpdesk.create', [
        'rooms' => $rooms,
        'petugas' => $petugas,
    ]);
})->name('helpdesk.create')->middleware(['auth', 'permission:helpdesk,create']);

Route::get('/helpdesk/devices', function (Request $request) {
    $data = $request->validate([
        'room_id' => ['required', 'integer', 'exists:rooms,id'],
    ]);

    $room = Room::find($data['room_id']);
    $roomCode = $room?->room_id;

    $devices = Device::query()
        ->when($roomCode, fn($q) => $q->where('room_id', $roomCode))
        ->orderBy('device_name')
        ->get(['id', 'device_name', 'device_type']);

    return response()->json($devices);
})->middleware(['auth', 'permission:helpdesk,create']);

Route::post('/helpdesk/tambah-ticket', function (Request $request) {
    $validated = $request->validate([
        'tanggal' => ['required', 'date'],
        'pelapor' => ['required', 'string', 'max:255'],
        'room_id' => ['required', 'integer', 'exists:rooms,id'],
        'kategori' => ['required', Rule::in(['hardware', 'software'])],
        'device_id' => [
            Rule::requiredIf(fn () => $request->input('kategori') === 'hardware'),
            'nullable',
            'integer',
            'exists:devices,id',
        ],
        'kendala' => ['required', 'string'],
        'prioritas' => ['required', Rule::in(['rendah', 'sedang', 'tinggi'])],
        'petugas_id' => ['nullable', 'integer', 'exists:users,id'],
        'keterangan' => ['nullable', 'string'],
    ]);

    $kategoriMap = [
        'hardware' => '1',
        'software' => '2',
    ];
    $prioritasMap = [
        'rendah' => '1',
        'sedang' => '2',
        'tinggi' => '3',
    ];

    $tanggal = \Carbon\Carbon::parse($validated['tanggal'])->format('dmY');
    $kategoriCode = $kategoriMap[$validated['kategori']];
    $prioritasCode = $prioritasMap[$validated['prioritas']];

    $room = Room::find($validated['room_id']);
    $roomCode = $room?->room_id ?: (string) $validated['room_id'];
    $roomCode = str_replace('-', '', $roomCode);

    $deviceId = $validated['device_id'] ?? null;
    if ($validated['kategori'] === 'hardware') {
        $device = Device::find($deviceId);
        $deviceRoom = $device?->room_id;
        if ($deviceRoom && $room && $deviceRoom !== $room->room_id) {
            return back()->withErrors(['device_id' => 'Perangkat tidak sesuai dengan ruangan yang dipilih.'])->withInput();
        }
    } else {
        $deviceId = null;
    }

    $deviceCode = $deviceId ? (string) $deviceId : '0';
    $noTicket = $roomCode . $tanggal . $kategoriCode . $deviceCode . $prioritasCode;

    $ticket = HelpdeskTicket::create([
        'no_ticket' => $noTicket,
        'tanggal' => $validated['tanggal'],
        'pelapor' => $validated['pelapor'],
        'room_id' => $validated['room_id'],
        'device_id' => $deviceId,
        'kategori' => $validated['kategori'],
        'kendala' => $validated['kendala'],
        'prioritas' => $validated['prioritas'],
        'petugas_id' => $validated['petugas_id'] ?? null,
        'status' => 'open',
        'keterangan' => $validated['keterangan'] ?? null,
    ]);

    if (!empty($validated['petugas_id'])) {
        $petugasItId = role_id_by_key('petugas_it');
        $petugas = User::select('id', 'phone')
            ->where('id', $validated['petugas_id'])
            ->when($petugasItId, fn ($q) => $q->where('role_id', $petugasItId))
            ->first();
        if ($petugas && !empty($petugas->phone)) {
            $phone = preg_replace('/\D+/', '', $petugas->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            if (!str_starts_with($phone, '62')) {
                $phone = '62' . $phone;
            }

            $token = ticket_token_encode($ticket->no_ticket);
            $message = "Anda menerima tiket baru. Silakan segera melakukan pengecekan pada detail tiket melalui tautan berikut:\n"
                . "https://rsudzm.simti.xyz/helpdesk/detail-ticket/{$token}\n"
                . "Mohon untuk segera ditindaklanjuti.";

            $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
            try {
                $response = Http::withHeaders([
                    'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
                ])->post($baseUrl . '/send', [
                    'phone' => $phone,
                    'message' => $message,
                ]);
                if (!$response->successful()) {
                    \Log::error('Gagal mengirim notif helpdesk.', [
                        'ticket' => $ticket->no_ticket,
                        'petugas_id' => $petugas->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Gateway WA belum berjalan saat kirim notif helpdesk.', [
                    'ticket' => $ticket->no_ticket,
                    'petugas_id' => $petugas->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Log::error('Notif helpdesk tidak dikirim karena no HP petugas kosong.', [
                'ticket' => $ticket->no_ticket,
                'petugas_id' => $validated['petugas_id'],
            ]);
        }
    } else {
        \Log::error('Notif helpdesk tidak dikirim karena petugas_id kosong.', [
            'ticket' => $ticket->no_ticket,
        ]);
    }

    return redirect('/helpdesk')->with('success', 'Ticket berhasil dibuat.');
})->name('helpdesk.store')->middleware(['auth', 'permission:helpdesk,create']);

Route::delete('/helpdesk/{ticket}', function (HelpdeskTicket $ticket) {
    $ticket->delete();
    return redirect('/helpdesk')->with('success', 'Ticket berhasil dihapus.');
})->name('helpdesk.destroy')->middleware(['auth', 'permission:helpdesk,delete']);

Route::get('/detail-ticket/{no_ticket}', function (string $noTicket) {
    $user = auth()->user();
    $roleKey = $user?->role_key;
    if ($user && !in_array($roleKey, ['petugas_helpdesk'], true) && !($user->is_admin ?? false)) {
        abort(403);
    }
    $ticket = DB::table('helpdesk_tickets')
        ->leftJoin('rooms', 'rooms.id', '=', 'helpdesk_tickets.room_id')
        ->leftJoin('devices', 'devices.id', '=', 'helpdesk_tickets.device_id')
        ->leftJoin('users', 'users.id', '=', 'helpdesk_tickets.petugas_id')
        ->select(
            'helpdesk_tickets.*',
            'rooms.name as room_name',
            'devices.device_name as device_name',
            'users.name as petugas_name'
        )
        ->where('helpdesk_tickets.no_ticket', $noTicket)
        ->first();

    if (!$ticket) {
        abort(404);
    }

    return view('helpdesk.detail', [
        'ticket' => $ticket,
    ]);
})->name('helpdesk.show.internal')->middleware(['auth', 'permission:helpdesk,read']);

Route::get('/helpdesk/detail-ticket/{token}', function (string $token) {
    if (auth()->check() && !in_array(auth()->user()->role_key, ['petugas_it'], true) && !(auth()->user()->is_admin ?? false)) {
        abort(403);
    }

    try {
        $noTicket = ticket_token_decode($token);
    } catch (DecryptException $e) {
        abort(404);
    }

    $ticket = DB::table('helpdesk_tickets')
        ->leftJoin('rooms', 'rooms.id', '=', 'helpdesk_tickets.room_id')
        ->leftJoin('devices', 'devices.id', '=', 'helpdesk_tickets.device_id')
        ->leftJoin('users', 'users.id', '=', 'helpdesk_tickets.petugas_id')
        ->select(
            'helpdesk_tickets.*',
            'rooms.name as room_name',
            'devices.device_name as device_name',
            'users.name as petugas_name'
        )
        ->where('helpdesk_tickets.no_ticket', $noTicket)
        ->first();

    if (!$ticket) {
        abort(404);
    }

    return view('helpdesk.detail', [
        'ticket' => $ticket,
        'token' => $token,
    ]);
})->name('helpdesk.show.public');

Route::post('/helpdesk/detail-ticket/{token}/progress', function (string $token) {
    if (auth()->check()) {
        abort(403);
    }

    try {
        $noTicket = ticket_token_decode($token);
    } catch (DecryptException $e) {
        abort(404);
    }

    $ticket = HelpdeskTicket::where('no_ticket', $noTicket)->first();
    if (!$ticket) {
        abort(404);
    }

    if ($ticket->status === 'open') {
        $ticket->status = 'in_progress';
        $ticket->save();
    }

    return redirect()->route('helpdesk.show.public', $token)->with('success', 'Tiket diproses.');
})->name('helpdesk.progress.guest');

// ---------------- Laporan ----------------
Route::get('/laporan', function () {
    return view('laporan.index');
})->name('laporan.index')->middleware(['auth', 'permission:laporan,read']);

// ---------------- WA Gateway ----------------
Route::get('/whatsapp-gateway', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    $status = ['status' => 'offline', 'hasAuth' => false, 'sentCount' => 0, 'phone' => null, 'lastActiveAt' => null];
    try {
        $res = \Illuminate\Support\Facades\Http::get($baseUrl . '/status');
        if ($res->successful()) {
            $status = $res->json();
        }
    } catch (\Throwable $e) {
        // ignore
    }
    return view('wa-gateway.index', [
        'waStatus' => $status['status'] ?? 'offline',
        'waHasAuth' => (bool) ($status['hasAuth'] ?? false),
        'waSentCount' => (int) ($status['sentCount'] ?? 0),
        'waPhone' => $status['phone'] ?? null,
        'waLastActiveAt' => $status['lastActiveAt'] ?? null,
    ]);
})->name('wa.gateway')->middleware(['auth', 'admin']);

Route::post('/whatsapp-gateway/device/delete', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $res = \Illuminate\Support\Facades\Http::post($baseUrl . '/logout');
        if (!$res->successful()) {
            return redirect()->route('wa.gateway')->with('error', 'Gagal menghapus device.');
        }
    } catch (\Throwable $e) {
        return redirect()->route('wa.gateway')->with('error', 'Gateway belum berjalan.');
    }
    return redirect()->route('wa.gateway')->with('success', 'Device berhasil dihapus.');
})->name('wa.gateway.delete')->middleware(['auth', 'admin']);

Route::post('/whatsapp-gateway/connect', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $res = \Illuminate\Support\Facades\Http::post($baseUrl . '/connect');
        if (!$res->successful()) {
            return response()->json(['ok' => false, 'message' => 'Gagal menghubungi gateway.'], 500);
        }
        return response()->json($res->json());
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => 'Gateway belum berjalan.'], 500);
    }
})->name('wa.gateway.connect')->middleware(['auth', 'admin']);

Route::post('/whatsapp-gateway/disconnect', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $res = \Illuminate\Support\Facades\Http::post($baseUrl . '/logout');
        if (!$res->successful()) {
            return response()->json(['ok' => false, 'message' => 'Gagal memutuskan koneksi.'], 500);
        }
        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => 'Gateway belum berjalan.'], 500);
    }
})->name('wa.gateway.disconnect')->middleware(['auth', 'admin']);

Route::post('/whatsapp-gateway/reconnect', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        \Illuminate\Support\Facades\Http::post($baseUrl . '/logout');
        $res = \Illuminate\Support\Facades\Http::post($baseUrl . '/connect');
        if (!$res->successful()) {
            return response()->json(['ok' => false, 'message' => 'Gagal menyambungkan ulang.'], 500);
        }
        return response()->json($res->json());
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => 'Gateway belum berjalan.'], 500);
    }
})->name('wa.gateway.reconnect')->middleware(['auth', 'admin']);

Route::get('/whatsapp-gateway/qr', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $res = \Illuminate\Support\Facades\Http::get($baseUrl . '/qr');
        if (!$res->successful()) {
            return response()->json(['ok' => false, 'message' => 'QR belum tersedia.'], 404);
        }
        return response()->json($res->json());
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => 'Gateway belum berjalan.'], 500);
    }
})->name('wa.gateway.qr')->middleware(['auth', 'admin']);

Route::get('/whatsapp-gateway/status', function () {
    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $res = \Illuminate\Support\Facades\Http::get($baseUrl . '/status');
        if (!$res->successful()) {
            return response()->json(['ok' => false, 'message' => 'Gagal mengambil status.'], 500);
        }
        return response()->json($res->json());
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => 'Gateway belum berjalan.'], 500);
    }
})->name('wa.gateway.status')->middleware(['auth', 'admin']);

// ---------------- IP Address ----------------
Route::get('/ip-address', function (Request $request) {
    $status = $request->query('status');
    $q = $request->query('q');

    $ipAddrs = IpAddr::query()
        ->when($status, fn($q2) => $q2->where('status', $status))
        ->when($q, function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('ip_address', 'like', "%{$q}%")
                    ->orWhere('subnet', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        })
        ->orderBy('ip_address')
        ->paginate(20)
        ->withQueryString();

    return view('ipaddress.index', compact('ipAddrs', 'status', 'q'));
})->name('ipaddr.index')->middleware(['auth', 'permission:ip_address,read']);

Route::get('/ip-address/tambah', function () {
    return view('ipaddress.add');
})->name('ipaddr.create')->middleware(['auth', 'permission:ip_address,create']);

Route::post('/ip-address/tambah', function (Request $request) {
    $data = $request->validate([
        'ip_address' => ['required', 'ip', 'unique:ip_addrs,ip_address'],
        'subnet' => ['nullable', 'ipv4'],
        'status' => ['required', Rule::in(['available', 'used'])],
        'description' => ['nullable', 'string'],
    ], [
        'ip_address.required' => 'IP address wajib diisi.',
        'ip_address.ip' => 'IP address harus berupa alamat IP yang valid.',
        'ip_address.unique' => 'IP address sudah digunakan.',
        'subnet.ipv4' => 'Subnet harus berupa alamat IPv4 yang valid.',
        'status.required' => 'Status wajib dipilih.',
        'status.in' => 'Status harus Available atau Used.',
    ]);

    IpAddr::create($data);

    return redirect()->route('ipaddr.index')->with('success', 'IP Address berhasil ditambahkan.');
})->name('ipaddr.store')->middleware(['auth', 'permission:ip_address,create']);

Route::delete('/ip-address/{encoded}', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $ip = IpAddr::findOrFail($id);
    $ip->delete();
    return redirect()->route('ipaddr.index')->with('success', 'IP Address berhasil dihapus.');
})->name('ipaddr.destroy')->middleware(['auth', 'permission:ip_address,delete']);

Route::get('/ip-address/{encoded}/edit', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $ip = IpAddr::findOrFail($id);
    return view('ipaddress.edit', compact('ip'));
})->name('ipaddr.edit')->middleware(['auth', 'permission:ip_address,update']);

Route::put('/ip-address/{encoded}', function (Request $request, string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $ip = IpAddr::findOrFail($id);
    $data = $request->validate([
        'ip_address' => ['required', 'ip', Rule::unique('ip_addrs', 'ip_address')->ignore($ip->id)],
        'subnet' => ['nullable', 'ipv4'],
        'status' => ['required', Rule::in(['available', 'used'])],
        'description' => ['nullable', 'string'],
    ], [
        'ip_address.required' => 'IP address wajib diisi.',
        'ip_address.ip' => 'IP address harus berupa alamat IP yang valid.',
        'ip_address.unique' => 'IP address sudah digunakan.',
        'subnet.ipv4' => 'Subnet harus berupa alamat IPv4 yang valid.',
        'status.required' => 'Status wajib dipilih.',
        'status.in' => 'Status harus Available atau Used.',
    ]);

    $ip->update($data);

    return redirect()->route('ipaddr.index')->with('success', 'IP Address berhasil diperbarui.');
})->name('ipaddr.update')->middleware(['auth', 'permission:ip_address,update']);

// ---------------- Devices ----------------
Route::get('/perangkat', function (Request $request) {
    $q = $request->query('q');
    $deviceType = $request->query('device_type');

    $devices = Device::with(['room','spec'])
        ->when($q, function ($query, $q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('device_name', 'like', "%{$q}%")
                    ->orWhere('device_type', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%")
                    ->orWhere('condition', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            });
        })
        ->when($deviceType, function ($query, $deviceType) {
            $query->where('device_type', $deviceType);
        })
        ->orderBy('device_name')
        ->paginate(20)
        ->withQueryString();

    $deviceCollection = $devices->getCollection();
    $descriptions = $deviceCollection->map(function ($device) {
        $roomName = $device->room->name ?? ($device->room_id ?? '');
        return trim(($device->device_name ?: 'Perangkat') . ' - ' . $roomName);
    })->filter()->unique()->values()->all();

    $ipMap = [];
    if (!empty($descriptions)) {
        $ipMap = IpAddr::whereIn('description', $descriptions)
            ->get(['ip_address', 'subnet', 'description'])
            ->keyBy('description');
    }

    $deviceCollection = $deviceCollection->map(function ($device) use ($ipMap) {
        $roomName = $device->room->name ?? ($device->room_id ?? '');
        $desc = trim(($device->device_name ?: 'Perangkat') . ' - ' . $roomName);
        $device->ip_info = $ipMap[$desc] ?? null;
        return $device;
    });
    $devices->setCollection($deviceCollection);

    $deviceTypes = [
        'CPU',
        'Monitor',
        'PC AIO',
        'Laptop',
        'Router',
        'Hub',
        'Printer',
        'Telepon',
    ];

    return view('devices.device', [
        'devices' => $devices,
        'q' => $q,
        'deviceTypes' => $deviceTypes,
    ]);
})->name('device.index')->middleware(['auth', 'permission:perangkat,read']);

Route::get('/perangkat/tambah-perangkat', function () {
    $rooms = Room::orderBy('room_id')->get(['room_id', 'name']);
    $deviceTypes = [
        'CPU',
        'Monitor',
        'PC AIO',
        'Laptop',
        'Router',
        'Hub',
        'Printer',
        'Telepon',
    ];
    return view('devices.adddevice', compact('rooms', 'deviceTypes'));
})->name('device.create')->middleware(['auth', 'permission:perangkat,create']);

Route::post('/perangkat/tambah-perangkat', function (Request $request) {
    $deviceTypes = ['CPU', 'Monitor', 'PC AIO', 'Laptop', 'Router', 'Hub', 'Printer', 'Telepon'];

    $data = $request->validate([
        'device_name' => ['required', 'string', 'max:255'],
        'room_id' => ['nullable', 'string', 'max:20', 'exists:rooms,room_id'],
        'device_type' => ['required', 'string', Rule::in($deviceTypes)],
        'brand' => ['nullable', 'string', 'max:255'],
        'model' => ['nullable', 'string', 'max:255'],
        'condition' => ['nullable', 'string', Rule::in(['Good','Damage','Maintenance'])],
        'status' => ['nullable', 'string', Rule::in(['Active','Inactive'])],
        'notes' => ['nullable', 'string'],
    ]);

    Device::create($data);

    return redirect()->route('device.index')->with('success', 'Perangkat berhasil ditambahkan.');
})->name('device.store')->middleware(['auth', 'permission:perangkat,create']);

Route::delete('/perangkat/{device}', function (Device $device) {
    $spec = DeviceSpec::where('device_id', $device->id)->first();
    if ($spec && !empty($spec->ip_address)) {
        IpAddr::where('ip_address', $spec->ip_address)->delete();
    }
    $device->load('room');
    $roomName = $device->room?->name ?: ($device->room_id ?: '');
    $desc = trim(($device->device_name ?: 'Perangkat') . ' - ' . $roomName);
    if ($desc !== '') {
        IpAddr::where('description', $desc)->delete();
    }
    DeviceSpec::where('device_id', $device->id)->delete();
    $device->delete();
    return redirect()->route('device.index')->with('success', 'Perangkat berhasil dihapus.');
})->name('device.destroy')->middleware(['auth', 'permission:perangkat,delete']);

Route::get('/perangkat/{encoded}/edit-perangkat', function (string $encoded) {
    try {
        $deviceId = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $deviceTypes = [
        'CPU','Monitor','PC AIO','Laptop','Router','Hub','Printer',
    ];
    $device = Device::findOrFail($deviceId);
    $rooms = Room::orderBy('room_id')->get(['room_id','name']);
    return view('devices.editdevice', compact('device','rooms','deviceTypes','encoded'));
})->name('device.edit')->middleware(['auth', 'permission:perangkat,update']);

Route::put('/perangkat/{encoded}/edit-perangkat', function (Request $request, string $encoded) {
    try {
        $deviceId = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $deviceTypes = ['CPU','Monitor','PC AIO','Laptop','Router','Hub','Printer'];
    $data = $request->validate([
        'device_name' => ['required', 'string', 'max:255'],
        'room_id' => ['nullable', 'string', 'max:20', 'exists:rooms,room_id'],
        'device_type' => ['required', 'string', Rule::in($deviceTypes)],
        'brand' => ['nullable', 'string', 'max:255'],
        'model' => ['nullable', 'string', 'max:255'],
        'condition' => ['nullable', 'string', Rule::in(['Good','Damage','Maintenance'])],
        'status' => ['nullable', 'string', Rule::in(['Active','Inactive'])],
        'notes' => ['nullable', 'string'],
    ]);
    $device = Device::findOrFail($deviceId);
    $device->update($data);
    return redirect()->route('device.index')->with('success','Perangkat berhasil diperbarui.');
})->name('device.update')->middleware(['auth', 'permission:perangkat,update']);

Route::get('/perangkat/spesifikasi-perangkat', function () {
    $usedIds = DeviceSpec::pluck('device_id')->all();
    $devices = Device::whereIn('device_type', ['CPU','PC AIO','Laptop'])
        ->whereNotIn('id', $usedIds)
        ->orderBy('device_name')
        ->get(['id','device_name','device_type']);
    $ramOptions = ['4 GB','8 GB','16 GB','32 GB','64 GB'];
    $capacityOptions = ['128 GB','256 GB','512 GB','1 TB','2 TB'];
    return view('devices.specdevice', compact('devices','ramOptions','capacityOptions'));
})->name('device.spec.form')->middleware(['auth', 'permission:spesifikasi_perangkat,create']);

Route::get('/perangkat/spesifikasi-perangkat/{device}', function (Device $device) {
    $usedIds = DeviceSpec::pluck('device_id')->all();
    $devices = Device::whereIn('device_type', ['CPU','PC AIO','Laptop'])
        ->where(function($q) use ($usedIds, $device) {
            $q->whereNotIn('id', $usedIds)->orWhere('id', $device->id);
        })
        ->orderBy('device_name')
        ->get(['id','device_name','device_type']);
    $ramOptions = ['4 GB','8 GB','16 GB','32 GB','64 GB'];
    $capacityOptions = ['128 GB','256 GB','512 GB','1 TB','2 TB'];
    $device->load('spec');
    return view('devices.specdevice', [
        'devices' => $devices,
        'ramOptions' => $ramOptions,
        'capacityOptions' => $capacityOptions,
        'editDevice' => $device,
    ]);
})->name('device.spec.edit')->middleware(['auth', 'permission:spesifikasi_perangkat,update']);

Route::post('/perangkat/spesifikasi-perangkat', function (Request $request) {
    $ramOptions = ['4 GB','8 GB','16 GB','32 GB','64 GB'];
    $capacityOptions = ['128 GB','256 GB','512 GB','1 TB','2 TB'];
    $data = $request->validate([
        'device_id' => ['required','exists:devices,id'],
        'processor' => ['required','string','max:255'],
        'ram' => ['required', Rule::in($ramOptions)],
        'storage_type' => ['required', Rule::in(['HDD','SSD'])],
        'storage_capacity' => ['required', Rule::in($capacityOptions)],
        'ip_address' => ['nullable', 'ip'],
        'subnet' => ['nullable', 'ipv4'],
        'gpu' => ['nullable','string','max:255'],
        'os' => ['required','string','max:255'],
        'details' => ['nullable','string'],
    ]);

    $device = Device::with('room')->find($data['device_id']);
    $roomName = $device?->room?->name ?: ($device?->room_id ?: '');
    $desc = trim(($device?->device_name ?: 'Perangkat') . ' - ' . $roomName);

    if (!empty($data['ip_address'])) {
        $existingIp = IpAddr::where('ip_address', $data['ip_address'])->first();
        if ($existingIp && $existingIp->description !== $desc) {
            return back()
                ->withInput()
                ->with('ip_error', 'IP address sudah digunakan di ' . ($existingIp->description ?: 'perangkat lain') . '.');
        }
    }

    DeviceSpec::updateOrCreate(
        ['device_id' => $data['device_id']],
        collect($data)->except('device_id')->toArray()
    );

    if (!empty($data['ip_address'])) {
        IpAddr::updateOrCreate(
            ['ip_address' => $data['ip_address']],
            [
                'subnet' => $data['subnet'] ?? null,
                'status' => 'used',
                'description' => $desc,
            ]
        );
    }

    return redirect()->route('device.index')->with('success','Spesifikasi perangkat berhasil disimpan.');
})->name('device.spec.save')->middleware(['auth', 'permission:spesifikasi_perangkat,create']);
Route::post('/perangkat/{device}/spec', function (Request $request, Device $device) {
    $ramOptions = ['4 GB','8 GB','16 GB','32 GB','64 GB'];
    $capacityOptions = ['128 GB','256 GB','512 GB','1 TB','2 TB'];

    $data = $request->validate([
        'processor' => ['required', 'string', 'max:255'],
        'ram' => ['required', Rule::in($ramOptions)],
        'storage_type' => ['required', Rule::in(['HDD','SSD'])],
        'storage_capacity' => ['required', Rule::in($capacityOptions)],
        'ip_address' => ['nullable', 'ip'],
        'subnet' => ['nullable', 'ipv4'],
        'gpu' => ['nullable', 'string', 'max:255'],
        'os' => ['required', 'string', 'max:255'],
        'details' => ['nullable', 'string'],
    ]);

    $device->load('room');
    $roomName = $device->room?->name ?: ($device->room_id ?: '');
    $desc = trim(($device->device_name ?: 'Perangkat') . ' - ' . $roomName);

    if (!empty($data['ip_address'])) {
        $existingIp = IpAddr::where('ip_address', $data['ip_address'])->first();
        if ($existingIp && $existingIp->description !== $desc) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'IP address sudah digunakan di ' . ($existingIp->description ?: 'perangkat lain') . '.'], 422);
            }
            return back()
                ->withInput()
                ->with('ip_error', 'IP address sudah digunakan di ' . ($existingIp->description ?: 'perangkat lain') . '.');
        }
    }

    DeviceSpec::updateOrCreate(
        ['device_id' => $device->id],
        $data
    );

    if (!empty($data['ip_address'])) {
        IpAddr::updateOrCreate(
            ['ip_address' => $data['ip_address']],
            [
                'subnet' => $data['subnet'] ?? null,
                'status' => 'used',
                'description' => $desc,
            ]
        );
    }

    if ($request->expectsJson()) {
        return response()->json(['message' => 'Spesifikasi tersimpan.']);
    }
    return redirect()->back()->with('success', 'Spesifikasi tersimpan.');
})->name('device.spec.store')->middleware(['auth', 'permission:spesifikasi_perangkat,update']);

Route::delete('/perangkat/spesifikasi-perangkat/{device}', function (Device $device) {
    $spec = DeviceSpec::where('device_id', $device->id)->first();
    if ($spec && !empty($spec->ip_address)) {
        IpAddr::where('ip_address', $spec->ip_address)->update([
            'status' => 'available',
        ]);
    }
    $device->load('room');
    $roomName = $device->room?->name ?: ($device->room_id ?: '');
    $desc = trim(($device->device_name ?: 'Perangkat') . ' - ' . $roomName);
    if ($desc !== '') {
        IpAddr::where('description', $desc)->update([
            'status' => 'available',
        ]);
    }
    DeviceSpec::where('device_id', $device->id)->delete();
    return redirect()->route('device.index')->with('success', 'Spesifikasi perangkat berhasil dihapus.');
})->name('device.spec.delete')->middleware(['auth', 'permission:spesifikasi_perangkat,delete']);

// ---------------- Profile ----------------
Route::get('/profile', function (Request $request) {
    $request->session()->put('active_app', 'profile');
    $user = auth()->user();
    $profile = DB::table('profiles')->where('user_id', $user->id)->first();
    if (!$profile) {
        return redirect()->route('profile.create');
    }
    $token = profile_token_encode((string) $profile->id);
    return view('profile.index', compact('profile', 'user', 'token'));
})->name('profile.home')->middleware('auth');

Route::get('/profile/tambah', function (Request $request) {
    $request->session()->put('active_app', 'profile');
    $user = auth()->user();
    $profile = DB::table('profiles')->where('user_id', $user->id)->first();
    if ($profile) {
        return redirect()->route('profile.edit', profile_token_encode((string) $profile->id));
    }
    return view('profile.create', compact('user'));
})->name('profile.create')->middleware('auth');

Route::post('/profile', function (Request $request) {
    $request->session()->put('active_app', 'profile');
    $user = auth()->user();
    $exists = DB::table('profiles')->where('user_id', $user->id)->exists();
    if ($exists) {
        $profileId = (string) DB::table('profiles')->where('user_id', $user->id)->value('id');
        return redirect()->route('profile.edit', profile_token_encode($profileId));
    }

    $data = $request->validate([
        'nama' => ['required', 'string', 'max:255'],
        'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan'])],
        'tempat_lahir' => ['nullable', 'string', 'max:255'],
        'tanggal_lahir' => ['nullable', 'date'],
        'agama' => ['nullable', 'string', 'max:100'],
        'status_perkawinan' => ['nullable', 'string', 'max:50'],
        'provinsi' => ['nullable', 'string', 'max:50'],
        'kabupaten' => ['nullable', 'string', 'max:50'],
        'kecamatan' => ['nullable', 'string', 'max:50'],
        'desa' => ['nullable', 'string', 'max:50'],
        'alamat' => ['nullable', 'string'],
    ]);

    DB::table('profiles')->insert([
        'user_id' => $user->id,
        'nama' => $data['nama'],
        'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
        'tempat_lahir' => $data['tempat_lahir'] ?? null,
        'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
        'agama' => $data['agama'] ?? null,
        'status_perkawinan' => $data['status_perkawinan'] ?? null,
        'provinsi' => $data['provinsi'] ?? null,
        'kabupaten' => $data['kabupaten'] ?? null,
        'kecamatan' => $data['kecamatan'] ?? null,
        'desa' => $data['desa'] ?? null,
        'alamat' => $data['alamat'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->where('id', $user->id)->update([
        'name' => $data['nama'],
        'updated_at' => now(),
    ]);

    $profileId = (string) DB::table('profiles')->where('user_id', $user->id)->value('id');
    return redirect()->route('profile.edit', profile_token_encode($profileId))
        ->with('success', 'Profil berhasil disimpan.');
})->name('profile.store')->middleware('auth');

Route::get('/profile/{id}/edit', function (Request $request, int $id) {
    $request->session()->put('active_app', 'profile');
    return redirect()->route('profile.edit', profile_token_encode((string) $id));
})->whereNumber('id')->middleware('auth');

Route::get('/profile/{token}/edit', function (Request $request, string $token) {
    $request->session()->put('active_app', 'profile');
    $user = auth()->user();
    try {
        $id = profile_token_decode($token);
    } catch (\Throwable $e) {
        abort(404);
    }
    $profile = DB::table('profiles')->where('id', $id)->first();
    if (!$profile) {
        abort(404);
    }
    if (!$user->is_admin && (int) $profile->user_id !== (int) $user->id) {
        abort(403);
    }
    return view('profile.edit', compact('profile', 'user', 'token'));
})->name('profile.edit')->middleware('auth');

Route::put('/profile/{token}', function (Request $request, string $token) {
    $request->session()->put('active_app', 'profile');
    $user = auth()->user();
    try {
        $id = profile_token_decode($token);
    } catch (\Throwable $e) {
        abort(404);
    }
    $profile = DB::table('profiles')->where('id', $id)->first();
    if (!$profile) {
        abort(404);
    }
    if (!$user->is_admin && (int) $profile->user_id !== (int) $user->id) {
        abort(403);
    }

    $data = $request->validate([
        'nama' => ['required', 'string', 'max:255'],
        'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'perempuan'])],
        'tempat_lahir' => ['nullable', 'string', 'max:255'],
        'tanggal_lahir' => ['nullable', 'date'],
        'agama' => ['nullable', 'string', 'max:100'],
        'status_perkawinan' => ['nullable', 'string', 'max:50'],
        'provinsi' => ['nullable', 'string', 'max:50'],
        'kabupaten' => ['nullable', 'string', 'max:50'],
        'kecamatan' => ['nullable', 'string', 'max:50'],
        'desa' => ['nullable', 'string', 'max:50'],
        'alamat' => ['nullable', 'string'],
    ]);

    DB::table('profiles')->where('id', $id)->update([
        'nama' => $data['nama'],
        'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
        'tempat_lahir' => $data['tempat_lahir'] ?? null,
        'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
        'agama' => $data['agama'] ?? null,
        'status_perkawinan' => $data['status_perkawinan'] ?? null,
        'provinsi' => $data['provinsi'] ?? null,
        'kabupaten' => $data['kabupaten'] ?? null,
        'kecamatan' => $data['kecamatan'] ?? null,
        'desa' => $data['desa'] ?? null,
        'alamat' => $data['alamat'] ?? null,
        'updated_at' => now(),
    ]);

    DB::table('users')->where('id', $profile->user_id)->update([
        'name' => $data['nama'],
        'updated_at' => now(),
    ]);

    return redirect()->route('profile.home')->with('success', 'Profil berhasil diperbarui.');
})->name('profile.update')->middleware('auth');

// ---------------- Lainnya ----------------

Route::get('/auth/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/auth/otp', function () {
    return view('auth.otp');
})->name('auth.otp');

Route::get('/forget-password', function () {
    return view('auth.forget-password');
})->name('auth.forget');

Route::post('/forget-password', function (Request $request) {
    $data = $request->validate([
        'phone' => ['required', 'string'],
    ]);

    $raw = normalize_id_phone($data['phone'] ?? null);
    if (!is_valid_id_phone($raw)) {
        return back()->withInput()->with('error', 'Format No HP tidak valid. Gunakan 628xxx atau 08xxx.');
    }

    $user = User::where('phone', $raw)->first();
    if (!$user) {
        return back()->withInput()->with('error', 'No HP tidak ditemukan.');
    }

    $token = Str::random(64);
    $user->reset_token = $token;
    $user->reset_token_expired_at = now()->addHours(1);
    $user->save();
    $link = url('/change-password/' . $token);
    $message = "Ganti password Anda melalui link berikut:\n{$link}\n\nAbaikan pesan ini jika Anda tidak meminta perubahan password.";

    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $response = Http::withHeaders([
            'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
        ])->post($baseUrl . '/send', [
            'phone' => $raw,
            'message' => $message,
        ]);
        if (!$response->successful()) {
            return back()->withInput()->with('error', 'Gagal mengirim link reset password.');
        }
    } catch (\Throwable $e) {
        return back()->withInput()->with('error', 'Gateway belum berjalan.');
    }

    return back()->with('success', 'Link reset password sudah dikirim ke WhatsApp.');
})->name('auth.forget.send');

Route::get('/change-password/{token}', function (string $token) {
    $user = User::where('reset_token', $token)
        ->where('reset_token_expired_at', '>=', now())
        ->first();
    if (!$user) {
        abort(404);
    }
    return view('auth.change-password', ['user' => $user, 'token' => $token]);
})->name('auth.change');

Route::post('/change-password/{token}', function (Request $request, string $token) {
    $user = User::where('reset_token', $token)
        ->where('reset_token_expired_at', '>=', now())
        ->first();
    if (!$user) {
        abort(404);
    }

    $data = $request->validate([
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ], [
        'password.confirmed' => 'Konfirmasi password tidak sama.',
    ]);

    if (Hash::check($data['password'], $user->password)) {
        return back()->withErrors(['password' => 'Password baru tidak boleh sama dengan password lama.']);
    }

    $user->password = Hash::make($data['password']);
    $user->reset_token = null;
    $user->reset_token_expired_at = null;
    $user->save();

    return redirect('/auth/login')
        ->with('success', 'Password berhasil diubah. Silakan login.');
})->name('auth.change.save');

Route::post('/auth/otp', function (Request $request) {
    $request->validate([
        'otp_code' => ['required', 'digits:' . (env('OTP_LENGTH') ?: 6)],
    ], [
        'otp_code.required' => 'Kode OTP wajib diisi.',
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

    $code = $request->session()->get('otp_login_code');
    $userId = $request->session()->get('otp_login_user');
    $expires = $request->session()->get('otp_login_expires');

    if (!$code || !$userId || !$expires) {
        return back()->withErrors(['otp_code' => 'Sesi OTP tidak ditemukan atau sudah kadaluarsa.']);
    }
    if ((int) $expires < now()->timestamp) {
        $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
        return back()->withErrors(['otp_code' => "Kode OTP sudah kadaluarsa (maksimal {$expireMinutes} menit)."]);
    }
    if ($request->input('otp_code') !== $code) {
        return back()->withErrors(['otp_code' => 'Kode OTP tidak valid.']);
    }

    $user = User::where('id', $userId)->first();
    if (!$user) {
        return back()->withErrors(['otp_code' => 'Akun tidak ditemukan.']);
    }

    Auth::login($user);
    $request->session()->regenerate();
    $request->session()->forget(['otp_login_code', 'otp_login_user', 'otp_login_expires']);

    return redirect()->intended('/apps');
})->name('auth.otp.verify');

Route::post('/auth/otp/resend', function (Request $request) {
    $userId = $request->session()->get('otp_login_user');
    if (!$userId) {
        return response()->json(['message' => 'Sesi OTP tidak ditemukan.'], 400);
    }

    $user = User::where('id', $userId)->first();
    if (!$user || empty($user->phone)) {
        return response()->json(['message' => 'No telepon belum terdaftar untuk akun ini.'], 400);
    }

    $length = (int) (env('OTP_LENGTH') ?: 6);
    $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
    $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    $message = "Kode OTP SIMTI RSUDZM: {$code}. Berlaku {$expireMinutes} menit.";

    $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
    try {
        $response = Http::withHeaders([
            'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
        ])->post($baseUrl . '/send', [
            'phone' => $user->phone,
            'message' => $message,
        ]);
        if (!$response->successful()) {
            return response()->json(['message' => 'Gagal mengirim OTP.'], 500);
        }
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Gateway belum berjalan.'], 500);
    }

    $request->session()->put([
        'otp_login_code' => $code,
        'otp_login_expires' => now()->addMinutes($expireMinutes)->timestamp,
    ]);

    return response()->json(['message' => 'OTP berhasil dikirim ulang.']);
})->name('auth.otp.resend');

Route::post('/auth/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::validate(['username' => $credentials['username'], 'password' => $credentials['password']])) {
        $user = User::where('username', $credentials['username'])->first();
        if (!$user || empty($user->phone)) {
            return back()->withInput($request->only('username'))
                ->withErrors(['username' => 'No telepon belum terdaftar untuk akun ini.']);
        }
        if (empty($user->is_verified)) {
            return back()->withInput($request->only('username'))
                ->withErrors(['username' => 'Akun SIMTI Anda belum diverifikasi oleh Admin. Silakan hubungi Admin untuk mengaktifkan akun']);
        }
        if (!filter_var(env('OTP_LOGIN_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/apps');
        }

        $length = (int) (env('OTP_LENGTH') ?: 6);
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $expireMinutes = (int) (env('OTP_EXPIRE') ?: 5);
        $message = "Kode OTP SIMTI RSUDZM: {$code}. Berlaku {$expireMinutes} menit.";

        $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
            ])->post($baseUrl . '/send', [
                'phone' => $user->phone,
                'message' => $message,
            ]);
            if (!$response->successful()) {
                return back()->withInput($request->only('username'))
                    ->withErrors(['username' => 'Gagal mengirim OTP. Silakan coba lagi.']);
            }
        } catch (\Throwable $e) {
            return back()->withInput($request->only('username'))
                ->withErrors(['username' => 'Gateway belum berjalan.']);
        }

        $request->session()->put([
            'otp_login_code' => $code,
            'otp_login_user' => $user->id,
            'otp_login_expires' => now()->addMinutes($expireMinutes)->timestamp,
        ]);

        return redirect()->route('auth.otp');
    }

    return back()->withInput($request->only('username'))
        ->withErrors(['username' => 'Username atau password salah']);
})->name('auth.login');

Route::post('/auth/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/')->with('success', 'Anda telah keluar.');
})->name('logout')->middleware('auth');
