<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKerusakan extends Model
{
    use HasFactory;

    protected $table = 'laporan_kerusakan';

    protected $fillable = [
        'timbangan_id',
        'riwayat_penggunaan_id',
        'line_asal',
        'pic_pelapor',
        'tanggal_laporan',
        'keterangan_tambahan',
        'status',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function timbangan()
    {
        return $this->belongsTo(Timbangan::class, 'timbangan_id');
    }

    public function riwayatPenggunaan()
    {
        return $this->belongsTo(RiwayatPenggunaan::class, 'riwayat_penggunaan_id');
    }

    /**
     * Keluhan-keluhan yang dilaporkan (many-to-many)
     */
    public function keluhanList()
    {
        return $this->belongsToMany(
            MasterKeluhan::class,
            'laporan_kerusakan_keluhan',
            'laporan_kerusakan_id',
            'master_keluhan_id'
        );
    }

    /**
     * Proses perbaikan yang menangani laporan ini (bisa lebih dari satu histori)
     */
    public function riwayatPerbaikan()
    {
        return $this->hasMany(RiwayatPerbaikan::class, 'laporan_kerusakan_id');
    }

    /**
     * Proses perbaikan yang sedang aktif (belum selesai)
     */
    public function perbaikanAktif()
    {
        return $this->hasOne(RiwayatPerbaikan::class, 'laporan_kerusakan_id')
                    ->whereNotIn('status_perbaikan', ['Selesai'])
                    ->latest();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'Menunggu');
    }

    public function scopeDiproses($query)
    {
        return $query->where('status', 'Diproses');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'Selesai');
    }

    public function scopeBelumSelesai($query)
    {
        return $query->whereIn('status', ['Menunggu', 'Diproses']);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Nama-nama keluhan digabung jadi satu string untuk tampilan ringkas
     */
    public function getKeluhanRingkasAttribute(): string
    {
        return $this->keluhanList->pluck('nama_keluhan')->join(', ') ?: '-';
    }

    /**
     * Warna badge berdasarkan status laporan
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Menunggu' => 'warning',
            'Diproses' => 'info',
            'Selesai'  => 'success',
            default    => 'secondary',
        };
    }

    /**
     * Icon berdasarkan status laporan
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'Menunggu' => 'clock',
            'Diproses' => 'tools',
            'Selesai'  => 'check-circle',
            default    => 'question-circle',
        };
    }

    /**
     * Status perbaikan terkini dari riwayat_perbaikan yang terkait
     */
    public function getStatusPerbaikanTerkiniAttribute(): string
    {
        $perbaikan = $this->riwayatPerbaikan()->latest()->first();
        return $perbaikan ? $perbaikan->status_perbaikan : '-';
    }

    // ── Helper Methods ────────────────────────────────────────────────────────

    public function isMenunggu(): bool
    {
        return $this->status === 'Menunggu';
    }

    public function isDiproses(): bool
    {
        return $this->status === 'Diproses';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'Selesai';
    }

    public function bisaDiproses(): bool
    {
        return !$this->isSelesai();
    }
}