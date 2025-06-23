<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nama_lengkap',
        'kelas_id',
        'jenis_kelamin',
        'alamat',
        'tanggal_lahir',
        'foto',
        'is_active',
        // 'user_id',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliMurid(): HasOne
    {
        return $this->hasOne(WaliMurid::class);
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class);
    }
    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }
}
