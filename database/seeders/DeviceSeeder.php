<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Room;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $roomId = Room::query()
            ->where('name', 'like', '%Ruang IT%')
            ->orderBy('room_id')
            ->value('room_id');

        if (!$roomId) {
            $roomId = Room::query()->orderBy('room_id')->value('room_id');
        }

        Device::updateOrCreate(
            ['device_name' => 'Router Core'],
            [
                'room_id' => $roomId,
                'device_type' => 'Router',
                'brand' => 'Cisco',
                'model' => 'ISR4321',
                'condition' => 'Good',
                'status' => 'Active',
                'notes' => 'Perangkat contoh untuk seeder',
            ]
        );
    }
}
