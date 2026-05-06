<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\Timbangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimbanganSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    use ExcelHelpers;

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
                'Kode Asset'      => $t->kode_asset,
                'Merk Tipe Seri'  => $t->merk_tipe_no_seri,
                'Tanggal Datang'  => $t->tanggal_datang ? $t->tanggal_datang->format('d/m/Y') : '-',
                'Lokasi Asli'     => $t->lokasi_asli ?? '-',
                'Lokasi Sekarang' => $t->status_line ?: 'Lab',
                'Kondisi'         => $t->kondisi_saat_ini,
                'Status Lengkap'  => $t->getStatusLengkapAttribute(),
                'Terakhir Update' => $t->updated_at ? $t->updated_at->format('d/m/Y H:i') : '-',
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

    public function title(): string
    {
        return 'Data Timbangan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}