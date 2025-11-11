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
    'merk_tipe_no_seri', 
    'tanggal_datang',
    'lokasi_asli',
    'status_line',
    'tanggal_selesai_perbaikan',
    'kondisi_saat_ini',
    'catatan'
];
    protected $casts = [
        'tanggal_datang' => 'date',
        'tanggal_selesai_perbaikan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Accessor untuk menampilkan kode asset lengkap
    public function getKodeAssetLengkapAttribute()
    {
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

    // Method untuk cek apakah di lokasi asli
public function isDiLokasiAsli()
{
    return $this->status_line === $this->lokasi_asli;
}

// Method untuk cek apakah dipinjam
public function isDipinjam()
{
    return $this->status_line && $this->status_line !== $this->lokasi_asli;
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

    // Method baru untuk mendapatkan status lokasi
public function getStatusLokasiAttribute()
{
    if (!$this->status_line) {
        return 'Di Lab';
    }
    
    if ($this->status_line === $this->lokasi_asli) {
        return 'Di Lokasi Asli';
    }
    
    return 'Dipinjam ke ' . $this->status_line;
}

// Method baru untuk mendapatkan tanggal selesai perbaikan terakhir
public function getTanggalSelesaiPerbaikanTerakhirAttribute()
{
    $perbaikanTerakhir = $this->riwayatPerbaikan()
        ->where('status_perbaikan', 'Selesai')
        ->orderBy('tanggal_selesai_perbaikan', 'desc')
        ->first();
    
    return $perbaikanTerakhir ? $perbaikanTerakhir->tanggal_selesai_perbaikan : null;
}

// Method untuk cek apakah baru selesai perbaikan (dalam 30 hari)
public function isBaruSelesaiPerbaikan()
{
    if (!$this->tanggal_selesai_perbaikan) {
        return false;
    }
    
    return $this->tanggal_selesai_perbaikan->diffInDays(now()) <= 30;
}
}