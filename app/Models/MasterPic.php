<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPic extends Model
{
    use HasFactory;

    protected $table = 'master_pic';

    protected $fillable = [
        'kode_pic',
        'nama_pic',
        'jabatan',
        'line_id',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // Scope PIC aktif
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    // Relasi ke MasterLine
    public function line()
    {
        return $this->belongsTo(MasterLine::class, 'line_id');
    }
}