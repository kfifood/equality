<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\Peralatan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RiwayatPergerakanSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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

        $peralatanList = Peralatan::with([
            'kategoriAlat',
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

        foreach ($peralatanList as $peralatan) {
            $allHistory = collect();

            foreach ($peralatan->riwayatPenggunaan as $p) {
                $allHistory->push([
                    'type'        => 'PENGGUNAAN',
                    'date'        => $p->tanggal_pemakaian,
                    'line_tujuan' => $p->line_tujuan,
                    'pic'         => $p->pic ?? '-',
                    'keterangan'  => $p->keterangan ?? '-',
                    'keluhan'     => '-',
                    'tindakan'    => '-',
                    'status'      => $p->status_penggunaan,
                ]);
            }

            foreach ($peralatan->riwayatPerbaikan as $p) {
                $allHistory->push([
                    'type'        => 'PERBAIKAN',
                    'date'        => $p->tanggal_masuk_lab,
                    'line_tujuan' => $p->line_sebelumnya ?? 'Lab',
                    'pic'         => $p->pic_teknik ?? '-',
                    'keterangan'  => '-',
                    'keluhan'     => $this->resolveKeluhan($p),
                    'tindakan'    => $this->resolveTindakan($p),
                    'status'      => $p->status_perbaikan,
                ]);

                if ($p->tanggal_selesai_perbaikan) {
                    $allHistory->push([
                        'type'        => 'SELESAI PERBAIKAN',
                        'date'        => $p->tanggal_selesai_perbaikan,
                        'line_tujuan' => $p->line_tujuan ?? 'Lab',
                        'pic'         => $p->pic_teknik ?? '-',
                        'keterangan'  => '-',
                        'keluhan'     => $this->resolveKeluhan($p),
                        'tindakan'    => $this->resolveTindakan($p),
                        'status'      => 'SELESAI',
                    ]);
                }
            }

            // Skip peralatan tanpa aktivitas di bulan ini
            if ($allHistory->isEmpty()) {
                continue;
            }

            $sortedHistory = $allHistory->sortBy('date');
            $kategori      = $peralatan->nama_kategori;

            // Status awal
            $data->push([
                'kode_asset'      => $peralatan->kode_asset,
                'kategori'        => $kategori,
                'merk_tipe'       => $peralatan->merk_tipe_lengkap,
                'jenis_aktivitas' => 'STATUS AWAL',
                'tanggal'         => $startDate->format('d/m/Y'),
                'line_tujuan'     => $peralatan->lokasi_asli ?? 'Lab',
                'pic'             => '-',
                'keterangan'      => 'Status awal bulan',
                'keluhan'         => '-',
                'tindakan'        => '-',
                'status'          => $peralatan->kondisi_saat_ini,
            ]);

            // Semua aktivitas
            foreach ($sortedHistory as $h) {
                $data->push([
                    'kode_asset'      => $peralatan->kode_asset,
                    'kategori'        => $kategori,
                    'merk_tipe'       => $peralatan->merk_tipe_lengkap,
                    'jenis_aktivitas' => $h['type'],
                    'tanggal'         => $h['date']->format('d/m/Y'),
                    'line_tujuan'     => $h['line_tujuan'],
                    'pic'             => $h['pic'],
                    'keterangan'      => $h['keterangan'],
                    'keluhan'         => $h['keluhan'],
                    'tindakan'        => $h['tindakan'],
                    'status'          => $h['status'],
                ]);
            }

            // Status akhir
            $data->push([
                'kode_asset'      => $peralatan->kode_asset,
                'kategori'        => $kategori,
                'merk_tipe'       => $peralatan->merk_tipe_lengkap,
                'jenis_aktivitas' => 'STATUS AKHIR',
                'tanggal'         => $endDate->format('d/m/Y'),
                'line_tujuan'     => $peralatan->status_line ?? 'Lab',
                'pic'             => '-',
                'keterangan'      => 'Status akhir bulan',
                'keluhan'         => '-',
                'tindakan'        => '-',
                'status'          => $peralatan->kondisi_saat_ini,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'KATEGORI', 'MERK TIPE', 'JENIS AKTIVITAS',
            'TANGGAL', 'LINE', 'PIC',
            'KETERANGAN', 'KELUHAN', 'TINDAKAN', 'STATUS',
        ];
    }

    public function title(): string
    {
        return 'Riwayat Pergerakan';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}