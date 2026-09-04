<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPenggunaan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_penggunaan';

    protected $fillable = [
        'peralatan_id',
        'line_tujuan',
        'tanggal_pemakaian',
        'pic',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pemakaian' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function peralatan()
    {
        return $this->belongsTo(Peralatan::class, 'peralatan_id');
    }

    /**
     * Laporan kerusakan yang berasal dari penggunaan ini
     */
    public function laporanKerusakan()
    {
        return $this->hasOne(LaporanKerusakan::class, 'riwayat_penggunaan_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->whereHas('peralatan', function ($q) {
            $q->whereColumn('status_line', 'riwayat_penggunaan.line_tujuan');
        });
    }

    public function scopeSelesai($query)
    {
        return $query->whereHas('peralatan', function ($q) {
            $q->where(function ($inner) {
                $inner->whereColumn('status_line', '!=', 'riwayat_penggunaan.line_tujuan')
                      ->orWhereNull('status_line');
            });
        });
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_pemakaian', now()->month)
                     ->whereYear('tanggal_pemakaian', now()->year);
    }

    public function scopeDiLine($query, $line)
    {
        return $query->where('line_tujuan', $line);
    }

    public function scopeDenganKodeAsset($query, $kodeAsset)
    {
        return $query->whereHas('peralatan', function ($q) use ($kodeAsset) {
            $q->where('kode_asset', 'like', '%' . $kodeAsset . '%');
        });
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getKodeAssetLengkapAttribute(): string
    {
        return $this->peralatan ? $this->peralatan->kode_asset : '-';
    }

    public function getMerkLengkapAttribute(): string
    {
        return $this->peralatan ? $this->peralatan->merk_tipe_lengkap : '-';
    }

    public function getKondisiAttribute(): string
    {
        return $this->peralatan ? $this->peralatan->kondisi_saat_ini : 'Baik';
    }

    public function getTanggalPemakaianFormattedAttribute(): string
    {
        return $this->tanggal_pemakaian
            ? $this->tanggal_pemakaian->format('d/m/Y')
            : '-';
    }

    public function getStatusPenggunaanAttribute(): string
    {
        if (!$this->peralatan) {
            return 'Selesai';
        }

        if ($this->peralatan->status_line === $this->line_tujuan
            && $this->peralatan->kondisi_saat_ini === 'Baik') {
            return 'Masih Digunakan';
        }

        if (in_array($this->peralatan->kondisi_saat_ini, ['Dalam Perbaikan', 'Rusak'])) {
            return 'Dikembalikan';
        }

        return 'Selesai';
    }

    // ── Helper Methods ────────────────────────────────────────────────────────

    public function isAktif(): bool
    {
        return $this->peralatan
            && $this->peralatan->status_line === $this->line_tujuan
            && $this->peralatan->kondisi_saat_ini === 'Baik';
    }

    public function isDikembalikan(): bool
    {
        return $this->peralatan
            && in_array($this->peralatan->kondisi_saat_ini, ['Dalam Perbaikan', 'Rusak']);
    }

    public function isSelesaiDipindahkan(): bool
    {
        return $this->peralatan
            && $this->peralatan->kondisi_saat_ini === 'Baik'
            && $this->peralatan->status_line !== $this->line_tujuan;
    }

    /**
     * Apakah penggunaan ini sudah punya laporan kerusakan aktif
     */
    public function sudahDilaporkanRusak(): bool
    {
        // Jika peralatan sudah kembali Baik, anggap laporan lama tidak relevan lagi
        if ($this->peralatan && $this->peralatan->kondisi_saat_ini === 'Baik') {
            return false;
        }

        return $this->laporanKerusakan()->whereIn('status', ['Menunggu', 'Diproses'])->exists();
    }
}