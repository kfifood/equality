<?php

namespace App\Exports;

use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\RiwayatPergerakanSheet;
use App\Exports\Sheets\LaporanLengkapSheet;
use App\Exports\Sheets\TimbanganSheet;
use App\Exports\Sheets\PenggunaanSheet;
use App\Exports\Sheets\PerbaikanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TimbanganExport implements WithMultipleSheets
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
                new TimbanganSheet(...$args),
                new PenggunaanSheet(...$args),
                new PerbaikanSheet(...$args),
            ],
            'timbangan'  => [new TimbanganSheet(...$args)],
            'penggunaan' => [new PenggunaanSheet(...$args)],
            'perbaikan'  => [new PerbaikanSheet(...$args)],
            'lengkap'    => [new LaporanLengkapSheet(...$args)],
            'riwayat'    => [new RiwayatPergerakanSheet(...$args)],
            default      => [new SummarySheet(...$args)],
        };
    }
}