<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kalibrasi extends Model
{
    use HasFactory;

    protected $table = 'kalibrasi';

    protected $fillable = [
        'peralatan_id',
        'certificate_number',
        'tanggal_pelaksanaan',
        'dept_bagian',
        'beda_maksimum',
        'hasil',
        'pelaksana',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function peralatan()
    {
        return $this->belongsTo(Peralatan::class, 'peralatan_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeLulus($query)
    {
        return $query->where('hasil', 'Lulus');
    }

    public function scopeTidakLulus($query)
    {
        return $query->where('hasil', 'Tidak Lulus');
    }

    // ── Accessors ───────────────────────────────────────────

    public function getHasilBadgeColorAttribute(): string
    {
        return match($this->hasil) {
            'Lulus'       => 'success',
            'Tidak Lulus' => 'danger',
            default       => 'secondary',
        };
    }
}