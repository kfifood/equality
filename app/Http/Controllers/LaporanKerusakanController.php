<?php

namespace App\Http\Controllers;

use App\Models\LaporanKerusakan;
use App\Models\MasterKeluhan;
use App\Models\RiwayatPenggunaan;
use App\Models\Timbangan;
use Illuminate\Http\Request;

class LaporanKerusakanController extends Controller
{
    // ── Buka modal form laporan rusak (dipanggil dari tombol di tabel penggunaan) ──

    public function create($penggunaan_id)
    {
        $penggunaan = RiwayatPenggunaan::with('timbangan')->findOrFail($penggunaan_id);
        $timbangan  = $penggunaan->timbangan;

        // Validasi: hanya timbangan yang sedang digunakan di line yang bisa dilaporkan
        if (!$timbangan->bisaDilaporkanRusak()) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan ini tidak bisa dilaporkan rusak. ' .
                             'Pastikan kondisi Baik dan sedang digunakan di Line.',
            ], 422);
        }

        // Cek apakah sudah ada laporan aktif
        if ($penggunaan->sudahDilaporkanRusak()) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan ini sudah memiliki laporan kerusakan yang sedang diproses.',
            ], 422);
        }

        $keluhanList = MasterKeluhan::orderBy('nama_keluhan')->get();

        return response()->json([
            'success' => true,
            'html'    => view('penggunaan.partials.laporkan-rusak-modal', compact(
                'penggunaan', 'timbangan', 'keluhanList'
            ))->render(),
        ]);
    }

    // ── Simpan laporan kerusakan baru ────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'penggunaan_id'       => 'required|exists:riwayat_penggunaan,id',
            'timbangan_id'        => 'required|exists:timbangan,id',
            'keluhan_ids'         => 'required|array|min:1',
            'keluhan_ids.*'       => 'exists:master_keluhan,id',
            'tanggal_laporan'     => 'required|date',
            'keterangan_tambahan' => 'nullable|string|max:500',
        ], [
            'keluhan_ids.required' => 'Pilih minimal satu keluhan.',
            'keluhan_ids.min'      => 'Pilih minimal satu keluhan.',
        ]);

        $penggunaan = RiwayatPenggunaan::with('timbangan')->findOrFail($request->penggunaan_id);
        $timbangan  = $penggunaan->timbangan;

        // Validasi ulang di server
        if (!$timbangan->bisaDilaporkanRusak()) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan tidak bisa dilaporkan rusak saat ini.',
            ], 422);
        }

        if ($penggunaan->sudahDilaporkanRusak()) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan ini sudah memiliki laporan kerusakan aktif.',
            ], 422);
        }

        // 1. Buat laporan kerusakan
        $laporan = LaporanKerusakan::create([
            'timbangan_id'          => $request->timbangan_id,
            'riwayat_penggunaan_id' => $request->penggunaan_id,
            'line_asal'             => $penggunaan->line_tujuan,
            'pic_pelapor'           => $penggunaan->pic,
            'tanggal_laporan'       => $request->tanggal_laporan,
            'keterangan_tambahan'   => $request->keterangan_tambahan,
            'status'                => 'Menunggu',
        ]);

        // 2. Simpan keluhan-keluhan yang dipilih (pivot)
        $laporan->keluhanList()->attach($request->keluhan_ids);

        // 3. Update kondisi timbangan → Rusak (tetap di line yang sama)
        $timbangan->update(['kondisi_saat_ini' => 'Rusak']);

        $namaKeluhan = MasterKeluhan::whereIn('id', $request->keluhan_ids)
                                    ->pluck('nama_keluhan')
                                    ->join(', ');

        return response()->json([
            'success' => true,
            'message' => 'Laporan kerusakan ' . $timbangan->kode_asset . ' berhasil dicatat. ' .
                         'Keluhan: ' . $namaKeluhan . '. ' .
                         'Silakan proses di menu Perbaikan Alat.',
        ]);
    }
}