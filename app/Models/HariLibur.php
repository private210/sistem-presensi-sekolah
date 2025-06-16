<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_liburs';

    protected $fillable = [
        'nama_hari_libur',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
}
