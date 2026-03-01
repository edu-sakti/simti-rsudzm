<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSpec extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'processor',
        'ram',
        'storage_type',
        'storage_capacity',
        'gpu',
        'os',
        'details',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
