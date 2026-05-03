<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTindakanPerbaikan extends Model
{
    use HasFactory;

    protected $table = 'detail_tindakan_perbaikan';

    protected $fillable = [
        'riwayat_perbaikan_id',
        'master_tindakan_id',
        'tanggal_tindakan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_tindakan' => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function riwayatPerbaikan()
    {
        return $this->belongsTo(RiwayatPerbaikan::class, 'riwayat_perbaikan_id');
    }

    public function masterTindakan()
    {
        return $this->belongsTo(MasterTindakan::class, 'master_tindakan_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getNamaTindakanAttribute(): string
    {
        return $this->masterTindakan ? $this->masterTindakan->nama_tindakan : '-';
    }

    public function getTanggalFormattedAttribute(): string
    {
        return $this->tanggal_tindakan
            ? $this->tanggal_tindakan->format('d/m/Y')
            : '-';
    }
}