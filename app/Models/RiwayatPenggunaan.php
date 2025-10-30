<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPenggunaan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_penggunaan';
    
    protected $fillable = [
        'timbangan_id',
        'line_tujuan',
        'tanggal_pemakaian',
        'pic',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_pemakaian' => 'date',
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
        if ($this->timbangan) {
            return $this->timbangan->kode_asset . 
                   ($this->timbangan->nomor_seri_unik ? ' - ' . $this->timbangan->nomor_seri_unik : '');
        }
        return '-';
    }

    // Accessor untuk merk lengkap
    public function getMerkLengkapAttribute()
    {
        return $this->timbangan ? $this->timbangan->merk_tipe_no_seri : '-';
    }

    // Accessor untuk kondisi - AMBIL DARI TIMBANGAN
    public function getKondisiAttribute()
    {
        return $this->timbangan ? $this->timbangan->kondisi_saat_ini : 'Baik';
    }

    // Scope untuk penggunaan aktif (timbangan masih di line tujuan)
    public function scopeAktif($query)
    {
        return $query->whereHas('timbangan', function($q) {
            $q->whereColumn('timbangan.status_line', 'riwayat_penggunaan.line_tujuan');
        });
    }

    // Scope untuk penggunaan selesai (timbangan sudah tidak di line tujuan)
    public function scopeSelesai($query)
    {
        return $query->whereHas('timbangan', function($q) {
            $q->whereColumn('timbangan.status_line', '!=', 'riwayat_penggunaan.line_tujuan')
              ->orWhereNull('timbangan.status_line');
        });
    }

    // Scope untuk penggunaan bulan ini
    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_pemakaian', now()->month)
                    ->whereYear('tanggal_pemakaian', now()->year);
    }

    // Scope untuk penggunaan di line tertentu
    public function scopeDiLine($query, $line)
    {
        return $query->where('line_tujuan', $line);
    }

    // Scope untuk penggunaan dengan kode asset tertentu
    public function scopeDenganKodeAsset($query, $kodeAsset)
    {
        return $query->whereHas('timbangan', function($q) use ($kodeAsset) {
            $q->where('kode_asset', 'like', '%' . $kodeAsset . '%');
        });
    }

    // Accessor untuk format tanggal
    public function getTanggalPemakaianFormattedAttribute()
    {
        return $this->tanggal_pemakaian ? $this->tanggal_pemakaian->format('d/m/Y') : '-';
    }

    // Accessor untuk status penggunaan - LOGIKA DIPERBAIKI
    public function getStatusPenggunaanAttribute()
    {
        // Jika timbangan masih di line yang sama dengan riwayat
        if ($this->timbangan && $this->timbangan->status_line === $this->line_tujuan) {
            return 'Masih Digunakan';
        }
        
        // Jika timbangan dalam perbaikan, statusnya "Dikembalikan"
        if ($this->timbangan && $this->timbangan->kondisi_saat_ini === 'Dalam Perbaikan') {
            return 'Dikembalikan';
        }
        
        // Jika timbangan rusak, statusnya "Dikembalikan" 
        if ($this->timbangan && $this->timbangan->kondisi_saat_ini === 'Rusak') {
            return 'Dikembalikan';
        }
        
        // Default: Selesai (kondisi baik tapi sudah tidak di line tujuan)
        return 'Selesai';
    }

    // Method untuk cek apakah penggunaan masih aktif
    public function isAktif()
    {
        return $this->timbangan && $this->timbangan->status_line === $this->line_tujuan;
    }

    // Method untuk cek apakah timbangan dikembalikan karena rusak/perbaikan
    public function isDikembalikan()
    {
        return $this->timbangan && 
               ($this->timbangan->kondisi_saat_ini === 'Dalam Perbaikan' || 
                $this->timbangan->kondisi_saat_ini === 'Rusak');
    }
}