<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cctv;
use App\Models\Room;

class CctvSeeder extends Seeder
{
    public function run(): void
    {
        // Cari ruangan IT, jika tidak ada gunakan ruangan pertama
        $roomId = Room::where('name', 'like', '%IT%')->orderBy('room_id')->value('room_id')
            ?? Room::orderBy('room_id')->value('room_id');

        if (!$roomId) {
            return; // tidak ada ruangan, skip
        }

        Cctv::updateOrCreate(
            ['ip' => '192.168.1.10'],
            [
                'room_id' => $roomId,
                'status' => 'aktif',
                'ip' => '192.168.1.10',
                'keterangan' => 'Data CCTV Tester',
            ]
        );
    }
}
