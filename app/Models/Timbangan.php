<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timbangan extends Model
{
    use HasFactory;

    protected $table = 'timbangan';
    
    protected $fillable = [
        'kode_asset',
        'nomor_seri_unik', // TAMBAHKAN INI
        'merk_tipe_no_seri',
        'tanggal_datang',
        'status_line',
        'kondisi_saat_ini'
    ];

    protected $casts = [
        'tanggal_datang' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Accessor untuk menampilkan kode asset lengkap
    public function getKodeAssetLengkapAttribute()
    {
        if ($this->nomor_seri_unik) {
            return $this->kode_asset . ' - ' . $this->nomor_seri_unik;
        }
        return $this->kode_asset;
    }

    // Relasi ke riwayat perbaikan
    public function riwayatPerbaikan()
    {
        return $this->hasMany(RiwayatPerbaikan::class, 'timbangan_id');
    }

    // Relasi ke riwayat penggunaan
    public function riwayatPenggunaan()
    {
        return $this->hasMany(RiwayatPenggunaan::class, 'timbangan_id');
    }

    // Relasi ke master line
    public function masterLine()
    {
        return $this->belongsTo(MasterLine::class, 'status_line', 'nama_line');
    }

    // Scope untuk mencari berdasarkan kode asset
    public function scopeByKodeAsset($query, $kodeAsset)
    {
        return $query->where('kode_asset', $kodeAsset);
    }

    // Scope untuk timbangan aktif (baik)
    public function scopeBaik($query)
    {
        return $query->where('kondisi_saat_ini', 'Baik');
    }

    // Scope untuk timbangan rusak
    public function scopeRusak($query)
    {
        return $query->where('kondisi_saat_ini', 'Rusak');
    }

    // Scope untuk timbangan dalam perbaikan
    public function scopeDalamPerbaikan($query)
    {
        return $query->where('kondisi_saat_ini', 'Dalam Perbaikan');
    }

    // Scope untuk timbangan di line tertentu
    public function scopeDiLine($query, $line)
    {
        return $query->where('status_line', $line);
    }

    // Scope untuk timbangan di lab (tidak ada status_line)
    public function scopeDiLab($query)
    {
        return $query->whereNull('status_line');
    }

    // Accessor untuk status
    public function getStatusAttribute()
    {
        if ($this->kondisi_saat_ini === 'Baik' && $this->status_line) {
            return 'Digunakan di ' . $this->status_line;
        } elseif ($this->kondisi_saat_ini === 'Baik' && !$this->status_line) {
            return 'Siap digunakan (Lab)';
        } else {
            return $this->kondisi_saat_ini;
        }
    }

    // Method untuk cek apakah bisa digunakan
    public function bisaDigunakan()
    {
        return $this->kondisi_saat_ini === 'Baik';
    }

    // Method untuk cek apakah sedang diperbaiki
    public function sedangDiperbaiki()
    {
        return $this->kondisi_saat_ini === 'Dalam Perbaikan';
    }

    // Method untuk cek apakah siap digunakan (di Lab dan kondisi Baik)
    public function isSiapDigunakan()
    {
        return $this->kondisi_saat_ini === 'Baik' && $this->status_line === null;
    }

    // Method untuk cek apakah sedang digunakan
    public function isSedangDigunakan()
    {
        return $this->kondisi_saat_ini === 'Baik' && $this->status_line !== null;
    }

    // Method untuk cek apakah perlu perbaikan
    public function isPerluPerbaikan()
    {
        return in_array($this->kondisi_saat_ini, ['Rusak', 'Dalam Perbaikan']) && $this->status_line !== null;
    }

    // Method untuk cek apakah sedang diperbaiki
    public function isSedangDiperbaiki()
    {
        return $this->kondisi_saat_ini === 'Dalam Perbaikan' && $this->status_line === null;
    }

    // Accessor untuk status lengkap
    public function getStatusLengkapAttribute()
    {
        if ($this->isSiapDigunakan()) {
            return 'Siap Digunakan (Lab)';
        } elseif ($this->isSedangDigunakan()) {
            return 'Digunakan di ' . $this->status_line;
        } elseif ($this->isSedangDiperbaiki()) {
            return 'Dalam Perbaikan (Lab)';
        } elseif ($this->isPerluPerbaikan()) {
            return 'Perlu Perbaikan dari ' . $this->status_line;
        } else {
            return $this->kondisi_saat_ini;
        }
    }
}