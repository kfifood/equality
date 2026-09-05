<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\RiwayatPenggunaan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenggunaanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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

        return RiwayatPenggunaan::with('peralatan.kategoriAlat')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($this->line !== '', fn($q) => $q->where('line_tujuan', $this->line))
            ->orderBy('tanggal_pemakaian', 'desc')
            ->get()
            ->map(fn($p) => [
                'Kode Asset'        => $p->kode_asset_lengkap,
                'Kategori'          => $p->peralatan->nama_kategori ?? '-',
                'Merk Tipe Seri'    => $p->merk_lengkap,
                'Line Tujuan'       => $p->line_tujuan,
                'Tanggal Pemakaian' => $p->tanggal_pemakaian_formatted,
                'PIC'               => $p->pic ?? '-',
                'Keterangan'        => $p->keterangan ?? '-',
                'Status'            => $p->status_penggunaan,
            ]);
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'KATEGORI', 'MERK TIPE SERI', 'LINE TUJUAN',
            'TANGGAL PEMAKAIAN', 'PIC', 'KETERANGAN', 'STATUS',
        ];
    }

    public function title(): string
    {
        return 'Riwayat Penggunaan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}