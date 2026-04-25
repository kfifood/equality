<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPerbaikan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_perbaikan';
    
    protected $fillable = [
        'timbangan_id',
        'line_sebelumnya',
        'penggunaan_terakhir',
        'deskripsi_keluhan',
        'tindakan_perbaikan',
        'perbaikan_eksternal',
        'tanggal_masuk_lab',
        'tanggal_selesai_perbaikan',
        'line_tujuan',
        'status_perbaikan'
    ];

    protected $casts = [
        'tanggal_masuk_lab'         => 'date',
        'tanggal_selesai_perbaikan' => 'date',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime'
    ];

    // ──────────────────────────────────────────────
    // Relasi
    // ──────────────────────────────────────────────

    public function timbangan()
    {
        return $this->belongsTo(Timbangan::class, 'timbangan_id');
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    public function getKodeAssetLengkapAttribute()
    {
        return $this->timbangan ? $this->timbangan->kode_asset : '-';
    }

    public function getMerkLengkapAttribute()
    {
        return $this->timbangan ? $this->timbangan->merk_tipe_no_seri : '-';
    }

    public function getKondisiTimbanganAttribute()
    {
        return $this->timbangan ? $this->timbangan->kondisi_saat_ini : '-';
    }

    /** Durasi perbaikan dalam hari */
    public function getDurasiPerbaikanAttribute()
    {
        if ($this->tanggal_selesai_perbaikan && $this->tanggal_masuk_lab) {
            return $this->tanggal_masuk_lab->diffInDays($this->tanggal_selesai_perbaikan);
        }
        if ($this->tanggal_masuk_lab) {
            return $this->tanggal_masuk_lab->diffInDays(now());
        }
        return null;
    }

    /**
     * Bootstrap Bootstrap color class untuk badge status.
     *
     * Masuk Lab        → secondary (abu)
     * Perbaikan Internal → warning  (kuning)
     * Dikirim Eksternal  → info     (biru muda)
     * Selesai           → success   (hijau)
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status_perbaikan) {
            'Masuk Lab'          => 'secondary',
            'Perbaikan Internal' => 'warning',
            'Dikirim Eksternal'  => 'info',
            'Selesai'            => 'success',
            default              => 'secondary'
        };
    }

    /** Bootstrap Icon untuk tiap status */
    public function getStatusIconAttribute(): string
    {
        return match($this->status_perbaikan) {
            'Masuk Lab'          => 'box-arrow-in-down',
            'Perbaikan Internal' => 'tools',
            'Dikirim Eksternal'  => 'arrow-right-circle',
            'Selesai'            => 'check-circle',
            default              => 'question-circle'
        };
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /** Semua perbaikan yang belum selesai */
    public function scopeAktif($query)
    {
        return $query->whereIn('status_perbaikan', [
            'Masuk Lab',
            'Perbaikan Internal',
            'Dikirim Eksternal',
        ]);
    }

    public function scopeSelesai($query)
    {
        return $query->where('status_perbaikan', 'Selesai');
    }

    // ──────────────────────────────────────────────
    // Helper methods
    // ──────────────────────────────────────────────

    public function isSelesai(): bool
    {
        return $this->status_perbaikan === 'Selesai';
    }

    public function canBeUpdated(): bool
    {
        return !$this->isSelesai();
    }

    public function isDalamPerbaikan(): bool
    {
        return in_array($this->status_perbaikan, [
            'Masuk Lab',
            'Perbaikan Internal',
            'Dikirim Eksternal',
        ]);
    }
}