<?php

namespace App\Http\Controllers;

use App\Models\Peralatan;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use App\Models\MasterLine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\PeralatanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function index(Request $request)
{
    $period = $request->get('period', 'monthly');
    $year   = $request->get('year', date('Y'));
    $month  = $request->get('month', date('m'));
    $line   = (string) $request->get('line', '');

    $lineList = MasterLine::where('status_aktif', true)
        ->orderBy('nama_line')
        ->pluck('nama_line');

    $statistik = $this->buildStatistik($line);

    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

    $penggunaanPeriod = $this->buildPenggunaanQuery($startDate, $endDate, $line)->count();
    $perbaikanPeriod  = $this->buildPerbaikanQuery($startDate, $endDate, $line)->count();

    $distribusiLine = $this->buildDistribusiLine($line);

    $recentPenggunaan = $this->buildPenggunaanQuery($startDate, $endDate, $line)
        ->with('peralatan')
        ->orderBy('tanggal_pemakaian', 'desc')
        ->limit(10)->get();

    $recentPerbaikan = $this->buildPerbaikanQuery($startDate, $endDate, $line)
        ->with(['peralatan', 'laporanKerusakan.keluhanList', 'detailTindakan.masterTindakan'])
        ->orderBy('tanggal_masuk_lab', 'desc')
        ->limit(10)->get();

    $peralatanList = Peralatan::orderBy('kode_asset')
        ->when($line !== '', fn($q) => $q->where('status_line', $line))
        ->get();

    $years = range(date('Y') - 2, date('Y'));
    $months = [
        '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
        '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
        '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
        '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
    ];

    if ($request->has('export_pdf')) {
        return $this->generatePDF(
            $year, $month, $line,
            $statistik, $penggunaanPeriod, $perbaikanPeriod,
            $distribusiLine, $recentPenggunaan, $recentPerbaikan, $peralatanList
        );
    }

    return view('laporan.index', compact(
        'statistik', 'distribusiLine',
        'recentPenggunaan', 'recentPerbaikan',
        'penggunaanPeriod', 'perbaikanPeriod',
        'peralatanList', 'years', 'months',
        'year', 'month', 'period', 'line', 'lineList'
    ));
}

    public function export(Request $request)
{
    $type   = $request->get('type', 'excel');
    $format = $request->get('format', 'summary');
    $year   = $request->get('year', date('Y'));
    $month  = $request->get('month', date('m'));
    $line   = (string) $request->get('line', '');

    try {
        if ($type === 'pdf') {
            $statistik = $this->buildStatistik($line);

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

            $penggunaanPeriod = $this->buildPenggunaanQuery($startDate, $endDate, $line)->count();
            $perbaikanPeriod  = $this->buildPerbaikanQuery($startDate, $endDate, $line)->count();
            $distribusiLine   = $this->buildDistribusiLine($line);

            $recentPenggunaan = $this->buildPenggunaanQuery($startDate, $endDate, $line)
                ->with('peralatan')->orderBy('tanggal_pemakaian', 'desc')->limit(10)->get();

            $recentPerbaikan = $this->buildPerbaikanQuery($startDate, $endDate, $line)
                ->with(['peralatan', 'laporanKerusakan.keluhanList', 'detailTindakan.masterTindakan'])
                ->orderBy('tanggal_masuk_lab', 'desc')->limit(10)->get();

            $peralatanList = Peralatan::orderBy('kode_asset')
                ->when($line !== '', fn($q) => $q->where('status_line', $line))
                ->get();

            return $this->generatePDF(
                $year, $month, $line,
                $statistik, $penggunaanPeriod, $perbaikanPeriod,
                $distribusiLine, $recentPenggunaan, $recentPerbaikan, $peralatanList
            );

        } else {
            $lineSuffix = $line ? '-' . str_replace(' ', '_', $line) : '';
            $filename   = 'laporan-peralatan-' . $year . '-' . $month . $lineSuffix . '-' . $format . '.xlsx';

            return Excel::download(
                new PeralatanExport($year, $month, $format, $line),
                $filename,
                \Maatwebsite\Excel\Excel::XLSX,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        }
    } catch (\Exception $e) {
        Log::error('Export Error: ' . $e->getMessage());
        Log::error('Export Trace: ' . $e->getTraceAsString());

        return redirect()->route('laporan.index')
            ->with('error', 'Error exporting data: ' . $e->getMessage());
    }
}

    // ── Private helpers ────────────────────────────────────────────────────

    private function buildStatistik(string $line): array
    {
        $q = Peralatan::query()->when($line !== '', fn($x) => $x->where('status_line', $line));

        $total    = (clone $q)->count();
        $baik     = (clone $q)->where('kondisi_saat_ini', 'Baik')->count();

        return [
            'total'            => $total,
            'baik'             => $baik,
            'rusak'            => (clone $q)->where('kondisi_saat_ini', 'Rusak')->count(),
            'perbaikan'        => (clone $q)->where('kondisi_saat_ini', 'Dalam Perbaikan')->count(),
            'di_lab'           => $line === '' ? Peralatan::whereNull('status_line')->count() : 0,
            'di_line'          => $line === ''
                ? Peralatan::whereNotNull('status_line')->count()
                : $total,
            'persentase_baik'  => $total > 0 ? round(($baik / $total) * 100, 1) : 0,
        ];
    }

    private function buildPenggunaanQuery($startDate, $endDate, string $line)
    {
        return RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->when($line !== '', fn($q) => $q->where('line_tujuan', $line));
    }

    private function buildPerbaikanQuery($startDate, $endDate, string $line)
    {
        return RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->when($line !== '', fn($q) => $q->where('line_sebelumnya', $line));
    }

    private function buildDistribusiLine(string $line)
    {
        return Peralatan::select('status_line')
            ->whereNotNull('status_line')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status_line')
            ->orderBy('total', 'desc')
            ->when($line !== '', fn($q) => $q->where('status_line', $line))
            ->get();
    }

    private function generatePDF(
        $year, $month, $line,
        $statistik, $penggunaanPeriod, $perbaikanPeriod,
        $distribusiLine, $recentPenggunaan, $recentPerbaikan, $peralatanList
    ) {
        $months = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        $data = [
            'periode'          => $months[$month] . ' ' . $year,
            'filterLine'       => $line ?: 'Semua Line',
            'tanggalCetak'     => Carbon::now()->format('d/m/Y H:i'),
            'statistik'        => $statistik,
            'penggunaanPeriod' => $penggunaanPeriod,
            'perbaikanPeriod'  => $perbaikanPeriod,
            'distribusiLine'   => $distribusiLine,
            'recentPenggunaan' => $recentPenggunaan,
            'recentPerbaikan'  => $recentPerbaikan,
            'peralatanList'    => $peralatanList,
        ];

        $suffix   = $line ? '-' . str_replace(' ', '_', $line) : '';
        $filename = 'laporan-peralatan-' . $year . '-' . $month . $suffix . '.pdf';

        $pdf = PDF::loadView('laporan.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'margin_top'           => 0,
                'margin_right'         => 0,
                'margin_bottom'        => 0,
                'margin_left'          => 0,
            ]);

        return $pdf->download($filename);
    }

    // ── Statistik page ─────────────────────────────────────────────────────

    public function statistik(Request $request)
    {
        $distribusiLine = Peralatan::select('status_line')
            ->whereNotNull('status_line')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status_line')
            ->orderBy('total', 'desc')
            ->get();

        $distribusiKondisi = Peralatan::select('kondisi_saat_ini')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('kondisi_saat_ini')
            ->get();

        $startDate = Carbon::now()->subDays(30);
        $endDate   = Carbon::now();

        $perbaikanHarian = RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal_masuk_lab) as tanggal, COUNT(*) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $penggunaanBulanan = RiwayatPenggunaan::whereYear('tanggal_pemakaian', date('Y'))
            ->selectRaw('MONTH(tanggal_pemakaian) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $mtbfData = $this->calculateMTBF();

        return view('laporan.statistik', compact(
            'distribusiLine', 'distribusiKondisi',
            'perbaikanHarian', 'penggunaanBulanan', 'mtbfData'
        ));
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/template-laporan-timbangan.xlsx');

        if (!file_exists($filePath)) {
            return redirect()->route('laporan.index')
                ->with('error', 'Template tidak ditemukan.');
        }

        return response()->download($filePath, 'template-laporan-timbangan.xlsx');
    }

    private function calculateMTBF(): array
    {
        $perbaikanData = RiwayatPerbaikan::where('status_perbaikan', 'Selesai')
            ->whereNotNull('tanggal_selesai_perbaikan')
            ->orderBy('tanggal_masuk_lab')
            ->get();

        $mtbf = [
            'total_perbaikan' => $perbaikanData->count(),
            'avg_downtime'    => 0,
            'reliability'     => 0,
        ];

        if ($perbaikanData->count() > 1) {
            $totalDays = 0;
            $count     = 0;

            for ($i = 1; $i < $perbaikanData->count(); $i++) {
                $totalDays += $perbaikanData[$i - 1]->tanggal_selesai_perbaikan
                    ->diffInDays($perbaikanData[$i]->tanggal_masuk_lab);
                $count++;
            }

            if ($count > 0) {
                $mtbf['avg_downtime'] = round($totalDays / $count, 1);
            }
        }

        $total = Peralatan::count();
        $baik  = Peralatan::where('kondisi_saat_ini', 'Baik')->count();

        if ($total > 0) {
            $mtbf['reliability'] = round(($baik / $total) * 100, 1);
        }

        return $mtbf;
    }
}