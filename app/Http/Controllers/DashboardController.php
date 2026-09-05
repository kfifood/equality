<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peralatan;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use App\Models\MasterLine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Statistik utama untuk peralatan
            $stats = [
                'total_peralatan' => Peralatan::count(),
                'peralatan_baik' => Peralatan::where('kondisi_saat_ini', 'Baik')->count(),
                'peralatan_rusak' => Peralatan::where('kondisi_saat_ini', 'Rusak')->count(),
                'peralatan_perbaikan' => Peralatan::where('kondisi_saat_ini', 'Dalam Perbaikan')->count(),
                'total_line' => Peralatan::distinct('status_line')->whereNotNull('status_line')->count('status_line'),
                'perbaikan_aktif' => RiwayatPerbaikan::whereIn('status_perbaikan', ['Masuk Lab', 'Dalam Perbaikan'])->count(),
                'penggunaan_bulan_ini' => RiwayatPenggunaan::whereMonth('tanggal_pemakaian', now()->month)->count(),
                'peralatan_di_lab' => Peralatan::whereNull('status_line')->count(),
            ];

            // Hitung persentase
            $stats['persentase_baik'] = $stats['total_peralatan'] > 0 ? 
                round(($stats['peralatan_baik'] / $stats['total_peralatan']) * 100, 1) : 0;

        } catch (\Exception $e) {
            // Fallback jika ada error
            $stats = [
                'total_peralatan' => 0,
                'peralatan_baik' => 0,
                'peralatan_rusak' => 0,
                'peralatan_perbaikan' => 0,
                'total_line' => 0,
                'perbaikan_aktif' => 0,
                'penggunaan_bulan_ini' => 0,
                'peralatan_di_lab' => 0,
                'persentase_baik' => 0,
            ];
        }

        // Data terbaru
        $recentPeralatan = Peralatan::with('kategoriAlat')->orderBy('updated_at', 'desc')->limit(5)->get();
        $recentPerbaikan = RiwayatPerbaikan::with(['peralatan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $recentPenggunaan = RiwayatPenggunaan::with(['peralatan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Data untuk chart distribusi line
        $distribusiLine = Peralatan::select('status_line', DB::raw('count(*) as total'))
            ->whereNotNull('status_line')
            ->groupBy('status_line')
            ->get();

        // Peralatan yang perlu perhatian
        // NOTE: eager-load laporanKerusakanAktif supaya tombol "proses" di dashboard bisa
        // langsung buka modal proses perbaikan (butuh laporan_kerusakan_id, bukan peralatan_id —
        // alur create perbaikan langsung dari peralatan sudah tidak ada lagi).
        $peralatanPerhatian = Peralatan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
            ->with('laporanKerusakanAktif')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        // Statistik perbaikan lama (lebih dari 7 hari)
        $perbaikanLama = RiwayatPerbaikan::whereIn('status_perbaikan', ['Masuk Lab', 'Dalam Perbaikan'])
            ->where('tanggal_masuk_lab', '<=', now()->subDays(7))
            ->count();

        return view('dashboard', compact(
            'stats', 
            'recentPeralatan', 
            'recentPerbaikan', 
            'recentPenggunaan',
            'distribusiLine',
            'peralatanPerhatian',
            'perbaikanLama'
        ));
    }
}