<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $queryPenggunaan = RiwayatPenggunaan::with('timbangan');
        $queryPerbaikan = RiwayatPerbaikan::with('timbangan');

        // Filter by date range
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $queryPenggunaan->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
            $queryPerbaikan->whereDate('tanggal_masuk_lab', '>=', $request->tanggal_dari);
        }

        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $queryPenggunaan->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
            $queryPerbaikan->whereDate('tanggal_masuk_lab', '<=', $request->tanggal_sampai);
        }

        // Filter by timbangan
        if ($request->has('timbangan_id') && $request->timbangan_id != '') {
            $queryPenggunaan->where('timbangan_id', $request->timbangan_id);
            $queryPerbaikan->where('timbangan_id', $request->timbangan_id);
        }

        // Filter by line
        if ($request->has('line') && $request->line != '') {
            $queryPenggunaan->where('line_tujuan', $request->line);
            $queryPerbaikan->where('line_sebelumnya', $request->line);
        }

        // Filter by kode asset
        if ($request->has('kode_asset') && $request->kode_asset != '') {
            $queryPenggunaan->whereHas('timbangan', function($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
            $queryPerbaikan->whereHas('timbangan', function($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
        }

        $riwayatPenggunaan = $queryPenggunaan->orderBy('created_at', 'desc')->paginate(15, ['*'], 'penggunaan_page');
        $riwayatPerbaikan = $queryPerbaikan->orderBy('created_at', 'desc')->paginate(15, ['*'], 'perbaikan_page');

        $timbanganList = Timbangan::orderBy('kode_asset')->get();

        return view('riwayat.index', compact(
            'riwayatPenggunaan', 
            'riwayatPerbaikan',
            'timbanganList'
        ));
    }

    public function timbangan($id)
    {
        $timbangan = Timbangan::with([
            'riwayatPerbaikan' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'riwayatPenggunaan' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        return view('riwayat.timbangan', compact('timbangan'));
    }

    // Method untuk timeline gabungan - FIXED VERSION
    public function timeline(Request $request)
    {
        // Query untuk penggunaan dengan pagination
        $penggunaanQuery = RiwayatPenggunaan::with('timbangan')
            ->select(
                'id',
                'timbangan_id',
                'line_tujuan as lokasi',
                'tanggal_pemakaian as tanggal',
                'pic',
                'keterangan',
                DB::raw("'penggunaan' as jenis"),
                'created_at'
            );

        // Query untuk perbaikan dengan pagination  
        $perbaikanQuery = RiwayatPerbaikan::with('timbangan')
            ->select(
                'id',
                'timbangan_id', 
                'line_sebelumnya as lokasi',
                'tanggal_masuk_lab as tanggal',
                DB::raw("NULL as pic"),
                'deskripsi_keluhan as keterangan',
                DB::raw("'perbaikan' as jenis"),
                'created_at'
            );

        // Apply filters untuk penggunaan
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $penggunaanQuery->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
            $perbaikanQuery->whereDate('tanggal_masuk_lab', '>=', $request->tanggal_dari);
        }
        
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $penggunaanQuery->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
            $perbaikanQuery->whereDate('tanggal_masuk_lab', '<=', $request->tanggal_sampai);
        }
        
        if ($request->has('timbangan_id') && $request->timbangan_id != '') {
            $penggunaanQuery->where('timbangan_id', $request->timbangan_id);
            $perbaikanQuery->where('timbangan_id', $request->timbangan_id);
        }

        // Filter by kode asset
        if ($request->has('kode_asset') && $request->kode_asset != '') {
            $penggunaanQuery->whereHas('timbangan', function($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
            $perbaikanQuery->whereHas('timbangan', function($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
        }

        // Gabungkan dengan UNION dan paginate
        $riwayat = $penggunaanQuery->unionAll($perbaikanQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $timbanganList = Timbangan::orderBy('kode_asset')->get();

        return view('riwayat.timeline', compact('riwayat', 'timbanganList'));
    }
}