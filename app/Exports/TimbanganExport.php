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

class TimbanganExport implements WithMultipleSheets
{
    protected $year;
    protected $month;
    protected $format;

    public function __construct($year, $month, $format = 'summary')
    {
        $this->year = $year;
        $this->month = $month;
        $this->format = $format;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->format === 'summary') {
            $sheets[] = new SummarySheet($this->year, $this->month);
            $sheets[] = new RiwayatPergerakanSheet($this->year, $this->month);
            $sheets[] = new LaporanLengkapSheet($this->year, $this->month);
            $sheets[] = new TimbanganSheet($this->year, $this->month);
            $sheets[] = new PenggunaanSheet($this->year, $this->month);
            $sheets[] = new PerbaikanSheet($this->year, $this->month);
        } elseif ($this->format === 'timbangan') {
            $sheets[] = new TimbanganSheet($this->year, $this->month);
        } elseif ($this->format === 'penggunaan') {
            $sheets[] = new PenggunaanSheet($this->year, $this->month);
        } elseif ($this->format === 'perbaikan') {
            $sheets[] = new PerbaikanSheet($this->year, $this->month);
        } elseif ($this->format === 'lengkap') {
            $sheets[] = new LaporanLengkapSheet($this->year, $this->month);
        } elseif ($this->format === 'riwayat') {
            $sheets[] = new RiwayatPergerakanSheet($this->year, $this->month);
        }

        return $sheets;
    }
}

// SHEET SUMMARY
class SummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $totalTimbangan = Timbangan::count();
        $timbanganBaik = Timbangan::where('kondisi_saat_ini', 'Baik')->count();
        $persentaseBaik = $totalTimbangan > 0 ? round(($timbanganBaik / $totalTimbangan) * 100, 1) : 0;

        $data = collect([
            ['METRIC' => 'Periode Laporan', 'NILAI' => Carbon::create($this->year, $this->month, 1)->format('F Y')],
            ['METRIC' => 'Total Timbangan', 'NILAI' => $totalTimbangan],
            ['METRIC' => 'Timbangan Baik', 'NILAI' => $timbanganBaik . ' (' . $persentaseBaik . '%)'],
            ['METRIC' => 'Timbangan Rusak', 'NILAI' => Timbangan::where('kondisi_saat_ini', 'Rusak')->count()],
            ['METRIC' => 'Dalam Perbaikan', 'NILAI' => Timbangan::where('kondisi_saat_ini', 'Dalam Perbaikan')->count()],
            ['METRIC' => 'Penggunaan Bulan Ini', 'NILAI' => RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])->count()],
            ['METRIC' => 'Perbaikan Bulan Ini', 'NILAI' => RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])->count()],
            ['METRIC' => 'Timbangan di Lab', 'NILAI' => Timbangan::whereNull('status_line')->count()],
            ['METRIC' => 'Timbangan di Line', 'NILAI' => Timbangan::whereNotNull('status_line')->count()],
            ['METRIC' => 'Tanggal Export', 'NILAI' => Carbon::now()->format('d/m/Y H:i')],
        ]);

        return $data;
    }

    public function headings(): array
    {
        return ['METRIC', 'NILAI'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(25);

        return [
            // Header style
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4361EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Data rows
            'A2:B11' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            'B2:B11' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];
    }
}

// SHEET RIWAYAT PERGERAKAN
class RiwayatPergerakanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $timbanganList = Timbangan::with([
            'riwayatPenggunaan' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
                      ->orderBy('tanggal_pemakaian', 'asc');
            },
            'riwayatPerbaikan' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
                      ->orderBy('tanggal_masuk_lab', 'asc');
            }
        ])->orderBy('kode_asset')->get();

        $data = collect();

        foreach ($timbanganList as $timbangan) {
            $baseData = [
                'kode_asset' => $timbangan->kode_asset,
                'nomor_seri' => $timbangan->nomor_seri_unik ?? '-',
                'merk_tipe' => $timbangan->merk_tipe_no_seri,
            ];

            // Gabungkan semua riwayat
            $allHistory = collect();
            
            // Riwayat penggunaan dalam periode
            foreach ($timbangan->riwayatPenggunaan as $penggunaan) {
                $allHistory->push([
                    'type' => 'PENGGUNAAN',
                    'date' => $penggunaan->tanggal_pemakaian,
                    'line_tujuan' => $penggunaan->line_tujuan,
                    'pic' => $penggunaan->pic ?? '-',
                    'keterangan' => $penggunaan->keterangan ?? 'Penggunaan di line',
                    'status' => $penggunaan->getStatusPenggunaanAttribute(),
                ]);
            }

            // Riwayat perbaikan dalam periode
            foreach ($timbangan->riwayatPerbaikan as $perbaikan) {
                $allHistory->push([
                    'type' => 'PERBAIKAN',
                    'date' => $perbaikan->tanggal_masuk_lab,
                    'line_tujuan' => $perbaikan->line_tujuan ?? 'Lab',
                    'pic' => '-',
                    'keterangan' => 'Perbaikan: ' . $perbaikan->deskripsi_keluhan,
                    'status' => $perbaikan->status_perbaikan,
                ]);

                // Jika perbaikan selesai, tambahkan event selesai
                if ($perbaikan->tanggal_selesai_perbaikan) {
                    $allHistory->push([
                        'type' => 'SELESAI PERBAIKAN',
                        'date' => $perbaikan->tanggal_selesai_perbaikan,
                        'line_tujuan' => $perbaikan->line_tujuan ?? 'Lab',
                        'pic' => '-',
                        'keterangan' => 'Perbaikan selesai: ' . ($perbaikan->tindakan_perbaikan ?? 'Tindakan perbaikan'),
                        'status' => 'SELESAI',
                    ]);
                }
            }

            // Urutkan berdasarkan tanggal
            $sortedHistory = $allHistory->sortBy('date');

            // Tambahkan status awal jika ada riwayat
            if ($sortedHistory->count() > 0) {
                $data->push(array_merge($baseData, [
                    'jenis_aktivitas' => 'STATUS AWAL',
                    'tanggal' => $startDate->format('d/m/Y'),
                    'line_tujuan' => $timbangan->lokasi_asli ?? 'Lab',
                    'pic' => '-',
                    'keterangan' => 'Status awal bulan',
                    'status' => $timbangan->kondisi_saat_ini,
                ]));
            }

            // Tambahkan semua riwayat yang sudah diurutkan
            foreach ($sortedHistory as $history) {
                $data->push(array_merge($baseData, [
                    'jenis_aktivitas' => $history['type'],
                    'tanggal' => $history['date']->format('d/m/Y'),
                    'line_tujuan' => $history['line_tujuan'],
                    'pic' => $history['pic'],
                    'keterangan' => $history['keterangan'],
                    'status' => $history['status'],
                ]));
            }

            // Tambahkan status akhir
            if ($sortedHistory->count() > 0) {
                $data->push(array_merge($baseData, [
                    'jenis_aktivitas' => 'STATUS AKHIR',
                    'tanggal' => $endDate->format('d/m/Y'),
                    'line_tujuan' => $timbangan->status_line ?? 'Lab',
                    'pic' => '-',
                    'keterangan' => 'Status akhir bulan',
                    'status' => $timbangan->kondisi_saat_ini,
                ]));
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI',
            'MERK TIPE',
            'JENIS AKTIVITAS',
            'TANGGAL',
            'LINE TUJUAN',
            'PIC',
            'KETERANGAN',
            'STATUS'
        ];
    }

    public function title(): string
    {
        return 'Riwayat Pergerakan';
    }

    public function styles(Worksheet $sheet)
    {
        // Auto size columns
        foreach(range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            // Header style
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4361EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Data rows
            'A2:I1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],
        ];
    }
}

// SHEET LAPORAN LENGKAP
class LaporanLengkapSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $penggunaanData = RiwayatPenggunaan::with('timbangan')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->orderBy('tanggal_pemakaian', 'asc')
            ->get();

        $perbaikanData = RiwayatPerbaikan::with('timbangan')
            ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->orderBy('tanggal_masuk_lab', 'asc')
            ->get();

        $data = collect();

        foreach ($penggunaanData as $penggunaan) {
            $data->push([
                'kode_asset' => $penggunaan->timbangan->kode_asset ?? '-',
                'nomor_seri' => $penggunaan->timbangan->nomor_seri_unik ?? '-',
                'merk_tipe' => $penggunaan->timbangan->merk_tipe_no_seri ?? '-',
                'tanggal' => $penggunaan->tanggal_pemakaian->format('d/m/Y'),
                'jenis' => 'PENGGUNAAN',
                'line' => $penggunaan->line_tujuan,
                'pic' => $penggunaan->pic ?? '-',
                'keterangan' => $penggunaan->keterangan ?? '-',
                'status' => $penggunaan->getStatusPenggunaanAttribute()
            ]);
        }

        foreach ($perbaikanData as $perbaikan) {
            $data->push([
                'kode_asset' => $perbaikan->timbangan->kode_asset ?? '-',
                'nomor_seri' => $perbaikan->timbangan->nomor_seri_unik ?? '-',
                'merk_tipe' => $perbaikan->timbangan->merk_tipe_no_seri ?? '-',
                'tanggal' => $perbaikan->tanggal_masuk_lab->format('d/m/Y'),
                'jenis' => 'PERBAIKAN',
                'line' => $perbaikan->line_sebelumnya,
                'pic' => '-',
                'keterangan' => $perbaikan->deskripsi_keluhan,
                'status' => $perbaikan->status_perbaikan,
                'tindakan' => $perbaikan->tindakan_perbaikan ?? '-',
                'perbaikan_eksternal' => $perbaikan->perbaikan_eksternal ?? '-',
                'tanggal_selesai' => $perbaikan->tanggal_selesai_perbaikan ? 
                    $perbaikan->tanggal_selesai_perbaikan->format('d/m/Y') : '-'
            ]);
        }

        return $data->sortBy(function($item) {
            return Carbon::createFromFormat('d/m/Y', $item['tanggal']);
        })->values();
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI',
            'MERK TIPE',
            'TANGGAL',
            'JENIS',
            'LINE',
            'PIC',
            'KETERANGAN',
            'STATUS',
            'TINDAKAN PERBAIKAN',
            'PERBAIKAN EKSTERNAL',
            'TANGGAL SELESAI'
        ];
    }

    public function title(): string
    {
        return 'Laporan Lengkap';
    }

    public function styles(Worksheet $sheet)
    {
        foreach(range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4361EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            'A2:L1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],
        ];
    }
}

// SHEET DATA TIMBANGAN
class TimbanganSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return Timbangan::orderBy('kode_asset')->get()->map(function ($timbangan) {
            return [
                'Kode Asset' => $timbangan->kode_asset,
                'Nomor Seri Unik' => $timbangan->nomor_seri_unik ?? '-',
                'Merk Tipe Seri' => $timbangan->merk_tipe_no_seri,
                'Tanggal Datang' => $timbangan->tanggal_datang ? $timbangan->tanggal_datang->format('d/m/Y') : '-',
                'Lokasi Asli' => $timbangan->lokasi_asli ?? '-',
                'Lokasi Sekarang' => $timbangan->status_line ?: 'Lab',
                'Kondisi' => $timbangan->kondisi_saat_ini,
                'Status Lengkap' => $timbangan->getStatusLengkapAttribute(),
                'Terakhir Update' => $timbangan->updated_at ? $timbangan->updated_at->format('d/m/Y H:i') : '-'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI UNIK',
            'MERK TIPE SERI',
            'TANGGAL DATANG',
            'LOKASI ASLI',
            'LOKASI SEKARANG',
            'KONDISI',
            'STATUS LENGKAP',
            'TERAKHIR UPDATE'
        ];
    }

    public function title(): string
    {
        return 'Data Timbangan';
    }

    public function styles(Worksheet $sheet)
    {
        foreach(range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4361EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            'A2:I1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],
        ];
    }
}

// SHEET RIWAYAT PENGGUNAAN
class PenggunaanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return RiwayatPenggunaan::with('timbangan')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->orderBy('tanggal_pemakaian', 'desc')
            ->get()
            ->map(function ($penggunaan) {
                return [
                    'Kode Asset' => $penggunaan->timbangan->kode_asset ?? '-',
                    'Nomor Seri' => $penggunaan->timbangan->nomor_seri_unik ?? '-',
                    'Line Tujuan' => $penggunaan->line_tujuan,
                    'Tanggal Pemakaian' => $penggunaan->tanggal_pemakaian ? $penggunaan->tanggal_pemakaian->format('d/m/Y') : '-',
                    'PIC' => $penggunaan->pic ?? '-',
                    'Keterangan' => $penggunaan->keterangan ?? '-',
                    'Status' => $penggunaan->getStatusPenggunaanAttribute(),
                    'Status Aktif' => $penggunaan->isAktif() ? 'Ya' : 'Tidak'
                ];
            });
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI',
            'LINE TUJUAN',
            'TANGGAL PEMAKAIAN',
            'PIC',
            'KETERANGAN',
            'STATUS',
            'STATUS AKTIF'
        ];
    }

    public function title(): string
    {
        return 'Riwayat Penggunaan';
    }

    public function styles(Worksheet $sheet)
    {
        foreach(range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4361EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            'A2:H1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],
        ];
    }
}

// SHEET RIWAYAT PERBAIKAN
class PerbaikanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return RiwayatPerbaikan::with('timbangan')
            ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->orderBy('tanggal_masuk_lab', 'desc')
            ->get()
            ->map(function ($perbaikan) {
                return [
                    'Kode Asset' => $perbaikan->timbangan->kode_asset ?? '-',
                    'Nomor Seri' => $perbaikan->timbangan->nomor_seri_unik ?? '-',
                    'Line Sebelumnya' => $perbaikan->line_sebelumnya,
                    'Tanggal Masuk' => $perbaikan->tanggal_masuk_lab ? $perbaikan->tanggal_masuk_lab->format('d/m/Y') : '-',
                    'Keluhan' => $perbaikan->deskripsi_keluhan,
                    'Status' => $perbaikan->status_perbaikan,
                    'Tindakan' => $perbaikan->tindakan_perbaikan ?? '-',
                    'Perbaikan Eksternal' => $perbaikan->perbaikan_eksternal ?? '-',
                    'Tanggal Selesai' => $perbaikan->tanggal_selesai_perbaikan ? 
                        $perbaikan->tanggal_selesai_perbaikan->format('d/m/Y') : '-',
                    'Line Tujuan' => $perbaikan->line_tujuan ?? '-',
                    'Durasi (Hari)' => $perbaikan->getDurasiPerbaikanAttribute() ?? '-'
                ];
            });
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI',
            'LINE SEBELUMNYA',
            'TANGGAL MASUK',
            'KELUHAN',
            'STATUS',
            'TINDAKAN PERBAIKAN',
            'PERBAIKAN EKSTERNAL',
            'TANGGAL SELESAI',
            'LINE TUJUAN',
            'DURASI (HARI)'
        ];
    }

    public function title(): string
    {
        return 'Riwayat Perbaikan';
    }

    public function styles(Worksheet $sheet)
    {
        foreach(range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4361EE']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            'A2:K1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],
        ];
    }
}