<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTindakan extends Model
{
    use HasFactory;

    protected $table    = 'master_tindakan';
    protected $fillable = ['nama_tindakan'];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /**
     * Tindakan ini dipakai di detail perbaikan mana saja.
     */
    public function detailTindakan()
    {
        return $this->hasMany(DetailTindakanPerbaikan::class, 'master_tindakan_id');
    }
}