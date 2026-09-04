<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKategoriAlat extends Model
{
    use HasFactory;

    protected $table = 'master_kategori_alat';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'satuan_default',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function peralatan()
    {
        return $this->hasMany(Peralatan::class, 'kategori_alat_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }
}