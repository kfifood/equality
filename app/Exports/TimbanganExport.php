<?php

namespace App\Exports;

use App\Models\Timbangan;
use App\Models\RiwayatPenggunaan;
use App\Models\RiwayatPerbaikan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

// ─── Warna header abu-abu gelap — dipakai di semua sheet ──────────────────────
define('EXCEL_HEADER_COLOR', '4A4A4A');   // abu-abu gelap
define('EXCEL_HEADER_ALT',   'F2F2F2');  // abu-abu muda untuk baris genap (jika diperlukan)

class TimbanganExport implements WithMultipleSheets
{
    protected $year;
    protected $month;
    protected $format;
    protected $line;   // '' = semua line

    public function __construct($year, $month, $format = 'summary', $line = '')
    {
        $this->year   = $year;
        $this->month  = $month;
        $this->format = $format;
        $this->line   = $line;
    }

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

// ─── Helper: resolve keluhan & tindakan dari relasi baru / field lama ─────────
function resolveKeluhan(RiwayatPerbaikan $perbaikan): string
{
    if ($perbaikan->laporanKerusakan && $perbaikan->laporanKerusakan->keluhanList->count()) {
        return $perbaikan->laporanKerusakan->keluhanList
            ->map(fn($k) => $k->nama_keluhan ?? $k->keluhan ?? '-')
            ->join(', ');
    }
    return $perbaikan->deskripsi_keluhan ?? '-';
}

function resolveTindakan(RiwayatPerbaikan $perbaikan): string
{
    if ($perbaikan->detailTindakan->count()) {
        return $perbaikan->detailTindakan
            ->map(fn($d) => $d->masterTindakan->nama_tindakan ?? '-')
            ->unique()->join(', ');
    }
    return $perbaikan->tindakan_perbaikan ?? '-';
}

// ─── Style header abu-abu gelap (reusable) ───────────────────────────────────
function headerStyle(): array
{
    return [
        'font' => [
            'bold'  => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size'  => 11,
        ],
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['rgb' => EXCEL_HEADER_COLOR],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER,
        ],
    ];
}

function dataRowStyle(string $lastCol, int $maxRow = 1000): array
{
    return [
        "A2:{$lastCol}{$maxRow}" => [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'DDDDDD'],
                ],
            ],
        ],
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET SUMMARY
// ─────────────────────────────────────────────────────────────────────────────
class SummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $q = Timbangan::query()->when($this->line !== '', fn($x) => $x->where('status_line', $this->line));

        $total    = (clone $q)->count();
        $baik     = (clone $q)->where('kondisi_saat_ini', 'Baik')->count();
        $persen   = $total > 0 ? round(($baik / $total) * 100, 1) : 0;

        $penggunaanCount = RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($this->line !== '', fn($x) => $x->where('line_tujuan', $this->line))
            ->count();

        $perbaikanCount = RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->when($this->line !== '', fn($x) => $x->where('line_sebelumnya', $this->line))
            ->count();

        return collect([
            ['Metrik' => 'Periode Laporan',    'Nilai' => Carbon::create($this->year, $this->month, 1)->isoFormat('MMMM YYYY')],
            ['Metrik' => 'Filter Line',         'Nilai' => $this->line ?: 'Semua Line'],
            ['Metrik' => 'Total Timbangan',     'Nilai' => $total],
            ['Metrik' => 'Timbangan Baik',      'Nilai' => $baik . ' (' . $persen . '%)'],
            ['Metrik' => 'Timbangan Rusak',     'Nilai' => (clone $q)->where('kondisi_saat_ini', 'Rusak')->count()],
            ['Metrik' => 'Dalam Perbaikan',     'Nilai' => (clone $q)->where('kondisi_saat_ini', 'Dalam Perbaikan')->count()],
            ['Metrik' => 'Penggunaan Bln Ini',  'Nilai' => $penggunaanCount],
            ['Metrik' => 'Perbaikan Bln Ini',   'Nilai' => $perbaikanCount],
            ['Metrik' => 'Di Lab',              'Nilai' => $this->line === '' ? Timbangan::whereNull('status_line')->count() : '-'],
            ['Metrik' => 'Di Line',             'Nilai' => $this->line === '' ? Timbangan::whereNotNull('status_line')->count() : $total],
            ['Metrik' => 'Tanggal Export',      'Nilai' => Carbon::now()->format('d/m/Y H:i')],
        ]);
    }

    public function headings(): array
    {
        return ['METRIK', 'NILAI'];
    }

    public function title(): string { return 'Summary'; }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(28);

        return array_merge(
            [1 => headerStyle()],
            dataRowStyle('B', 12)
        );
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET RIWAYAT PERGERAKAN
// ─────────────────────────────────────────────────────────────────────────────
class RiwayatPergerakanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $timbanganList = Timbangan::with([
            'riwayatPenggunaan' => fn($q) => $q
                ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
                ->when($this->line !== '', fn($x) => $x->where('line_tujuan', $this->line))
                ->orderBy('tanggal_pemakaian'),
            'riwayatPerbaikan' => fn($q) => $q
                ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
                ->when($this->line !== '', fn($x) => $x->where('line_sebelumnya', $this->line))
                ->with(['laporanKerusakan.keluhanList', 'detailTindakan.masterTindakan'])
                ->orderBy('tanggal_masuk_lab'),
        ])
        ->when($this->line !== '', fn($q) => $q->where('status_line', $this->line))
        ->orderBy('kode_asset')
        ->get();

        $data = collect();

        foreach ($timbanganList as $timbangan) {
            $baseData = [
                'kode_asset' => $timbangan->kode_asset,
                'merk_tipe'  => $timbangan->merk_tipe_no_seri,
            ];

            $allHistory = collect();

            foreach ($timbangan->riwayatPenggunaan as $p) {
                $allHistory->push([
                    'type'        => 'PENGGUNAAN',
                    'date'        => $p->tanggal_pemakaian,
                    'line_tujuan' => $p->line_tujuan,
                    'pic'         => $p->pic ?? '-',
                    'keterangan'  => $p->keterangan ?? 'Penggunaan di line',
                    'keluhan'     => '-',
                    'tindakan'    => '-',
                    'status'      => $p->getStatusPenggunaanAttribute(),
                ]);
            }

            foreach ($timbangan->riwayatPerbaikan as $p) {
                $allHistory->push([
                    'type'        => 'PERBAIKAN',
                    'date'        => $p->tanggal_masuk_lab,
                    'line_tujuan' => $p->line_tujuan ?? 'Lab',
                    'pic'         => $p->pic_teknik ?? '-',
                    'keterangan'  => '-',
                    'keluhan'     => resolveKeluhan($p),
                    'tindakan'    => resolveTindakan($p),
                    'status'      => $p->status_perbaikan,
                ]);

                if ($p->tanggal_selesai_perbaikan) {
                    $allHistory->push([
                        'type'        => 'SELESAI PERBAIKAN',
                        'date'        => $p->tanggal_selesai_perbaikan,
                        'line_tujuan' => $p->line_tujuan ?? 'Lab',
                        'pic'         => $p->pic_teknik ?? '-',
                        'keterangan'  => '-',
                        'keluhan'     => resolveKeluhan($p),
                        'tindakan'    => resolveTindakan($p),
                        'status'      => 'SELESAI',
                    ]);
                }
            }

            $sortedHistory = $allHistory->sortBy('date');

            if ($sortedHistory->count() > 0) {
                $data->push(array_merge($baseData, [
                    'jenis_aktivitas' => 'STATUS AWAL',
                    'tanggal'         => $startDate->format('d/m/Y'),
                    'line_tujuan'     => $timbangan->lokasi_asli ?? 'Lab',
                    'pic'             => '-',
                    'keterangan'      => 'Status awal bulan',
                    'keluhan'         => '-',
                    'tindakan'        => '-',
                    'status'          => $timbangan->kondisi_saat_ini,
                ]));
            }

            foreach ($sortedHistory as $h) {
                $data->push(array_merge($baseData, [
                    'jenis_aktivitas' => $h['type'],
                    'tanggal'         => $h['date']->format('d/m/Y'),
                    'line_tujuan'     => $h['line_tujuan'],
                    'pic'             => $h['pic'],
                    'keterangan'      => $h['keterangan'],
                    'keluhan'         => $h['keluhan'],
                    'tindakan'        => $h['tindakan'],
                    'status'          => $h['status'],
                ]));
            }

            if ($sortedHistory->count() > 0) {
                $data->push(array_merge($baseData, [
                    'jenis_aktivitas' => 'STATUS AKHIR',
                    'tanggal'         => $endDate->format('d/m/Y'),
                    'line_tujuan'     => $timbangan->status_line ?? 'Lab',
                    'pic'             => '-',
                    'keterangan'      => 'Status akhir bulan',
                    'keluhan'         => '-',
                    'tindakan'        => '-',
                    'status'          => $timbangan->kondisi_saat_ini,
                ]));
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'MERK TIPE', 'JENIS AKTIVITAS',
            'TANGGAL', 'LINE TUJUAN', 'PIC',
            'KETERANGAN', 'KELUHAN', 'TINDAKAN PERBAIKAN', 'STATUS',
        ];
    }

    public function title(): string { return 'Riwayat Pergerakan'; }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return array_merge([1 => headerStyle()], dataRowStyle('J'));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET LAPORAN LENGKAP
// ─────────────────────────────────────────────────────────────────────────────
class LaporanLengkapSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $penggunaanData = RiwayatPenggunaan::with('timbangan')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($this->line !== '', fn($q) => $q->where('line_tujuan', $this->line))
            ->orderBy('tanggal_pemakaian')
            ->get();

        $perbaikanData = RiwayatPerbaikan::with([
                'timbangan',
                'laporanKerusakan.keluhanList',
                'detailTindakan.masterTindakan',
            ])
            ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->when($this->line !== '', fn($q) => $q->where('line_sebelumnya', $this->line))
            ->orderBy('tanggal_masuk_lab')
            ->get();

        $data = collect();

        foreach ($penggunaanData as $p) {
            $data->push([
                'kode_asset'       => $p->timbangan->kode_asset ?? '-',
                'merk_tipe'        => $p->timbangan->merk_tipe_no_seri ?? '-',
                'tanggal'          => $p->tanggal_pemakaian->format('d/m/Y'),
                'jenis'            => 'PENGGUNAAN',
                'line'             => $p->line_tujuan,
                'pic'              => $p->pic ?? '-',
                'keterangan'       => $p->keterangan ?? '-',
                'keluhan'          => '-',
                'tindakan'         => '-',
                'perbaikan_ekst'   => '-',
                'tanggal_selesai'  => '-',
                'durasi_hari'      => '-',
                'status'           => $p->getStatusPenggunaanAttribute(),
            ]);
        }

        foreach ($perbaikanData as $p) {
            $data->push([
                'kode_asset'       => $p->timbangan->kode_asset ?? '-',
                'merk_tipe'        => $p->timbangan->merk_tipe_no_seri ?? '-',
                'tanggal'          => $p->tanggal_masuk_lab->format('d/m/Y'),
                'jenis'            => 'PERBAIKAN',
                'line'             => $p->line_sebelumnya,
                'pic'              => $p->pic_teknik ?? '-',
                'keterangan'       => '-',
                'keluhan'          => resolveKeluhan($p),
                'tindakan'         => resolveTindakan($p),
                'perbaikan_ekst'   => $p->perbaikan_eksternal ?? '-',
                'tanggal_selesai'  => $p->tanggal_selesai_perbaikan
                    ? $p->tanggal_selesai_perbaikan->format('d/m/Y') : '-',
                'durasi_hari'      => $p->getDurasiPerbaikanAttribute() ?? '-',
                'status'           => $p->status_perbaikan,
            ]);
        }

        return $data->sortBy(fn($item) => Carbon::createFromFormat('d/m/Y', $item['tanggal']))->values();
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'MERK TIPE', 'TANGGAL', 'JENIS', 'LINE', 'PIC',
            'KETERANGAN', 'KELUHAN', 'TINDAKAN PERBAIKAN',
            'PERBAIKAN EKSTERNAL', 'TANGGAL SELESAI', 'DURASI (HARI)', 'STATUS',
        ];
    }

    public function title(): string { return 'Laporan Lengkap'; }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return array_merge([1 => headerStyle()], dataRowStyle('M'));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET DATA TIMBANGAN
// ─────────────────────────────────────────────────────────────────────────────
class TimbanganSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        return Timbangan::orderBy('kode_asset')
            ->when($this->line !== '', fn($q) => $q->where('status_line', $this->line))
            ->get()
            ->map(fn($t) => [
                'Kode Asset'     => $t->kode_asset,
                'Merk Tipe Seri' => $t->merk_tipe_no_seri,
                'Tanggal Datang' => $t->tanggal_datang ? $t->tanggal_datang->format('d/m/Y') : '-',
                'Lokasi Asli'    => $t->lokasi_asli ?? '-',
                'Lokasi Sekarang'=> $t->status_line ?: 'Lab',
                'Kondisi'        => $t->kondisi_saat_ini,
                'Status Lengkap' => $t->getStatusLengkapAttribute(),
                'Terakhir Update'=> $t->updated_at ? $t->updated_at->format('d/m/Y H:i') : '-',
            ]);
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'MERK TIPE SERI', 'TANGGAL DATANG',
            'LOKASI ASLI', 'LOKASI SEKARANG', 'KONDISI',
            'STATUS LENGKAP', 'TERAKHIR UPDATE',
        ];
    }

    public function title(): string { return 'Data Timbangan'; }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return array_merge([1 => headerStyle()], dataRowStyle('H'));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET RIWAYAT PENGGUNAAN
// ─────────────────────────────────────────────────────────────────────────────
class PenggunaanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return RiwayatPenggunaan::with('timbangan')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($this->line !== '', fn($q) => $q->where('line_tujuan', $this->line))
            ->orderBy('tanggal_pemakaian', 'desc')
            ->get()
            ->map(fn($p) => [
                'Kode Asset'       => $p->timbangan->kode_asset ?? '-',
                'Merk Tipe Seri'   => $p->timbangan->merk_tipe_no_seri ?? '-',
                'Line Tujuan'      => $p->line_tujuan,
                'Tanggal Pemakaian'=> $p->tanggal_pemakaian ? $p->tanggal_pemakaian->format('d/m/Y') : '-',
                'PIC'              => $p->pic ?? '-',
                'Keterangan'       => $p->keterangan ?? '-',
                'Status'           => $p->getStatusPenggunaanAttribute(),
            ]);
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'MERK TIPE SERI', 'LINE TUJUAN',
            'TANGGAL PEMAKAIAN', 'PIC', 'KETERANGAN', 'STATUS',
        ];
    }

    public function title(): string { return 'Riwayat Penggunaan'; }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return array_merge([1 => headerStyle()], dataRowStyle('G'));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SHEET RIWAYAT PERBAIKAN
// ─────────────────────────────────────────────────────────────────────────────
class PerbaikanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return RiwayatPerbaikan::with([
                'timbangan',
                'laporanKerusakan.keluhanList',
                'detailTindakan.masterTindakan',
            ])
            ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->when($this->line !== '', fn($q) => $q->where('line_sebelumnya', $this->line))
            ->orderBy('tanggal_masuk_lab', 'desc')
            ->get()
            ->map(fn($p) => [
                'Kode Asset'          => $p->timbangan->kode_asset ?? '-',
                'Merk Tipe Seri'      => $p->timbangan->merk_tipe_no_seri ?? '-',
                'Line Sebelumnya'     => $p->line_sebelumnya ?? '-',
                'Tanggal Masuk'       => $p->tanggal_masuk_lab ? $p->tanggal_masuk_lab->format('d/m/Y') : '-',
                'Keluhan'             => resolveKeluhan($p),
                'Tindakan Perbaikan'  => resolveTindakan($p),
                'Perbaikan Eksternal' => $p->perbaikan_eksternal ?? '-',
                'Status'              => $p->status_perbaikan,
                'Tanggal Selesai'     => $p->tanggal_selesai_perbaikan
                    ? $p->tanggal_selesai_perbaikan->format('d/m/Y') : '-',
                'Line Tujuan'         => $p->line_tujuan ?? '-',
                'Durasi (Hari)'       => $p->getDurasiPerbaikanAttribute() ?? '-',
                'PIC Teknik'          => $p->pic_teknik ?? '-',
            ]);
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'MERK TIPE SERI', 'LINE SEBELUMNYA',
            'TANGGAL MASUK', 'KELUHAN', 'TINDAKAN PERBAIKAN',
            'PERBAIKAN EKSTERNAL', 'STATUS', 'TANGGAL SELESAI',
            'LINE TUJUAN', 'DURASI (HARI)', 'PIC TEKNIK',
        ];
    }

    public function title(): string { return 'Riwayat Perbaikan'; }

    public function styles(Worksheet $sheet)
    {
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return array_merge([1 => headerStyle()], dataRowStyle('L'));
    }
}