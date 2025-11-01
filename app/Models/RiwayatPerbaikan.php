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
        'tanggal_masuk_lab' => 'date',
        'tanggal_selesai_perbaikan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relasi ke timbangan
    public function timbangan()
    {
        return $this->belongsTo(Timbangan::class, 'timbangan_id');
    }

    // Accessor untuk kode asset lengkap
    public function getKodeAssetLengkapAttribute()
    {
        return $this->timbangan ? $this->timbangan->kode_asset : '-';
    }

    // Accessor untuk merk lengkap
    public function getMerkLengkapAttribute()
    {
        return $this->timbangan ? $this->timbangan->merk_tipe_no_seri : '-';
    }

    // Accessor untuk kondisi timbangan
    public function getKondisiTimbanganAttribute()
    {
        return $this->timbangan ? $this->timbangan->kondisi_saat_ini : '-';
    }

    // Scope untuk perbaikan aktif
    public function scopeAktif($query)
    {
        return $query->whereIn('status_perbaikan', ['Masuk Lab', 'Dalam Perbaikan', 'Dikirim Eksternal']);
    }

    // Scope untuk perbaikan selesai
    public function scopeSelesai($query)
    {
        return $query->where('status_perbaikan', 'Selesai');
    }

    // Accessor untuk durasi perbaikan
    public function getDurasiPerbaikanAttribute()
    {
        if ($this->tanggal_selesai_perbaikan && $this->tanggal_masuk_lab) {
            return $this->tanggal_masuk_lab->diffInDays($this->tanggal_selesai_perbaikan);
        }
        
        // Jika belum selesai, hitung dari tanggal masuk sampai sekarang
        if ($this->tanggal_masuk_lab) {
            return $this->tanggal_masuk_lab->diffInDays(now());
        }
        
        return null;
    }

    // Method untuk cek apakah perbaikan sudah selesai
    public function isSelesai()
    {
        return $this->status_perbaikan === 'Selesai';
    }

    // Method untuk cek apakah masih bisa diupdate
    public function canBeUpdated()
    {
        return !$this->isSelesai();
    }

    // Accessor untuk status warna
    public function getStatusColorAttribute()
    {
        return match($this->status_perbaikan) {
            'Masuk Lab' => 'secondary',
            'Dalam Perbaikan' => 'warning',
            'Selesai' => 'success',
            'Dikirim Eksternal' => 'info',
            default => 'secondary'
        };
    }

    // Accessor untuk status icon
    public function getStatusIconAttribute()
    {
        return match($this->status_perbaikan) {
            'Masuk Lab' => 'box-arrow-in-down',
            'Dalam Perbaikan' => 'tools',
            'Selesai' => 'check-circle',
            'Dikirim Eksternal' => 'arrow-right-circle',
            default => 'question-circle'
        };
    }

    // Method untuk cek apakah masih dalam perbaikan
    public function isDalamPerbaikan()
    {
        return in_array($this->status_perbaikan, ['Masuk Lab', 'Dalam Perbaikan', 'Dikirim Eksternal']);
    }
}