<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaliMurid extends Model
{
    protected $table = 'wali_murids';

    protected $fillable = [
        'user_id',
        'siswa_id',
        'nama_lengkap',
        'hubungan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class);
    }
}
