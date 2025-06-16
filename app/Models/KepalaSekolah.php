<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KepalaSekolah extends Model
{
    protected $table = 'kepala_sekolahs';

    protected $fillable = [
        'user_id',
        'nip',
        'pangkat',
        'golongan',
        'nama_lengkap',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
