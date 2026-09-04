<?php

namespace App\Exports;

use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\RiwayatPergerakanSheet;
use App\Exports\Sheets\LaporanLengkapSheet;
use App\Exports\Sheets\PeralatanSheet;
use App\Exports\Sheets\PenggunaanSheet;
use App\Exports\Sheets\PerbaikanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * NOTE: Class-class di App\Exports\Sheets\* (SummarySheet, RiwayatPergerakanSheet,
 * LaporanLengkapSheet, PeralatanSheet, PenggunaanSheet, PerbaikanSheet) belum
 * ikut diupload, jadi belum bisa disesuaikan di sini. File-file itu kemungkinan
 * masih mereferensikan model/kolom Timbangan lama (jenis_alat_ukur, kapasitas, dst)
 * dan perlu ditinjau ulang secara terpisah supaya konsisten dengan skema Peralatan
 * yang baru (kategori_alat_id, spesifikasi JSON).
 *
 * PeralatanSheet di bawah ini adalah rename dari TimbanganSheet — pastikan file
 * fisiknya juga di-rename/disesuaikan.
 */
class PeralatanExport implements WithMultipleSheets
{
    public function __construct(
        protected $year,
        protected $month,
        protected $format = 'summary',
        protected $line = ''
    ) {}

    public function sheets(): array
    {
        $args = [$this->year, $this->month, $this->line];

        return match ($this->format) {
            'summary'    => [
                new SummarySheet(...$args),
                new RiwayatPergerakanSheet(...$args),
                new LaporanLengkapSheet(...$args),
                new PeralatanSheet(...$args),
                new PenggunaanSheet(...$args),
                new PerbaikanSheet(...$args),
            ],
            'peralatan'  => [new PeralatanSheet(...$args)],
            'penggunaan' => [new PenggunaanSheet(...$args)],
            'perbaikan'  => [new PerbaikanSheet(...$args)],
            'lengkap'    => [new LaporanLengkapSheet(...$args)],
            'riwayat'    => [new RiwayatPergerakanSheet(...$args)],
            default      => [new SummarySheet(...$args)],
        };
    }
}