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

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Kategori termometer = nama kategori diawali "Termometer"
     * (mis. Termometer Dinding, Termometer Tusuk). Dipakai untuk memilih
     * template sticker & input pengukuran suhu di modul Kalibrasi, tanpa
     * bergantung pada kode_kategori.
     */
    public function getIsTermometerAttribute(): bool
    {
        return (bool) preg_match('/^\s*t(h)?ermometer/i', (string) $this->nama_kategori);
    }

    /**
     * Kategori timer = nama kategori diawali "Timer" (mis. Timer Digital,
     * Timer Mekanik). Sama seperti termometer: tidak bergantung kode_kategori.
     */
    public function getIsTimerAttribute(): bool
    {
        return (bool) preg_match('/^\s*timer/i', (string) $this->nama_kategori);
    }

    /**
     * Jenis template sticker kalibrasi: 'termometer' | 'timer' | 'default'.
     * Kategori baru yang butuh template sendiri cukup ditambah di sini.
     */
    public function getJenisStickerAttribute(): string
    {
        if ($this->is_termometer) {
            return 'termometer';
        }
        if ($this->is_timer) {
            return 'timer';
        }
        return 'default';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }
}