<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLine extends Model
{
    use HasFactory;

    protected $table = 'master_line';
    
    protected $fillable = [
        'kode_line',
        'nama_line',
        'department',
        'status_aktif'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Scope untuk line aktif
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    // Relasi ke riwayat penggunaan (jika diperlukan)
    public function riwayatPenggunaan()
    {
        return $this->hasMany(RiwayatPenggunaan::class, 'line_tujuan', 'nama_line');
    }

    // Relasi ke timbangan (jika diperlukan)
    public function timbangan()
    {
        return $this->hasMany(Timbangan::class, 'status_line', 'nama_line');
    }
}