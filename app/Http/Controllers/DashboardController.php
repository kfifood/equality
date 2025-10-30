<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timbangan;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use App\Models\MasterLine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Statistik utama untuk timbangan
            $stats = [
                'total_timbangan' => Timbangan::count(),
                'timbangan_baik' => Timbangan::where('kondisi_saat_ini', 'Baik')->count(),
                'timbangan_rusak' => Timbangan::where('kondisi_saat_ini', 'Rusak')->count(),
                'timbangan_perbaikan' => Timbangan::where('kondisi_saat_ini', 'Dalam Perbaikan')->count(),
                'total_line' => Timbangan::distinct('status_line')->whereNotNull('status_line')->count('status_line'),
                'perbaikan_aktif' => RiwayatPerbaikan::whereIn('status_perbaikan', ['Masuk Lab', 'Dalam Perbaikan'])->count(),
                'penggunaan_bulan_ini' => RiwayatPenggunaan::whereMonth('tanggal_pemakaian', now()->month)->count(),
                'timbangan_di_lab' => Timbangan::whereNull('status_line')->count(),
            ];

            // Hitung persentase
            $stats['persentase_baik'] = $stats['total_timbangan'] > 0 ? 
                round(($stats['timbangan_baik'] / $stats['total_timbangan']) * 100, 1) : 0;

        } catch (\Exception $e) {
            // Fallback jika ada error
            $stats = [
                'total_timbangan' => 0,
                'timbangan_baik' => 0,
                'timbangan_rusak' => 0,
                'timbangan_perbaikan' => 0,
                'total_line' => 0,
                'perbaikan_aktif' => 0,
                'penggunaan_bulan_ini' => 0,
                'timbangan_di_lab' => 0,
                'persentase_baik' => 0,
            ];
        }

        // Data terbaru
        $recentTimbangan = Timbangan::orderBy('updated_at', 'desc')->limit(5)->get();
        $recentPerbaikan = RiwayatPerbaikan::with(['timbangan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        $recentPenggunaan = RiwayatPenggunaan::with(['timbangan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Data untuk chart distribusi line
        $distribusiLine = Timbangan::select('status_line', DB::raw('count(*) as total'))
            ->whereNotNull('status_line')
            ->groupBy('status_line')
            ->get();

        // Timbangan yang perlu perhatian
        $timbanganPerhatian = Timbangan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        // Statistik perbaikan lama (lebih dari 7 hari)
        $perbaikanLama = RiwayatPerbaikan::whereIn('status_perbaikan', ['Masuk Lab', 'Dalam Perbaikan'])
            ->where('tanggal_masuk_lab', '<=', now()->subDays(7))
            ->count();

        return view('dashboard', compact(
            'stats', 
            'recentTimbangan', 
            'recentPerbaikan', 
            'recentPenggunaan',
            'distribusiLine',
            'timbanganPerhatian',
            'perbaikanLama'
        ));
    }
}