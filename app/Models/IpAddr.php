<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpAddr extends Model
{
    use HasFactory;

    protected $table = 'ip_addrs';

    protected $fillable = [
        'ip_address',
        'subnet',
        'status',
        'description',
    ];
}
