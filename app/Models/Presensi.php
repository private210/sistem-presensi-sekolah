<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    protected $table = 'presensis';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'wali_kelas_id',
        'tanggal_presensi',
        'status',
        'keterangan',
        'pertemuan_ke'
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(WaliKelas::class);
    }

    // TAMBAHAN: Accessor untuk mendapatkan status yang lebih user-friendly
    public function getStatusDisplayAttribute(): string
    {
        return $this->status ?? 'Hari Libur';
    }

    // TAMBAHAN: Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        if ($status === 'libur') {
            return $query->whereNull('status');
        }

        return $query->where('status', $status);
    }

    // TAMBAHAN: Scope untuk hari sekolah saja (yang memiliki status)
    public function scopeSchoolDaysOnly($query)
    {
        return $query->whereNotNull('status');
    }

    // TAMBAHAN: Scope untuk hari libur saja (yang tidak memiliki status)
    public function scopeHolidaysOnly($query)
    {
        return $query->whereNull('status');
    }
}
