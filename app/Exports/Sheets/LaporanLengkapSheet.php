<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\RiwayatPenggunaan;
use App\Models\RiwayatPerbaikan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanLengkapSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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

        $penggunaanData = RiwayatPenggunaan::with('peralatan.kategoriAlat')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($this->line !== '', fn($q) => $q->where('line_tujuan', $this->line))
            ->orderBy('tanggal_pemakaian')
            ->get();

        $perbaikanData = RiwayatPerbaikan::with([
                'peralatan.kategoriAlat',
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
                'kode_asset'      => $p->kode_asset_lengkap,
                'kategori'        => $p->peralatan->nama_kategori ?? '-',
                'merk_tipe'       => $p->merk_lengkap,
                'tanggal'         => $p->tanggal_pemakaian->format('d/m/Y'),
                'jenis'           => 'PENGGUNAAN',
                'line'            => $p->line_tujuan,
                'pic'             => $p->pic ?? '-',
                'keterangan'      => $p->keterangan ?? '-',
                'keluhan'         => '-',
                'tindakan'        => '-',
                'perbaikan_ekst'  => '-',
                'tanggal_selesai' => '-',
                'durasi_hari'     => '-',
                'status'          => $p->status_penggunaan,
            ]);
        }

        foreach ($perbaikanData as $p) {
            $data->push([
                'kode_asset'      => $p->kode_asset_lengkap,
                'kategori'        => $p->peralatan->nama_kategori ?? '-',
                'merk_tipe'       => $p->merk_lengkap,
                'tanggal'         => $p->tanggal_masuk_lab->format('d/m/Y'),
                'jenis'           => 'PERBAIKAN',
                'line'            => $p->line_sebelumnya ?? '-',
                'pic'             => $p->pic_teknik ?? '-',
                'keterangan'      => '-',
                'keluhan'         => $this->resolveKeluhan($p),
                'tindakan'        => $this->resolveTindakan($p),
                'perbaikan_ekst'  => $p->perbaikan_eksternal ?? '-',
                'tanggal_selesai' => $p->tanggal_selesai_perbaikan
                    ? $p->tanggal_selesai_perbaikan->format('d/m/Y') : '-',
                'durasi_hari'     => $p->durasi_perbaikan ?? '-',
                'status'          => $p->status_perbaikan,
            ]);
        }

        return $data->sortBy(fn($item) => Carbon::createFromFormat('d/m/Y', $item['tanggal']))->values();
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'KATEGORI', 'MERK TIPE', 'TANGGAL', 'JENIS', 'LINE', 'PIC',
            'KETERANGAN', 'KELUHAN', 'TINDAKAN PERBAIKAN',
            'PERBAIKAN EKSTERNAL', 'TANGGAL SELESAI', 'DURASI (HARI)', 'STATUS',
        ];
    }

    public function title(): string
    {
        return 'Laporan Lengkap';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}