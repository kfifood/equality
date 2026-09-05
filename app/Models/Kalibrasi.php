<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kalibrasi extends Model
{
    use HasFactory;

    protected $table = 'kalibrasi';

    protected $fillable = [
        'peralatan_id',
        'calibration_number',
        'tanggal_pelaksanaan',
        'dept_bagian',
        'beda_maksimum',
        'data_pengukuran',
        'hasil',
        'pelaksana',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
        'data_pengukuran'     => 'array',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function peralatan()
    {
        return $this->belongsTo(Peralatan::class, 'peralatan_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeLulus($query)
    {
        return $query->where('hasil', 'Lulus');
    }

    public function scopeTidakLulus($query)
    {
        return $query->where('hasil', 'Tidak Lulus');
    }

    // ── Accessors ───────────────────────────────────────────

    public function getHasilBadgeColorAttribute(): string
    {
        return match($this->hasil) {
            'Lulus'       => 'success',
            'Tidak Lulus' => 'danger',
            default       => 'secondary',
        };
    }

    /**
     * Daftar baris pengukuran (mis. suhu alat/master untuk Thermometer),
     * selalu berupa array — kosong kalau tidak ada / bukan kategori yang
     * pakai struktur ini (mis. Timbangan, yang pakai 'beda_maksimum').
     */
    public function getPengukuranAttribute(): array
    {
        return $this->data_pengukuran['pengukuran'] ?? [];
    }

    /**
     * Ringkasan hasil kalibrasi jadi satu baris teks, generik mengikuti
     * kategori alat — dipakai di tabel index & sticker supaya tidak perlu
     * kolom terpisah tiap kategori.
     * - Timbangan (ada beda_maksimum): tampilkan apa adanya.
     * - Thermometer (ada data_pengukuran.pengukuran): gabungkan tiap baris
     *   "Alat/Master".
     * - Tidak ada keduanya: '-'.
     */
    public function getHasilPengukuranRingkasAttribute(): string
    {
        if (!empty($this->beda_maksimum)) {
            return $this->beda_maksimum;
        }

        if (!empty($this->pengukuran)) {
            return collect($this->pengukuran)
                ->map(fn($p, $i) => ($i + 1) . ') ' . ($p['suhu_alat'] ?? '-') . '°C / ' . ($p['suhu_master'] ?? '-') . '°C')
                ->join(', ');
        }

        return '-';
    }
}