<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPerbaikan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_perbaikan';

    protected $fillable = [
        'laporan_kerusakan_id',
        'peralatan_id',
        'line_sebelumnya',
        'penggunaan_terakhir',    // dipertahankan untuk kompatibilitas data lama
        'deskripsi_keluhan',      // dipertahankan untuk kompatibilitas data lama
        'tindakan_perbaikan',     // dipertahankan untuk kompatibilitas data lama
        'perbaikan_eksternal',    // dipertahankan untuk kompatibilitas data lama
        'tanggal_masuk_lab',
        'tanggal_selesai_perbaikan',
        'line_tujuan',
        'status_perbaikan',
        'catatan',
        'jenis_perbaikan',
        'pic_teknik',
    ];

    protected $casts = [
        'tanggal_masuk_lab'         => 'date',
        'tanggal_selesai_perbaikan' => 'date',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function peralatan()
    {
        return $this->belongsTo(Peralatan::class, 'peralatan_id');
    }

    /**
     * Laporan kerusakan yang memicu perbaikan ini
     */
    public function laporanKerusakan()
    {
        return $this->belongsTo(LaporanKerusakan::class, 'laporan_kerusakan_id');
    }

    /**
     * Daftar tindakan yang diambil selama perbaikan ini
     */
    public function detailTindakan()
    {
        return $this->hasMany(DetailTindakanPerbaikan::class, 'riwayat_perbaikan_id')
                    ->with('masterTindakan')
                    ->orderBy('tanggal_tindakan');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getKodeAssetLengkapAttribute(): string
    {
        return $this->peralatan ? $this->peralatan->kode_asset : '-';
    }

    public function getMerkLengkapAttribute(): string
    {
        // FIX: field lama 'merk_tipe_no_seri' sudah tidak ada di model Peralatan,
        // sekarang jadi accessor 'merk_tipe_lengkap' (gabungan merk + type + serial_number).
        return $this->peralatan ? $this->peralatan->merk_tipe_lengkap : '-';
    }

    public function getKondisiPeralatanAttribute(): string
    {
        return $this->peralatan ? $this->peralatan->kondisi_saat_ini : '-';
    }

    /** Durasi perbaikan dalam hari */
    public function getDurasiPerbaikanAttribute(): ?int
    {
        if ($this->tanggal_selesai_perbaikan && $this->tanggal_masuk_lab) {
            return $this->tanggal_masuk_lab->diffInDays($this->tanggal_selesai_perbaikan);
        }
        if ($this->tanggal_masuk_lab) {
            return $this->tanggal_masuk_lab->diffInDays(now());
        }
        return null;
    }

    /**
     * Nama-nama tindakan yang sudah dilakukan (ringkas)
     */
    public function getTindakanRingkasAttribute(): string
    {
        // Prioritaskan dari detail_tindakan (struktur baru)
        if ($this->relationLoaded('detailTindakan') && $this->detailTindakan->count()) {
            return $this->detailTindakan
                ->map(fn($d) => $d->masterTindakan?->nama_tindakan ?? '-')
                ->join(', ');
        }
        // Fallback ke field lama
        return $this->tindakan_perbaikan ?? '-';
    }

    /**
     * Keluhan dari laporan terkait (ringkas)
     */
    public function getKeluhanRingkasAttribute(): string
    {
        if ($this->laporanKerusakan && $this->laporanKerusakan->keluhanList->count()) {
            return $this->laporanKerusakan->keluhan_ringkas;
        }
        // Fallback ke field lama
        return $this->deskripsi_keluhan ?? '-';
    }

    /** Warna badge Bootstrap berdasarkan status */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_perbaikan) {
            'Menunggu Penanganan' => 'warning',
            'Masuk Lab'           => 'secondary',
            'Perbaikan Internal'  => 'primary',
            'Dikirim Eksternal'   => 'info',
            'Selesai'             => 'success',
            default               => 'secondary',
        };
    }

    /** Icon Bootstrap Icons berdasarkan status */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status_perbaikan) {
            'Menunggu Penanganan' => 'clock',
            'Masuk Lab'           => 'box-arrow-in-down',
            'Perbaikan Internal'  => 'tools',
            'Dikirim Eksternal'   => 'arrow-right-circle',
            'Selesai'             => 'check-circle',
            default               => 'question-circle',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->whereNotIn('status_perbaikan', ['Selesai']);
    }

    public function scopeSelesai($query)
    {
        return $query->where('status_perbaikan', 'Selesai');
    }

    // ── Helper Methods ────────────────────────────────────────────────────────

    public function isSelesai(): bool
    {
        return $this->status_perbaikan === 'Selesai';
    }

    public function canBeUpdated(): bool
    {
        return !$this->isSelesai();
    }

    public function isDalamPerbaikan(): bool
    {
        return in_array($this->status_perbaikan, [
            'Menunggu Penanganan',
            'Masuk Lab',
            'Perbaikan Internal',
            'Dikirim Eksternal',
        ]);
    }
}