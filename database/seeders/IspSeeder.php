<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Room;

class IspSeeder extends Seeder
{
    public function run(): void
    {
        $room = Room::where('name', 'Ruang IT')
            ->orWhere('room_id', 'AM-03')
            ->first();

        if (!$room) {
            return;
        }

        DB::table('isps')->updateOrInsert(
            ['nama_isp' => 'ISP Contoh'],
            [
                'jenis_koneksi' => 'fiber',
                'bandwidth' => '100 Mbps',
                'ip_address' => '10.10.1.1',
                'ruang_installasi' => $room->id,
                'pic_isp' => 'PIC ISP',
                'no_telepon' => '081234567890',
                'status' => 'aktif',
                'keterangan' => 'Seeder ISP contoh',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
