<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use App\Models\MasterLine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\TimbanganExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        // Statistik dasar
        $statistik = [
            'total' => Timbangan::count(),
            'baik' => Timbangan::where('kondisi_saat_ini', 'Baik')->count(),
            'rusak' => Timbangan::where('kondisi_saat_ini', 'Rusak')->count(),
            'perbaikan' => Timbangan::where('kondisi_saat_ini', 'Dalam Perbaikan')->count(),
            'di_lab' => Timbangan::whereNull('status_line')->count(),
            'di_line' => Timbangan::whereNotNull('status_line')->count(),
        ];

        $statistik['persentase_baik'] = $statistik['total'] > 0 ? 
            round(($statistik['baik'] / $statistik['total']) * 100, 1) : 0;

        // Data untuk periode tertentu
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $penggunaanPeriod = RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])->count();
        $perbaikanPeriod = RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])->count();

        // Distribusi per line
        $distribusiLine = Timbangan::select('status_line')
            ->whereNotNull('status_line')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status_line')
            ->orderBy('total', 'desc')
            ->get();

        // Riwayat terbaru untuk laporan
        $recentPenggunaan = RiwayatPenggunaan::with('timbangan')
            ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
            ->orderBy('tanggal_pemakaian', 'desc')
            ->limit(10)
            ->get();

        $recentPerbaikan = RiwayatPerbaikan::with('timbangan')
            ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
            ->orderBy('tanggal_masuk_lab', 'desc')
            ->limit(10)
            ->get();

        // Data timbangan untuk PDF
        $timbanganList = Timbangan::orderBy('kode_asset')->get();

        $years = range(date('Y') - 2, date('Y'));
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        // Handle PDF export langsung dari index
        if ($request->has('export_pdf')) {
            return $this->generatePDF($year, $month, $statistik, $penggunaanPeriod, $perbaikanPeriod, $distribusiLine, $recentPenggunaan, $recentPerbaikan, $timbanganList);
        }

        return view('laporan.index', compact(
            'statistik',
            'distribusiLine',
            'recentPenggunaan',
            'recentPerbaikan',
            'penggunaanPeriod',
            'perbaikanPeriod',
            'timbanganList',
            'years',
            'months',
            'year',
            'month',
            'period'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'excel');
        $format = $request->get('format', 'summary');
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        
        try {
            if ($type === 'pdf') {
                // Untuk PDF, kita perlu mengambil data lagi
                $statistik = [
                    'total' => Timbangan::count(),
                    'baik' => Timbangan::where('kondisi_saat_ini', 'Baik')->count(),
                    'rusak' => Timbangan::where('kondisi_saat_ini', 'Rusak')->count(),
                    'perbaikan' => Timbangan::where('kondisi_saat_ini', 'Dalam Perbaikan')->count(),
                    'di_lab' => Timbangan::whereNull('status_line')->count(),
                    'di_line' => Timbangan::whereNotNull('status_line')->count(),
                ];

                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();

                $penggunaanPeriod = RiwayatPenggunaan::whereBetween('tanggal_pemakaian', [$startDate, $endDate])->count();
                $perbaikanPeriod = RiwayatPerbaikan::whereBetween('tanggal_masuk_lab', [$startDate, $endDate])->count();

                $distribusiLine = Timbangan::select('status_line')
                    ->whereNotNull('status_line')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('status_line')
                    ->orderBy('total', 'desc')
                    ->get();

                $recentPenggunaan = RiwayatPenggunaan::with('timbangan')
                    ->whereBetween('tanggal_pemakaian', [$startDate, $endDate])
                    ->orderBy('tanggal_pemakaian', 'desc')
                    ->limit(10)
                    ->get();

                $recentPerbaikan = RiwayatPerbaikan::with('timbangan')
                    ->whereBetween('tanggal_masuk_lab', [$startDate, $endDate])
                    ->orderBy('tanggal_masuk_lab', 'desc')
                    ->limit(10)
                    ->get();

                $timbanganList = Timbangan::orderBy('kode_asset')->get();

                return $this->generatePDF($year, $month, $statistik, $penggunaanPeriod, $perbaikanPeriod, $distribusiLine, $recentPenggunaan, $recentPerbaikan, $timbanganList);

            } else {
                $filename = 'laporan-timbangan-' . $year . '-' . $month . '-' . $format . '.xlsx';
                
                return Excel::download(new TimbanganExport($year, $month, $format), $filename, \Maatwebsite\Excel\Excel::XLSX, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Export Error: ' . $e->getMessage());
            Log::error('Export Trace: ' . $e->getTraceAsString());
            
            return redirect()->route('laporan.index')
                ->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    private function generatePDF($year, $month, $statistik, $penggunaanPeriod, $perbaikanPeriod, $distribusiLine, $recentPenggunaan, $recentPerbaikan, $timbanganList)
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $data = [
            'periode' => $months[$month] . ' ' . $year,
            'tanggalCetak' => Carbon::now()->format('d/m/Y H:i'),
            'statistik' => $statistik,
            'penggunaanPeriod' => $penggunaanPeriod,
            'perbaikanPeriod' => $perbaikanPeriod,
            'distribusiLine' => $distribusiLine,
            'recentPenggunaan' => $recentPenggunaan,
            'recentPerbaikan' => $recentPerbaikan,
            'timbanganList' => $timbanganList,
        ];

        $filename = 'laporan-timbangan-' . $year . '-' . $month . '.pdf';

        $pdf = PDF::loadView('laporan.pdf', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'sans-serif',
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true
                  ]);

        return $pdf->download($filename);
    }

    public function statistik(Request $request)
    {
        // Data untuk charts
        $distribusiLine = Timbangan::select('status_line')
            ->whereNotNull('status_line')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status_line')
            ->orderBy('total', 'desc')
            ->get();

        $distribusiKondisi = Timbangan::select('kondisi_saat_ini')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('kondisi_saat_ini')
            ->get();

        // Data perbaikan 30 hari terakhir
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
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

        // MTBF Calculation (Mean Time Between Failures)
        $mtbfData = $this->calculateMTBF();

        return view('laporan.statistik', compact(
            'distribusiLine', 
            'distribusiKondisi',
            'perbaikanHarian',
            'penggunaanBulanan',
            'mtbfData'
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

    private function calculateMTBF()
    {
        // Simple MTBF calculation based on repair data
        $perbaikanData = RiwayatPerbaikan::where('status_perbaikan', 'Selesai')
            ->whereNotNull('tanggal_selesai_perbaikan')
            ->orderBy('tanggal_masuk_lab')
            ->get();

        $mtbf = [
            'total_perbaikan' => $perbaikanData->count(),
            'avg_downtime' => 0,
            'reliability' => 0
        ];

        if ($perbaikanData->count() > 1) {
            $totalDays = 0;
            $count = 0;

            for ($i = 1; $i < $perbaikanData->count(); $i++) {
                $current = $perbaikanData[$i];
                $previous = $perbaikanData[$i - 1];
                
                $daysBetween = $previous->tanggal_selesai_perbaikan->diffInDays($current->tanggal_masuk_lab);
                $totalDays += $daysBetween;
                $count++;
            }

            if ($count > 0) {
                $mtbf['avg_downtime'] = round($totalDays / $count, 1);
            }
        }

        // Calculate reliability (percentage of time operational)
        $totalTimbangan = Timbangan::count();
        $timbanganBaik = Timbangan::where('kondisi_saat_ini', 'Baik')->count();
        
        if ($totalTimbangan > 0) {
            $mtbf['reliability'] = round(($timbanganBaik / $totalTimbangan) * 100, 1);
        }

        return $mtbf;
    }
}