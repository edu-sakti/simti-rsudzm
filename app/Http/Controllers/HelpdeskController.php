<?php

namespace App\Http\Controllers;

use App\Models\HelpdeskTicket;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class HelpdeskController extends Controller
{
    public function index()
    {
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
    }

    public function store(Request $request)
    {
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
            'status' => ['required', Rule::in(['open', 'assigned', 'in_progress', 'resolved', 'closed'])],
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
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        if (!empty($validated['petugas_id'])) {
            $petugas = User::select('phone', 'name')->find($validated['petugas_id']);
            $roomName = $room?->name ?: '-';
            $tanggalLabel = \Carbon\Carbon::parse($validated['tanggal'])->format('d/m/Y');
            if ($petugas && !empty($petugas->phone)) {
                $message = "📢 NOTIFIKASI TIKET HELPDESK\n\n" .
                    "Berikut tiket yang perlu segera ditindaklanjuti:\n\n" .
                    "No. Ticket : {$noTicket}\n" .
                    "Tanggal    : {$tanggalLabel}\n" .
                    "Pelapor    : {$validated['pelapor']}\n" .
                    "Ruangan    : {$roomName}\n" .
                    "Kategori   : {$validated['kategori']}\n" .
                    "Sub Kategori : " . ($subKategoriValue ?: '-') . "\n\n" .
                    "Kendala :\n{$validated['kendala']}\n\n" .
                    "Prioritas  : {$validated['prioritas']}\n\n" .
                    "Keterangan :\n" . ($validated['keterangan'] ?: '-') . "\n\n" .
                    "Mohon untuk segera melakukan pengecekan dan penanganan terhadap tiket tersebut.\n\n" .
                    "Terima kasih.";

                $baseUrl = env('WA_GATEWAY_URL', 'http://127.0.0.1:3001');
                try {
                    Http::withHeaders([
                        'X-API-KEY' => env('WA_GATEWAY_TOKEN', ''),
                    ])->post($baseUrl . '/send', [
                        'phone' => $petugas->phone,
                        'message' => $message,
                    ]);
                } catch (\Throwable $e) {
                    // abaikan jika gagal kirim WA
                }
            }
        }

        return redirect('/helpdesk')->with('success', 'Ticket berhasil dibuat.');
    }

    public function destroy(HelpdeskTicket $ticket)
    {
        $ticket->delete();

        return redirect('/helpdesk')->with('success', 'Ticket berhasil dihapus.');
    }
}
