<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'room_id',
        'kategori',
        'name',
    ];

    // Primary key tetap kolom "id" (default) agar relasi ke users.room_id bekerja
}
