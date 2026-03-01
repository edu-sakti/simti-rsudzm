<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Room;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'device_name',
        'device_type',
        'brand',
        'model',
        'condition',
        'status',
        'notes',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function spec()
    {
        return $this->hasOne(DeviceSpec::class);
    }
}
