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
        'catatan',
        'certificate_number',
        'jenis_alat_ukur',
        'kapasitas',
    ];

    protected $casts = [
        'tanggal_datang'            => 'date',
        'tanggal_selesai_perbaikan' => 'date',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function riwayatPerbaikan()
    {
        return $this->hasMany(RiwayatPerbaikan::class, 'timbangan_id');
    }

    public function riwayatPenggunaan()
    {
        return $this->hasMany(RiwayatPenggunaan::class, 'timbangan_id');
    }

    public function masterLine()
    {
        return $this->belongsTo(MasterLine::class, 'status_line', 'nama_line');
    }

    /**
     * BARU — semua laporan kerusakan timbangan ini
     */
    public function laporanKerusakan()
    {
        return $this->hasMany(LaporanKerusakan::class, 'timbangan_id');
    }

    /**
     * BARU — laporan kerusakan yang masih aktif (belum selesai)
     */
    public function laporanKerusakanAktif()
    {
        return $this->hasOne(LaporanKerusakan::class, 'timbangan_id')
                    ->whereIn('status', ['Menunggu', 'Diproses'])
                    ->latest();
    }

    /**
     * BARU — penggunaan aktif saat ini (timbangan masih di line ini)
     */
    public function penggunaanAktif()
    {
        return $this->hasOne(RiwayatPenggunaan::class, 'timbangan_id')
                    ->whereColumn('line_tujuan', 'timbangan.status_line')
                    ->latest('tanggal_pemakaian');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByKodeAsset($query, $kodeAsset)
    {
        return $query->where('kode_asset', $kodeAsset);
    }

    public function scopeBaik($query)
    {
        return $query->where('kondisi_saat_ini', 'Baik');
    }

    public function scopeRusak($query)
    {
        return $query->where('kondisi_saat_ini', 'Rusak');
    }

    public function scopeDalamPerbaikan($query)
    {
        return $query->where('kondisi_saat_ini', 'Dalam Perbaikan');
    }

    public function scopeDiLine($query, $line)
    {
        return $query->where('status_line', $line);
    }

    public function scopeDiLab($query)
    {
        return $query->whereNull('status_line');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getKodeAssetLengkapAttribute(): string
    {
        return $this->kode_asset;
    }

    public function getStatusAttribute(): string
    {
        if ($this->kondisi_saat_ini === 'Baik' && $this->status_line) {
            return 'Digunakan di ' . $this->status_line;
        } elseif ($this->kondisi_saat_ini === 'Baik' && !$this->status_line) {
            return 'Siap digunakan (Lab)';
        } else {
            return $this->kondisi_saat_ini;
        }
    }

    public function getStatusLengkapAttribute(): string
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

    public function getStatusLokasiAttribute(): string
    {
        if (!$this->status_line) {
            return 'Di Lab';
        }
        if ($this->status_line === $this->lokasi_asli) {
            return 'Di Lokasi Asli';
        }
        return 'Dipinjam ke ' . $this->status_line;
    }

    public function getTanggalSelesaiPerbaikanTerakhirAttribute()
    {
        return $this->riwayatPerbaikan()
            ->where('status_perbaikan', 'Selesai')
            ->orderBy('tanggal_selesai_perbaikan', 'desc')
            ->value('tanggal_selesai_perbaikan');
    }

    // ── Helper Methods ────────────────────────────────────────────────────────

    public function bisaDigunakan(): bool
    {
        return $this->kondisi_saat_ini === 'Baik';
    }

    public function isSiapDigunakan(): bool
    {
        return $this->kondisi_saat_ini === 'Baik' && $this->status_line === null;
    }

    public function isSedangDigunakan(): bool
    {
        return $this->kondisi_saat_ini === 'Baik' && $this->status_line !== null;
    }

    public function isPerluPerbaikan(): bool
    {
        return in_array($this->kondisi_saat_ini, ['Rusak', 'Dalam Perbaikan'])
            && $this->status_line !== null;
    }

    public function isSedangDiperbaiki(): bool
    {
        return $this->kondisi_saat_ini === 'Dalam Perbaikan' && $this->status_line === null;
    }

    public function sedangDiperbaiki(): bool
    {
        return $this->kondisi_saat_ini === 'Dalam Perbaikan';
    }

    public function isDiLokasiAsli(): bool
    {
        return $this->status_line === $this->lokasi_asli;
    }

    public function isDipinjam(): bool
    {
        return $this->status_line && $this->status_line !== $this->lokasi_asli;
    }

    public function isBaruSelesaiPerbaikan(): bool
    {
        if (!$this->tanggal_selesai_perbaikan) {
            return false;
        }
        return $this->tanggal_selesai_perbaikan->diffInDays(now()) <= 30;
    }

    /**
     * BARU — apakah timbangan ini bisa dilaporkan rusak
     * (harus Baik, sedang di Line, dan belum ada laporan aktif)
     */
    public function bisaDilaporkanRusak(): bool
    {
        return $this->kondisi_saat_ini === 'Baik'
            && $this->status_line !== null;
    }

    public function kalibrasi()
{
    return $this->hasMany(\App\Models\Kalibrasi::class, 'timbangan_id');
}
 
public function kalibrasiterakhir()
{
    return $this->hasOne(\App\Models\Kalibrasi::class, 'timbangan_id')
                ->latest('tanggal_pelaksanaan');
}
}