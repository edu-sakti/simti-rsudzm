<?php

namespace Database\Seeders;

use App\Models\DeviceSpec;
use Illuminate\Database\Seeder;

class DeviceSpecSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeviceSpec::updateOrCreate(
            ['device_id' => 3],
            [
                'processor'        => 'Intel Core i5',
                'ram'              => '8 GB',
                'storage_type'     => 'SSD',
                'storage_capacity' => '256 GB',
                'gpu'              => 'Integrated',
                'os'               => 'Windows 11 Pro',
                'details'          => 'Contoh spesifikasi untuk perangkat id 3',
            ]
        );
    }
}
