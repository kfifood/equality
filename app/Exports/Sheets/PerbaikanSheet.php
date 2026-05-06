<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\RiwayatPerbaikan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerbaikanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
                'Keluhan'             => $this->resolveKeluhan($p),
                'Tindakan Perbaikan'  => $this->resolveTindakan($p),
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

    public function title(): string
    {
        return 'Riwayat Perbaikan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}