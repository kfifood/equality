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
        }

        return $sheets;
    }
}

// SHEET BARU: LAPORAN LENGKAP DENGAN KOLOM YANG DIMINTA
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
        $timbanganList = Timbangan::with([
            'riwayatPenggunaan' => function($query) {
                $query->orderBy('tanggal_pemakaian', 'desc');
            },
            'riwayatPerbaikan' => function($query) {
                $query->orderBy('tanggal_masuk_lab', 'desc');
            }
        ])->orderBy('kode_asset')->orderBy('nomor_seri_unik')->get();

        $data = collect();

        foreach ($timbanganList as $timbangan) {
            // Data dasar timbangan
            $baseData = [
                'kode_asset' => $timbangan->kode_asset,
                'nomor_seri' => $timbangan->nomor_seri_unik,
                'merk_tipe_no_seri' => $timbangan->merk_tipe_no_seri,
                'tanggal_datang' => $timbangan->tanggal_datang ? $timbangan->tanggal_datang->format('d/m/Y') : '',
            ];

            // Jika ada riwayat penggunaan
            if ($timbangan->riwayatPenggunaan->count() > 0) {
                foreach ($timbangan->riwayatPenggunaan as $penggunaan) {
                    $data->push(array_merge($baseData, [
                        'tanggal_pemakaian' => $penggunaan->tanggal_pemakaian ? $penggunaan->tanggal_pemakaian->format('d/m/Y') : '',
                        'lokasi_pemakaian' => $penggunaan->line_tujuan,
                        'tanggal_kerusakan' => '', // Akan diisi dari data perbaikan
                        'keluhan' => '', // Akan diisi dari data perbaikan
                        'perbaikan' => '', // Akan diisi dari data perbaikan
                        'perbaikan_eksternal' => '', // Akan diisi dari data perbaikan
                        'tanggal_rilis' => '', // Akan diisi dari data perbaikan
                        'status_line' => $timbangan->status_line,
                    ]));
                }
            } else {
                // Jika tidak ada penggunaan, tetap tampilkan data dasar
                $data->push(array_merge($baseData, [
                    'tanggal_pemakaian' => '',
                    'lokasi_pemakaian' => '',
                    'tanggal_kerusakan' => '',
                    'keluhan' => '',
                    'perbaikan' => '',
                    'perbaikan_eksternal' => '',
                    'tanggal_rilis' => '',
                    'status_line' => $timbangan->status_line,
                ]));
            }

            // Tambahkan data perbaikan jika ada
            if ($timbangan->riwayatPerbaikan->count() > 0) {
                foreach ($timbangan->riwayatPerbaikan as $perbaikan) {
                    // Cari penggunaan yang sesuai dengan perbaikan ini
                    $penggunaanSebelumnya = $timbangan->riwayatPenggunaan
                        ->where('line_tujuan', $perbaikan->line_sebelumnya)
                        ->where('tanggal_pemakaian', '<=', $perbaikan->tanggal_masuk_lab)
                        ->sortByDesc('tanggal_pemakaian')
                        ->first();

                    $data->push(array_merge($baseData, [
                        'tanggal_pemakaian' => $penggunaanSebelumnya ? $penggunaanSebelumnya->tanggal_pemakaian->format('d/m/Y') : '',
                        'lokasi_pemakaian' => $perbaikan->line_sebelumnya,
                        'tanggal_kerusakan' => $perbaikan->tanggal_masuk_lab ? $perbaikan->tanggal_masuk_lab->format('d/m/Y') : '',
                        'keluhan' => $perbaikan->deskripsi_keluhan,
                        'perbaikan' => $perbaikan->tindakan_perbaikan,
                        'perbaikan_eksternal' => $perbaikan->perbaikan_eksternal,
                        'tanggal_rilis' => $perbaikan->tanggal_selesai_perbaikan ? $perbaikan->tanggal_selesai_perbaikan->format('d/m/Y') : '',
                        'status_line' => $perbaikan->line_tujuan ?: 'Lab',
                    ]));
                }
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI UNIK',
            'MERK, TIPE & NO SERI',
            'TANGGAL DATANG',
            'TANGGAL PEMAKAIAN',
            'LOKASI PEMAKAIAN (LINE)',
            'TANGGAL KERUSAKAN (PENGEMBALIAN)',
            'KELUHAN',
            'PERBAIKAN',
            'PERBAIKAN EKSTERNAL',
            'TANGGAL RILIS (SETELAH PERBAIKAN)',
            'STATUS LINE (LOKASI SEKARANG)'
        ];
    }

    public function title(): string
    {
        return 'Laporan Lengkap';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4361EE']]
            ],
            // Auto size columns
            'A:L' => ['autoSize' => true],
        ];
    }
}

// SHEET SUMMARY (tetap sama)
class SummarySheet implements FromCollection, WithHeadings, WithTitle
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

        $data = collect([
            [
                'Metric' => 'Total Timbangan',
                'Value' => Timbangan::count()
            ],
            [
                'Metric' => 'Timbangan Baik',
                'Value' => Timbangan::where('kondisi_saat_ini', 'Baik')->count()
            ],
            [
                'Metric' => 'Timbangan Rusak', 
                'Value' => Timbangan::where('kondisi_saat_ini', 'Rusak')->count()
            ],
            [
                'Metric' => 'Dalam Perbaikan',
                'Value' => Timbangan::where('kondisi_saat_ini', 'Dalam Perbaikan')->count()
            ],
            [
                'Metric' => 'Penggunaan Bulan Ini',
                'Value' => RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])->count()
            ],
            [
                'Metric' => 'Perbaikan Bulan Ini',
                'Value' => RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])->count()
            ],
            [
                'Metric' => 'Timbangan di Lab',
                'Value' => Timbangan::whereNull('status_line')->count()
            ],
            [
                'Metric' => 'Timbangan di Line',
                'Value' => Timbangan::whereNotNull('status_line')->count()
            ]
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'METRIC',
            'VALUE'
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

// SHEET TIMBANGAN (update dengan nomor seri)
class TimbanganSheet implements FromCollection, WithHeadings, WithTitle
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
        return Timbangan::orderBy('kode_asset')->orderBy('nomor_seri_unik')->get()->map(function ($timbangan) {
            return [
                'Kode Asset' => $timbangan->kode_asset,
                'Nomor Seri Unik' => $timbangan->nomor_seri_unik,
                'Merk & Seri' => $timbangan->merk_tipe_no_seri,
                'Tanggal Datang' => $timbangan->tanggal_datang ? $timbangan->tanggal_datang->format('d/m/Y') : '',
                'Lokasi' => $timbangan->status_line ?: 'Lab',
                'Kondisi' => $timbangan->kondisi_saat_ini,
                'Terakhir Update' => $timbangan->updated_at ? $timbangan->updated_at->format('d/m/Y H:i') : ''
            ];
        });
    }

    public function headings(): array
    {
        return [
            'KODE ASSET',
            'NOMOR SERI UNIK',
            'MERK & SERI', 
            'TANGGAL DATANG',
            'LOKASI',
            'KONDISI',
            'TERAKHIR UPDATE'
        ];
    }

    public function title(): string
    {
        return 'Data Timbangan';
    }
}

// SHEET PENGGUNAAN (tetap sama)
class PenggunaanSheet implements FromCollection, WithHeadings, WithTitle
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
                    'Kode Asset' => $penggunaan->timbangan->kode_asset ?? '',
                    'Nomor Seri' => $penggunaan->timbangan->nomor_seri_unik ?? '',
                    'Line Tujuan' => $penggunaan->line_tujuan,
                    'Tanggal Pemakaian' => $penggunaan->tanggal_pemakaian ? $penggunaan->tanggal_pemakaian->format('d/m/Y') : '',
                    'PIC' => $penggunaan->pic ?? '',
                    'Keterangan' => $penggunaan->keterangan ?? ''
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
            'KETERANGAN'
        ];
    }

    public function title(): string
    {
        return 'Riwayat Penggunaan';
    }
}

// SHEET PERBAIKAN (tetap sama)
class PerbaikanSheet implements FromCollection, WithHeadings, WithTitle
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
                    'Kode Asset' => $perbaikan->timbangan->kode_asset ?? '',
                    'Nomor Seri' => $perbaikan->timbangan->nomor_seri_unik ?? '',
                    'Line Sebelumnya' => $perbaikan->line_sebelumnya,
                    'Tanggal Masuk' => $perbaikan->tanggal_masuk_lab ? $perbaikan->tanggal_masuk_lab->format('d/m/Y') : '',
                    'Keluhan' => $perbaikan->deskripsi_keluhan,
                    'Status' => $perbaikan->status_perbaikan,
                    'Tindakan' => $perbaikan->tindakan_perbaikan ?? '',
                    'Perbaikan Eksternal' => $perbaikan->perbaikan_eksternal ?? '',
                    'Tanggal Selesai' => $perbaikan->tanggal_selesai_perbaikan ? $perbaikan->tanggal_selesai_perbaikan->format('d/m/Y') : '',
                    'Line Tujuan' => $perbaikan->line_tujuan ?? ''
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
            'LINE TUJUAN'
        ];
    }

    public function title(): string
    {
        return 'Riwayat Perbaikan';
    }
}