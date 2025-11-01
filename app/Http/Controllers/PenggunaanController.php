<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use App\Models\RiwayatPenggunaan;
use App\Models\MasterLine;
use Illuminate\Http\Request;

class PenggunaanController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatPenggunaan::with(['timbangan']);

        // Filter tanggal
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }

        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }

        // Filter line
        if ($request->has('line_tujuan') && $request->line_tujuan != '') {
            $query->where('line_tujuan', $request->line_tujuan);
        }

        // Filter kode asset
        if ($request->has('kode_asset') && $request->kode_asset != '') {
            $query->whereHas('timbangan', function($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
        }

        // Filter kondisi (ambil dari tabel timbangan)
        if ($request->has('kondisi') && $request->kondisi != '') {
            $query->whereHas('timbangan', function($q) use ($request) {
                $q->where('kondisi_saat_ini', $request->kondisi);
            });
        }

        $penggunaan = $query->orderBy('tanggal_pemakaian', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        
        // Hanya timbangan yang kondisi BAIK dan di LAB (status_line = NULL) - TERMASUK YANG BARU SELESAI PERBAIKAN
        $timbanganList = Timbangan::where('kondisi_saat_ini', 'Baik')
                                ->whereNull('status_line')
                                ->orderBy('kode_asset')
                                ->get();
                                
        $lineList = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();

        return view('penggunaan.index', compact('penggunaan', 'timbanganList', 'lineList'));
    }

    // Method untuk create modal
    public function create($timbangan_id = null)
    {
        // Hanya timbangan yang kondisi BAIK dan di LAB (siap digunakan) - TERMASUK YANG BARU SELESAI PERBAIKAN
        $timbangan = Timbangan::where('kondisi_saat_ini', 'Baik')
                            ->whereNull('status_line')
                            ->orderBy('kode_asset')
                            ->get();
                            
        $lines = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();
        $selectedTimbangan = $timbangan_id ? Timbangan::find($timbangan_id) : null;

        return response()->json([
            'success' => true,
            'html' => view('penggunaan.partials.create-modal', compact('timbangan', 'lines', 'selectedTimbangan'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id' => 'required|exists:timbangan,id',
            'line_tujuan' => 'required|string',
            'tanggal_pemakaian' => 'required|date',
            'pic' => 'nullable|string',
            'keterangan' => 'nullable|string'
        ]);

        $timbangan = Timbangan::findOrFail($request->timbangan_id);

        // Validasi: Pastikan timbangan dalam kondisi baik dan di lab (TERMASUK YANG BARU SELESAI PERBAIKAN)
        if ($timbangan->kondisi_saat_ini !== 'Baik') {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan tidak dalam kondisi baik. Tidak bisa digunakan.'
            ], 422);
        }

        if ($timbangan->status_line !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan sedang digunakan di ' . $timbangan->status_line . '. Tidak bisa digunakan lagi.'
            ], 422);
        }

        // Buat riwayat penggunaan - TIDAK PERLU simpan kondisi di sini
        $riwayat = RiwayatPenggunaan::create([
            'timbangan_id' => $request->timbangan_id,
            'line_tujuan' => $request->line_tujuan,
            'tanggal_pemakaian' => $request->tanggal_pemakaian,
            'pic' => $request->pic,
            'keterangan' => $request->keterangan
        ]);

        // Update timbangan - pindahkan ke line tujuan
        $timbangan->update([
            'status_line' => $request->line_tujuan
            // kondisi_saat_ini tetap 'Baik'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penggunaan timbangan ' . $timbangan->kode_asset . ' berhasil dicatat. Timbangan sekarang di ' . $request->line_tujuan
        ]);
    }
}