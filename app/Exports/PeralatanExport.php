<?php

namespace App\Exports;

use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\RiwayatPergerakanSheet;
use App\Exports\Sheets\LaporanLengkapSheet;
use App\Exports\Sheets\PeralatanSheet;
use App\Exports\Sheets\PenggunaanSheet;
use App\Exports\Sheets\PerbaikanSheet;
use App\Exports\Sheets\KalibrasiSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
                new KalibrasiSheet(...$args),
            ],
            'peralatan'  => [new PeralatanSheet(...$args)],
            'penggunaan' => [new PenggunaanSheet(...$args)],
            'perbaikan'  => [new PerbaikanSheet(...$args)],
            'kalibrasi'  => [new KalibrasiSheet(...$args)],
            'lengkap'    => [new LaporanLengkapSheet(...$args)],
            'riwayat'    => [new RiwayatPergerakanSheet(...$args)],
            default      => [new SummarySheet(...$args)],
        };
    }
}