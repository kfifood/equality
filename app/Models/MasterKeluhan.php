<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKeluhan extends Model
{
    use HasFactory;

    protected $table    = 'master_keluhan';
    protected $fillable = ['nama_keluhan'];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /**
     * Keluhan ini dipakai di laporan kerusakan mana saja.
     * (many-to-many melalui pivot laporan_kerusakan_keluhan)
     */
    public function laporanKerusakan()
    {
        return $this->belongsToMany(
            LaporanKerusakan::class,
            'laporan_kerusakan_keluhan',
            'master_keluhan_id',
            'laporan_kerusakan_id'
        );
    }
}