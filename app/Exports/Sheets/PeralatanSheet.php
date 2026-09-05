<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\Peralatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeralatanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    use ExcelHelpers;

    public function __construct(
        protected $year,
        protected $month,
        protected $line = ''
    ) {}

    public function collection()
    {
        return Peralatan::with('kategoriAlat')
            ->orderBy('kode_asset')
            ->when($this->line !== '', fn($q) => $q->where('status_line', $this->line))
            ->get()
            ->map(fn($p) => [
                'Kode Asset'      => $p->kode_asset,
                'Kategori'        => $p->nama_kategori,
                'Merk Tipe Seri'  => $p->merk_tipe_lengkap,
                'Spesifikasi'     => $p->spesifikasi_ringkas,
                'Tanggal Datang'  => $p->tanggal_datang ? $p->tanggal_datang->format('d/m/Y') : '-',
                'Lokasi Asli'     => $p->lokasi_asli ?? '-',
                'Lokasi Sekarang' => $p->status_line ?: 'Lab',
                'Kondisi'         => $p->kondisi_saat_ini,
                'Status Lengkap'  => $p->status_lengkap,
                'Terakhir Update' => $p->updated_at ? $p->updated_at->format('d/m/Y H:i') : '-',
            ]);
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'KATEGORI', 'MERK TIPE SERI', 'SPESIFIKASI', 'TANGGAL DATANG',
            'LOKASI ASLI', 'LOKASI SEKARANG', 'KONDISI',
            'STATUS LENGKAP', 'TERAKHIR UPDATE',
        ];
    }

    public function title(): string
    {
        return 'Data Peralatan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}