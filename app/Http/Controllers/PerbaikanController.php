<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use App\Models\RiwayatPerbaikan;
use App\Models\MasterLine;
use Illuminate\Http\Request;

class PerbaikanController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatPerbaikan::with(['timbangan']);

        // Filter status
        if ($request->has('status') && $request->status != '') {
            $query->where('status_perbaikan', $request->status);
        }

        // Filter tanggal
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal_masuk_lab', '>=', $request->tanggal_dari);
        }

        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal_masuk_lab', '<=', $request->tanggal_sampai);
        }

        // Filter kode asset
        if ($request->has('kode_asset') && $request->kode_asset != '') {
            $query->whereHas('timbangan', function($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
        }

        $perbaikan = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Timbangan yang RUSAK atau DALAM PERBAIKAN dan masih di LINE (belum dikembalikan ke lab)
        $timbanganList = Timbangan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
                                ->whereNotNull('status_line') // Masih di line, belum dikembalikan ke lab
                                ->orderBy('kode_asset')
                                ->get();

        return view('perbaikan.index', compact('perbaikan', 'timbanganList'));
    }

    // Method untuk create modal
    public function create($timbangan_id = null)
    {
        // Timbangan yang RUSAK atau DALAM PERBAIKAN dan masih di LINE
        $timbangan = Timbangan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
                            ->whereNotNull('status_line')
                            ->orderBy('kode_asset')
                            ->get();
                            
        $lines = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();
        $selectedTimbangan = $timbangan_id ? Timbangan::find($timbangan_id) : null;

        return response()->json([
            'success' => true,
            'html' => view('perbaikan.partials.create-modal', compact('timbangan', 'lines', 'selectedTimbangan'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id' => 'required|exists:timbangan,id',
            'line_sebelumnya' => 'required|string',
            'deskripsi_keluhan' => 'required|string',
            'tanggal_masuk_lab' => 'required|date',
            'penggunaan_terakhir' => 'nullable|string'
        ]);

        $timbangan = Timbangan::findOrFail($request->timbangan_id);

        // Validasi: Pastikan timbangan memang perlu perbaikan
        if ($timbangan->kondisi_saat_ini === 'Baik') {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan dalam kondisi baik. Tidak perlu perbaikan.'
            ], 422);
        }

        // Validasi: Pastikan timbangan masih di line (belum dikembalikan)
        if ($timbangan->status_line === null) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan sudah dikembalikan ke lab. Gunakan menu Perbaikan untuk update status.'
            ], 422);
        }

        // Buat riwayat perbaikan
        RiwayatPerbaikan::create([
            'timbangan_id' => $request->timbangan_id,
            'line_sebelumnya' => $request->line_sebelumnya,
            'penggunaan_terakhir' => $request->penggunaan_terakhir,
            'deskripsi_keluhan' => $request->deskripsi_keluhan,
            'tanggal_masuk_lab' => $request->tanggal_masuk_lab,
            'status_perbaikan' => 'Masuk Lab'
        ]);

        // Update timbangan - Kembalikan ke Lab dan set status perbaikan
        $timbangan->update([
            'kondisi_saat_ini' => 'Dalam Perbaikan',
            'status_line' => null // Kembalikan ke Lab
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perbaikan timbangan ' . $timbangan->kode_asset . ' berhasil dicatat. Timbangan dikembalikan ke Lab.'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_perbaikan' => 'required|in:Masuk Lab,Dalam Perbaikan,Selesai,Dikirim Eksternal',
            'tindakan_perbaikan' => 'nullable|string',
            'perbaikan_eksternal' => 'nullable|string',
            'tanggal_selesai_perbaikan' => 'nullable|date',
            'line_tujuan' => 'nullable|string'
        ]);

        $riwayat = RiwayatPerbaikan::findOrFail($id);
        $timbangan = $riwayat->timbangan;

        // Validasi: Jika status sudah Selesai, tidak boleh diupdate lagi
        if ($riwayat->status_perbaikan === 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Perbaikan sudah selesai. Tidak dapat diupdate lagi.'
            ], 422);
        }

        $riwayat->update([
            'status_perbaikan' => $request->status_perbaikan,
            'tindakan_perbaikan' => $request->tindakan_perbaikan,
            'perbaikan_eksternal' => $request->perbaikan_eksternal,
            'tanggal_selesai_perbaikan' => $request->tanggal_selesai_perbaikan,
            'line_tujuan' => $request->line_tujuan
        ]);

        // Update timbangan berdasarkan status perbaikan
        if ($request->status_perbaikan == 'Selesai') {
            // PERBAIKAN SELESAI: Kembalikan ke Lab dalam kondisi BAIK
            $timbangan->update([
                'kondisi_saat_ini' => 'Baik',
                'status_line' => null // Kembali ke Lab, TIDAK langsung ke line_tujuan
            ]);
        } elseif ($request->status_perbaikan == 'Dikirim Eksternal') {
            // Timbangan dikirim eksternal, tetap di lab tapi status khusus
            $timbangan->update([
                'kondisi_saat_ini' => 'Dalam Perbaikan',
                'status_line' => null // Tetap di lab
            ]);
        } else {
            // Masih dalam perbaikan internal
            $timbangan->update([
                'kondisi_saat_ini' => 'Dalam Perbaikan',
                'status_line' => null // Tetap di lab
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status perbaikan berhasil diperbarui.'
        ]);
    }
}