<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peralatan extends Model
{
    use HasFactory;

    protected $table = 'peralatan';

    protected $fillable = [
        'kategori_alat_id',
        'kode_asset',
        'merk',
        'type',
        'serial_number',
        'tanggal_datang',
        'lokasi_asli',
        'status_line',
        'tanggal_selesai_perbaikan',
        'kondisi_saat_ini',
        'catatan',
        'certificate_number',
        'spesifikasi',
    ];

    protected $casts = [
        'tanggal_datang'            => 'date',
        'tanggal_selesai_perbaikan' => 'date',
        'spesifikasi'               => 'array',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function kategoriAlat()
    {
        return $this->belongsTo(MasterKategoriAlat::class, 'kategori_alat_id');
    }

    public function riwayatPerbaikan()
    {
        return $this->hasMany(RiwayatPerbaikan::class, 'peralatan_id');
    }

    public function riwayatPenggunaan()
    {
        return $this->hasMany(RiwayatPenggunaan::class, 'peralatan_id');
    }

    public function masterLine()
    {
        return $this->belongsTo(MasterLine::class, 'status_line', 'nama_line');
    }

    /**
     * Semua laporan kerusakan peralatan ini
     */
    public function laporanKerusakan()
    {
        return $this->hasMany(LaporanKerusakan::class, 'peralatan_id');
    }

    /**
     * Laporan kerusakan yang masih aktif (belum selesai)
     */
    public function laporanKerusakanAktif()
    {
        return $this->hasOne(LaporanKerusakan::class, 'peralatan_id')
                    ->whereIn('status', ['Menunggu', 'Diproses'])
                    ->latest();
    }

    /**
     * Penggunaan aktif saat ini (peralatan masih di line ini)
     */
    public function penggunaanAktif()
    {
        return $this->hasOne(RiwayatPenggunaan::class, 'peralatan_id')
                    ->whereColumn('line_tujuan', 'peralatan.status_line')
                    ->latest('tanggal_pemakaian');
    }

    public function kalibrasi()
    {
        return $this->hasMany(Kalibrasi::class, 'peralatan_id');
    }

    public function kalibrasiTerakhir()
    {
        return $this->hasOne(Kalibrasi::class, 'peralatan_id')
                    ->latest('tanggal_pelaksanaan');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByKodeAsset($query, $kodeAsset)
    {
        return $query->where('kode_asset', $kodeAsset);
    }

    public function scopeKategori($query, $kategoriAlatId)
    {
        return $query->where('kategori_alat_id', $kategoriAlatId);
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

    public function getNamaKategoriAttribute(): string
    {
        return $this->kategoriAlat ? $this->kategoriAlat->nama_kategori : '-';
    }

    /**
     * Gabungan Merk, Type, dan No. Seri untuk tampilan ringkas
     * (dipakai di tabel list, riwayat, dsb — bukan di form input).
     */
    public function getMerkTipeLengkapAttribute(): string
    {
        $bagian = trim(implode(' ', array_filter([$this->merk, $this->type])));

        if (!empty($this->serial_number)) {
            $bagian .= ($bagian ? ' ' : '') . 'No. ' . $this->serial_number;
        }

        return $bagian !== '' ? $bagian : '-';
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

    /**
     * Ringkasan spesifikasi (JSON key-value) jadi satu baris teks,
     * misal "Kapasitas: 3000gr/0.2gr" atau "Range: -20°C – 100°C".
     * Generik — mengikuti apa pun field yang diisi per kategori alat,
     * bukan hardcode nama field seperti 'kapasitas' saja.
     * Dipakai di modul Kalibrasi (create/bulk modal, template Excel)
     * supaya tidak bergantung pada satu jenis alat.
     */
    public function getSpesifikasiRingkasAttribute(): string
    {
        if (empty($this->spesifikasi) || !is_array($this->spesifikasi)) {
            return '-';
        }

        $bagian = [];
        foreach ($this->spesifikasi as $label => $nilai) {
            $nilai = trim((string) $nilai);
            if ($nilai === '') {
                continue;
            }
            $bagian[] = ucfirst($label) . ': ' . $nilai;
        }

        return $bagian ? implode(', ', $bagian) : '-';
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
     * Apakah peralatan ini bisa dilaporkan rusak
     * (harus Baik, sedang di Line, dan belum ada laporan aktif)
     */
    public function bisaDilaporkanRusak(): bool
    {
        return $this->kondisi_saat_ini === 'Baik'
            && $this->status_line !== null;
    }
}