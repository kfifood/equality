<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\Peralatan;
use App\Models\RiwayatPenggunaan;
use App\Models\RiwayatPerbaikan;
use App\Models\Kalibrasi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    use ExcelHelpers;

    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $baseQuery = fn() => Peralatan::query()
            ->when($this->line !== '', fn($x) => $x->where('status_line', $this->line));

        $total     = $baseQuery()->count();
        $baik      = $baseQuery()->where('kondisi_saat_ini', 'Baik')->count();
        $rusak     = $baseQuery()->where('kondisi_saat_ini', 'Rusak')->count();
        $perbaikan = $baseQuery()->where('kondisi_saat_ini', 'Dalam Perbaikan')->count();
        $persen    = $total > 0 ? round(($baik / $total) * 100, 1) : 0;

        $penggunaanCount = RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($this->line !== '', fn($x) => $x->where('line_tujuan', $this->line))
            ->count();

        $perbaikanCount = RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->when($this->line !== '', fn($x) => $x->where('line_sebelumnya', $this->line))
            ->count();

        // Filter kalibrasi pakai dept_bagian (snapshot lokasi saat kalibrasi
        // dilaksanakan), bukan status_line saat ini pada peralatan — konsisten
        // dengan pola filter historis di PenggunaanSheet/PerbaikanSheet.
        $kalibrasiBase = fn() => Kalibrasi::whereBetween('tanggal_pelaksanaan', [$startDate, $endDate])
            ->when($this->line !== '', fn($x) => $x->where('dept_bagian', $this->line));

        $kalibrasiCount    = $kalibrasiBase()->count();
        $kalibrasiLulus    = $kalibrasiBase()->where('hasil', 'Lulus')->count();
        $kalibrasiTakLulus = $kalibrasiBase()->where('hasil', 'Tidak Lulus')->count();

        return collect([
            ['Metrik' => 'Periode Laporan',      'Nilai' => Carbon::create($this->year, $this->month, 1)->isoFormat('MMMM YYYY')],
            ['Metrik' => 'Filter Line',           'Nilai' => $this->line ?: 'Semua Line'],
            ['Metrik' => 'Total Peralatan',       'Nilai' => $total],
            ['Metrik' => 'Peralatan Baik',        'Nilai' => $baik . ' (' . $persen . '%)'],
            ['Metrik' => 'Peralatan Rusak',       'Nilai' => $rusak],
            ['Metrik' => 'Dalam Perbaikan',       'Nilai' => $perbaikan],
            ['Metrik' => 'Penggunaan Bln Ini',    'Nilai' => $penggunaanCount],
            ['Metrik' => 'Perbaikan Bln Ini',     'Nilai' => $perbaikanCount],
            ['Metrik' => 'Kalibrasi Bln Ini',     'Nilai' => $kalibrasiCount],
            ['Metrik' => 'Kalibrasi Lulus',       'Nilai' => $kalibrasiLulus],
            ['Metrik' => 'Kalibrasi Tidak Lulus', 'Nilai' => $kalibrasiTakLulus],
            ['Metrik' => 'Di Lab',                'Nilai' => $this->line === '' ? Peralatan::whereNull('status_line')->count() : '-'],
            ['Metrik' => 'Di Line',               'Nilai' => $this->line === '' ? Peralatan::whereNotNull('status_line')->count() : $total],
            ['Metrik' => 'Tanggal Export',        'Nilai' => Carbon::now()->format('d/m/Y H:i')],
        ]);
    }

    public function headings(): array
    {
        return ['METRIK', 'NILAI'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 28,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Hanya return style untuk row 1 (header) — integer key, bukan range string
        return [
            1 => $this->headerStyle(),
        ];
    }
}