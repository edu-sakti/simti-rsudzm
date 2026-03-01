<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IpAddr;

class IpAddrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IpAddr::firstOrCreate(
            ['ip_address' => '10.10.1.253'],
            [
                'subnet' => '255.255.0.0',
                'status' => 'available',
                'description' => 'IP Address server SIMRS',
            ]
        );
    }
}
