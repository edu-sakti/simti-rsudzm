<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpdeskTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_ticket',
        'tanggal',
        'pelapor',
        'room_id',
        'kategori',
        'sub_kategori',
        'kendala',
        'prioritas',
        'petugas_id',
        'status',
        'keterangan',
    ];
}
