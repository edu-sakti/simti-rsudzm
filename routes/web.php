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
use App\Models\User;
use App\Models\Room;
use App\Models\Cctv;
use App\Models\IpAddr;
use App\Models\Device;
use App\Models\DeviceSpec;
use App\Models\HelpdeskTicket;

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('dashboard');
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

// ---------------- Pengguna ----------------
Route::get('/pengguna', function (Request $request) {
    $search = $request->query('q');
    $users = User::query()
        ->with('room')
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
        })
        ->orderBy('name')
        ->paginate(10)
        ->withQueryString();

    return view('users.user', compact('users', 'search'));
})->name('users.index')->middleware(['auth', 'admin']);

Route::get('/pengguna/tambah-link', function (Request $request) {
    $role = $request->query('role');
    if ($role && !in_array($role, ['manajemen','kepala_ruangan','petugas','petugas_helpdesk'], true)) {
        abort(400);
    }
    $code = bin2hex(random_bytes(16));
    Cache::put('user_invite_' . $code, [
        'valid' => true,
        'role' => $role,
    ], now()->addMinutes(15));
    $link = url('/pengguna/tambah/' . $code);
    if (request()->expectsJson()) {
        return response()->json(['link' => $link]);
    }
    return redirect()->to($link);
})->name('users.invite')->middleware(['auth', 'admin']);

Route::get('/pengguna/tambah', function () {
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('users.adduser', compact('rooms'));
})->name('users.create')->middleware(['auth', 'admin']);

Route::get('/pengguna/tambah/{kode}', function (string $kode) {
    $invite = Cache::get('user_invite_' . $kode);
    if (!$invite || empty($invite['valid'])) {
        abort(403);
    }
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('users.adduser', [
        'rooms' => $rooms,
        'invite_code' => $kode,
        'invite_role' => $invite['role'] ?? null,
    ]);
})->name('users.create.invite');

Route::post('/pengguna/otp', function (Request $request) {
    if (!filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
        return response()->json(['message' => 'OTP sedang dinonaktifkan.'], 400);
    }
    $data = $request->validate([
        'phone' => ['required', 'regex:/^62\d{8,15}$/'],
        'invite_code' => ['nullable', 'string'],
    ], [
        'phone.regex' => 'No telepon harus format internasional (contoh: 62812xxxxxxx).',
    ]);
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
            'phone' => $data['phone'],
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
        'otp_phone' => $data['phone'],
        'otp_expires' => now()->addMinutes($expireMinutes)->timestamp,
    ]);

    return response()->json(['message' => 'OTP berhasil dikirim.']);
})->name('users.otp');

Route::post('/pengguna', function (Request $request) {
    $otpEnabled = filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
        'role' => ['required', 'in:admin,petugas,petugas_helpdesk,manajemen,kepala_ruangan,staff'],
        'room_id' => ['nullable', 'integer', Rule::requiredIf(fn() => $request->input('role') === 'kepala_ruangan'), 'exists:rooms,id'],
        'phone' => ['required', 'regex:/^62\d{8,15}$/', 'unique:users,phone'],
        'otp_code' => $otpEnabled ? ['required', 'digits:' . (env('OTP_LENGTH') ?: 6)] : ['nullable'],
        'password' => ['required', 'string', 'min:8'],
    ], [
        'phone.regex' => 'No telepon harus format internasional (contoh: 62812xxxxxxx).',
        'phone.unique' => 'No HP Telah Terdaftar, Silahkan Hubungi Admin',
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

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

    if ($data['role'] === 'staff') {
        $data['role'] = 'petugas';
    }
    if ($data['role'] !== 'kepala_ruangan') {
        $data['room_id'] = null;
    }

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
    $inviteRole = $invite['role'] ?? null;
    $request->merge(['invite_code' => $kode]);
    $otpEnabled = filter_var(env('OTP_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
        'role' => ['required', 'in:admin,petugas,petugas_helpdesk,manajemen,kepala_ruangan,staff'],
        'room_id' => ['nullable', 'integer', Rule::requiredIf(fn() => $request->input('role') === 'kepala_ruangan'), 'exists:rooms,id'],
        'phone' => ['required', 'regex:/^62\d{8,15}$/', 'unique:users,phone'],
        'otp_code' => $otpEnabled ? ['required', 'digits:' . (env('OTP_LENGTH') ?: 6)] : ['nullable'],
        'password' => ['required', 'string', 'min:8'],
        'invite_code' => ['required', 'string'],
    ], [
        'phone.regex' => 'No telepon harus format internasional (contoh: 62812xxxxxxx).',
        'phone.unique' => 'No HP Telah Terdaftar, Silahkan Hubungi Admin',
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

    if ($inviteRole && $data['role'] !== $inviteRole) {
        return back()->withErrors(['role' => 'Role tidak sesuai dengan link undangan.'])->withInput();
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

    if ($data['role'] === 'staff') {
        $data['role'] = 'petugas';
    }
    if ($data['role'] !== 'kepala_ruangan') {
        $data['room_id'] = null;
    }

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
    return view('users.edituser', ['user' => $user, 'encoded' => $encoded, 'rooms' => $rooms]);
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
        'role' => ['required', 'in:admin,petugas,petugas_helpdesk,manajemen,kepala_ruangan,staff'],
        'room_id' => ['nullable', 'integer', Rule::requiredIf(fn() => $request->input('role') === 'kepala_ruangan'), 'exists:rooms,id'],
        'phone' => ['required', 'regex:/^62\d{8,15}$/', Rule::unique('users', 'phone')->ignore($user->username, 'username')],
        'otp_code' => $otpEnabled ? ['nullable', 'digits:' . (env('OTP_LENGTH') ?: 6)] : ['nullable'],
        'password' => ['nullable', 'string', 'min:8'],
    ], [
        'phone.regex' => 'No telepon harus format internasional (contoh: 62812xxxxxxx).',
        'phone.unique' => 'No HP Telah Terdaftar, Silahkan Hubungi Admin',
        'otp_code.digits' => 'Kode OTP harus ' . (env('OTP_LENGTH') ?: 6) . ' digit.',
    ]);

    if (empty($data['password'])) {
        unset($data['password']);
    }
    if ($data['role'] === 'staff') {
        $data['role'] = 'petugas';
    }
    if ($data['role'] !== 'kepala_ruangan') {
        $data['room_id'] = null;
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
    if (!auth()->check() || auth()->user()->role !== 'admin') {
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

    return view('ruangan.ruangan', [
        'rooms' => $rooms,
        'search' => $search,
        'selectedKategori' => $kategori,
        'categories' => $categories,
    ]);
})->name('rooms.index')->middleware('auth');

Route::get('/ruangan/tambah', function () use ($roomCategories) {
    return view('ruangan.addruangan', ['categories' => $roomCategories]);
})->name('rooms.create')->middleware('auth');

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
})->name('rooms.store')->middleware('auth');

Route::get('/ruangan/{encoded}/edit', function (string $encoded) use ($roomCategories) {
    try {
        $roomId = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $room = Room::findOrFail($roomId);
    return view('ruangan.editruangan', ['room' => $room, 'encoded' => $encoded, 'categories' => $roomCategories]);
})->name('rooms.edit')->middleware('auth');

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
})->name('rooms.update')->middleware('auth');

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
})->name('rooms.destroy')->middleware('auth');

// ---------------- Hak Akses ----------------
Route::get('/hak-akses', function () {
    return view('hak-akses.hakakses');
})->name('hakakses.index')->middleware(['auth', 'admin']);

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

    return view('cctv.cctv', compact('cctvs', 'status', 'q'));
})->name('cctv.index')->middleware('auth');

Route::get('/cctv/tambah', function () {
    $rooms = Room::orderBy('room_id')->get(['room_id', 'name']);
    return view('cctv.addcctv', ['rooms' => $rooms]);
})->name('cctv.create')->middleware('auth');

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
})->name('cctv.store')->middleware('auth');

Route::get('/cctv/{encoded}/edit', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $cctv = Cctv::findOrFail($id);
    $rooms = Room::orderBy('name')->get(['room_id', 'name']);
    return view('cctv.editcctv', [
        'cctv' => $cctv,
        'rooms' => $rooms,
        'encoded' => $encoded,
    ]);
})->name('cctv.edit')->middleware('auth');

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
})->name('cctv.update')->middleware('auth');

Route::delete('/cctv/{encoded}', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $cctv = Cctv::findOrFail($id);
    $cctv->delete();
    return redirect()->route('cctv.index')->with('success', 'CCTV berhasil dihapus.');
})->name('cctv.destroy')->middleware('auth');

// ---------------- ISP ----------------
Route::get('/isp', function () {
    $isps = DB::table('isps')
        ->leftJoin('rooms', 'isps.ruang_installasi', '=', 'rooms.id')
        ->select('isps.*', 'rooms.name as room_name', 'rooms.room_id as room_code')
        ->orderBy('isps.nama_isp')
        ->get();
    return view('isp.isp', compact('isps'));
})->name('isp.index')->middleware('auth');

Route::get('/isp/tambah', function () {
    $rooms = Room::orderBy('room_id')->get(['id','room_id','name']);
    return view('isp.addisp', compact('rooms'));
})->name('isp.create')->middleware('auth');

Route::post('/isp/tambah', function (Request $request) {
    $data = $request->validate([
        'nama_isp' => ['required', 'string', 'max:255'],
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
})->name('isp.store')->middleware('auth');

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
    return view('isp.editisp', ['isp' => $isp, 'encoded' => $encoded, 'rooms' => $rooms]);
})->name('isp.edit')->middleware('auth');

Route::put('/isp/{encoded}', function (Request $request, string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $data = $request->validate([
        'nama_isp' => ['required', 'string', 'max:255'],
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
})->name('isp.update')->middleware('auth');

Route::delete('/isp/{encoded}', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    DB::table('isps')->where('id', $id)->delete();
    return redirect()->route('isp.index')->with('success', 'Data ISP berhasil dihapus.');
})->name('isp.destroy')->middleware('auth');

// ---------------- Helpdesk ----------------
Route::get('/helpdesk', function () {
    $tickets = DB::table('helpdesk_tickets')
        ->leftJoin('rooms', 'rooms.id', '=', 'helpdesk_tickets.room_id')
        ->leftJoin('users', 'users.id', '=', 'helpdesk_tickets.petugas_id')
        ->select(
            'helpdesk_tickets.id',
            'helpdesk_tickets.no_ticket',
            'helpdesk_tickets.tanggal',
            'helpdesk_tickets.pelapor',
            'helpdesk_tickets.kategori',
            'helpdesk_tickets.sub_kategori',
            'helpdesk_tickets.kendala',
            'helpdesk_tickets.prioritas',
            'helpdesk_tickets.status',
            'helpdesk_tickets.keterangan',
            'rooms.name as room_name',
            'users.name as petugas_name'
        )
        ->orderByDesc('helpdesk_tickets.tanggal')
        ->orderByDesc('helpdesk_tickets.id')
        ->get();

    return view('helpdesk.helpdesk', [
        'tickets' => $tickets,
    ]);
})->name('helpdesk.index')->middleware('auth');

Route::get('/helpdesk/tambah-ticket', function () {
    $rooms = \App\Models\Room::query()
        ->select('id', 'name')
        ->orderBy('name')
        ->get();
    $petugas = \App\Models\User::query()
        ->select('id', 'name')
        ->where('role', 'petugas')
        ->orderBy('name')
        ->get();

    return view('helpdesk.addticket', [
        'rooms' => $rooms,
        'petugas' => $petugas,
    ]);
})->name('helpdesk.create')->middleware('auth');

Route::post('/helpdesk/tambah-ticket', function (Request $request) {
    $validated = $request->validate([
        'tanggal' => ['required', 'date'],
        'pelapor' => ['required', 'string', 'max:255'],
        'room_id' => ['required', 'integer', 'exists:rooms,id'],
        'kategori' => ['required', Rule::in(['hardware', 'software'])],
        'jenis_hardware' => [
            Rule::requiredIf(fn () => $request->input('kategori') === 'hardware'),
            Rule::in(['komputer', 'jaringan', 'printer', 'telepon']),
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
    $subKategoriMap = [
        'komputer' => '1',
        'jaringan' => '2',
        'printer' => '3',
        'telepon' => '4',
    ];
    $prioritasMap = [
        'rendah' => '1',
        'sedang' => '2',
        'tinggi' => '3',
    ];

    $tanggal = \Carbon\Carbon::parse($validated['tanggal'])->format('dmY');
    $kategoriCode = $kategoriMap[$validated['kategori']];
    $subKategoriValue = $validated['kategori'] === 'hardware' ? ($validated['jenis_hardware'] ?? null) : null;
    $subKategoriCode = $subKategoriValue ? $subKategoriMap[$subKategoriValue] : '0';
    $prioritasCode = $prioritasMap[$validated['prioritas']];

    $room = Room::find($validated['room_id']);
    $roomCode = $room?->room_id ?: (string) $validated['room_id'];
    $roomCode = str_replace('-', '', $roomCode);

    $noTicket = $roomCode . $tanggal . $kategoriCode . $subKategoriCode . $prioritasCode;

    $ticket = HelpdeskTicket::create([
        'no_ticket' => $noTicket,
        'tanggal' => $validated['tanggal'],
        'pelapor' => $validated['pelapor'],
        'room_id' => $validated['room_id'],
        'kategori' => $validated['kategori'],
        'sub_kategori' => $subKategoriValue,
        'kendala' => $validated['kendala'],
        'prioritas' => $validated['prioritas'],
        'petugas_id' => $validated['petugas_id'] ?? null,
        'status' => 'open',
        'keterangan' => $validated['keterangan'] ?? null,
    ]);

    if (!empty($validated['petugas_id'])) {
        $petugas = User::select('id', 'phone')
            ->where('id', $validated['petugas_id'])
            ->where('role', 'petugas')
            ->first();
        if ($petugas && !empty($petugas->phone)) {
            $phone = preg_replace('/\D+/', '', $petugas->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            if (!str_starts_with($phone, '62')) {
                $phone = '62' . $phone;
            }

            $message = "Anda menerima tiket baru. Silakan segera melakukan pengecekan pada detail tiket melalui tautan berikut:\n"
                . "https://rsudzm.simti.xyz/helpdesk/detail-ticket/{$ticket->no_ticket}\n"
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
})->name('helpdesk.store')->middleware('auth');

Route::delete('/helpdesk/{ticket}', function (HelpdeskTicket $ticket) {
    $ticket->delete();
    return redirect('/helpdesk')->with('success', 'Ticket berhasil dihapus.');
})->name('helpdesk.destroy')->middleware('auth');

Route::get('/helpdesk/detail-ticket/{no_ticket}', function (string $noTicket) {
    $ticket = DB::table('helpdesk_tickets')
        ->leftJoin('rooms', 'rooms.id', '=', 'helpdesk_tickets.room_id')
        ->leftJoin('users', 'users.id', '=', 'helpdesk_tickets.petugas_id')
        ->select(
            'helpdesk_tickets.*',
            'rooms.name as room_name',
            'users.name as petugas_name'
        )
        ->where('helpdesk_tickets.no_ticket', $noTicket)
        ->first();

    if (!$ticket) {
        abort(404);
    }

    return view('helpdesk.detailticket', [
        'ticket' => $ticket,
    ]);
})->name('helpdesk.show');

// ---------------- Laporan ----------------
Route::get('/laporan', function () {
    return view('laporan.laporan');
})->name('laporan.index')->middleware('auth');

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

    return view('ipaddress.ipaddr', compact('ipAddrs', 'status', 'q'));
})->name('ipaddr.index')->middleware('auth');

Route::get('/ip-address/tambah', function () {
    return view('ipaddress.addipaddress');
})->name('ipaddr.create')->middleware('auth');

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
})->name('ipaddr.store')->middleware('auth');

Route::delete('/ip-address/{encoded}', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $ip = IpAddr::findOrFail($id);
    $ip->delete();
    return redirect()->route('ipaddr.index')->with('success', 'IP Address berhasil dihapus.');
})->name('ipaddr.destroy')->middleware('auth');

Route::get('/ip-address/{encoded}/edit', function (string $encoded) {
    try {
        $id = decrypt($encoded);
    } catch (DecryptException $e) {
        abort(404);
    }
    $ip = IpAddr::findOrFail($id);
    return view('ipaddress.editipaddress', compact('ip'));
})->name('ipaddr.edit')->middleware('auth');

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
})->name('ipaddr.update')->middleware('auth');

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
})->name('device.index')->middleware('auth');

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
})->name('device.create')->middleware('auth');

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
})->name('device.store')->middleware('auth');

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
})->name('device.destroy')->middleware('auth');

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
})->name('device.edit')->middleware('auth');

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
})->name('device.update')->middleware('auth');

Route::get('/perangkat/spesifikasi-perangkat', function () {
    $usedIds = DeviceSpec::pluck('device_id')->all();
    $devices = Device::whereIn('device_type', ['CPU','PC AIO','Laptop'])
        ->whereNotIn('id', $usedIds)
        ->orderBy('device_name')
        ->get(['id','device_name','device_type']);
    $ramOptions = ['4 GB','8 GB','16 GB','32 GB','64 GB'];
    $capacityOptions = ['128 GB','256 GB','512 GB','1 TB','2 TB'];
    return view('devices.specdevice', compact('devices','ramOptions','capacityOptions'));
})->name('device.spec.form')->middleware('auth');

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
})->name('device.spec.edit')->middleware('auth');

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
})->name('device.spec.save')->middleware('auth');
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
})->name('device.spec.store')->middleware('auth');

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
})->name('device.spec.delete')->middleware('auth');

// ---------------- Lainnya ----------------
Route::get('/profile', function () {
    return view('profile');
})->middleware('auth');

Route::get('/auth/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/auth/otp', function () {
    return view('auth.otp');
})->name('auth.otp');

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

    return redirect()->intended('/home');
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
            return redirect()->intended('/home');
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
