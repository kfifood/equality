<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\ExcelHelpers;
use App\Models\Kalibrasi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KalibrasiSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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

        return Kalibrasi::with('peralatan.kategoriAlat')
            ->whereBetween('tanggal_pelaksanaan', [$startDate, $endDate])
            // Filter pakai dept_bagian (snapshot lokasi saat kalibrasi
            // dilaksanakan), konsisten dengan pola filter historis di sheet lain.
            ->when($this->line !== '', fn($q) => $q->where('dept_bagian', $this->line))
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->get()
            ->map(fn($k) => [
                'Kode Asset'          => $k->peralatan->kode_asset ?? '-',
                'Kategori'            => $k->peralatan->nama_kategori ?? '-',
                'Merk Tipe Seri'      => $k->peralatan->merk_tipe_lengkap ?? '-',
                'No. Sertifikat'      => $k->calibration_number ?? '-',
                'Tanggal Pelaksanaan' => $k->tanggal_pelaksanaan ? $k->tanggal_pelaksanaan->format('d/m/Y') : '-',
                'Dept/Bagian'         => $k->dept_bagian ?? '-',
                // Generik: otomatis pakai beda_maksimum (mis. Timbangan) atau
                // ringkasan data_pengukuran (mis. Thermometer), tergantung
                // kategori alatnya — lihat Kalibrasi::getHasilPengukuranRingkasAttribute().
                'Hasil Pengukuran'    => $k->hasil_pengukuran_ringkas,
                'Hasil'               => $k->hasil ?? '-',
                'Pelaksana'           => $k->pelaksana ?? '-',
                'Catatan'             => $k->catatan ?? '-',
            ]);
    }

    public function headings(): array
    {
        return [
            'KODE ASSET', 'KATEGORI', 'MERK TIPE SERI', 'NO. SERTIFIKAT',
            'TANGGAL PELAKSANAAN', 'DEPT/BAGIAN', 'HASIL PENGUKURAN',
            'HASIL', 'PELAKSANA', 'CATATAN',
        ];
    }

    public function title(): string
    {
        return 'Kalibrasi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => $this->headerStyle(),
        ];
    }
}