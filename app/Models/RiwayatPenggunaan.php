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
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pemakaian' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function timbangan()
    {
        return $this->belongsTo(Timbangan::class, 'timbangan_id');
    }

    /**
     * BARU — laporan kerusakan yang berasal dari penggunaan ini
     */
    public function laporanKerusakan()
    {
        return $this->hasOne(LaporanKerusakan::class, 'riwayat_penggunaan_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->whereHas('timbangan', function ($q) {
            $q->whereColumn('status_line', 'riwayat_penggunaan.line_tujuan');
        });
    }

    public function scopeSelesai($query)
    {
        return $query->whereHas('timbangan', function ($q) {
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
        return $query->whereHas('timbangan', function ($q) use ($kodeAsset) {
            $q->where('kode_asset', 'like', '%' . $kodeAsset . '%');
        });
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getKodeAssetLengkapAttribute(): string
    {
        return $this->timbangan ? $this->timbangan->kode_asset : '-';
    }

    public function getMerkLengkapAttribute(): string
    {
        return $this->timbangan ? $this->timbangan->merk_tipe_no_seri : '-';
    }

    public function getKondisiAttribute(): string
    {
        return $this->timbangan ? $this->timbangan->kondisi_saat_ini : 'Baik';
    }

    public function getTanggalPemakaianFormattedAttribute(): string
    {
        return $this->tanggal_pemakaian
            ? $this->tanggal_pemakaian->format('d/m/Y')
            : '-';
    }

    public function getStatusPenggunaanAttribute(): string
    {
        if (!$this->timbangan) {
            return 'Selesai';
        }

        if ($this->timbangan->status_line === $this->line_tujuan
            && $this->timbangan->kondisi_saat_ini === 'Baik') {
            return 'Masih Digunakan';
        }

        if (in_array($this->timbangan->kondisi_saat_ini, ['Dalam Perbaikan', 'Rusak'])) {
            return 'Dikembalikan';
        }

        return 'Selesai';
    }

    // ── Helper Methods ────────────────────────────────────────────────────────

    public function isAktif(): bool
    {
        return $this->timbangan
            && $this->timbangan->status_line === $this->line_tujuan
            && $this->timbangan->kondisi_saat_ini === 'Baik';
    }

    public function isDikembalikan(): bool
    {
        return $this->timbangan
            && in_array($this->timbangan->kondisi_saat_ini, ['Dalam Perbaikan', 'Rusak']);
    }

    public function isSelesaiDipindahkan(): bool
    {
        return $this->timbangan
            && $this->timbangan->kondisi_saat_ini === 'Baik'
            && $this->timbangan->status_line !== $this->line_tujuan;
    }

    /**
     * BARU — apakah penggunaan ini sudah punya laporan kerusakan aktif
     */
public function sudahDilaporkanRusak(): bool
{
    // Jika timbangan sudah kembali Baik, anggap laporan lama tidak relevan lagi
    if ($this->timbangan && $this->timbangan->kondisi_saat_ini === 'Baik') {
        return false;
    }

    return $this->laporanKerusakan()->whereIn('status', ['Menunggu', 'Diproses'])->exists();
}
}