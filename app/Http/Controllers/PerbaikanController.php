<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use App\Models\Timbangan;
use Illuminate\Http\Request;

class PerbaikanController extends Controller
{
    // ──────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = RiwayatPerbaikan::with(['timbangan']);

        if ($request->filled('status')) {
            $query->where('status_perbaikan', $request->status);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_masuk_lab', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_masuk_lab', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('kode_asset')) {
            $query->whereHas('timbangan', function ($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
        }

        $perbaikan = $query->orderBy('created_at', 'desc')->paginate(10);

        // Timbangan Rusak / Dalam Perbaikan yang masih ada di Line (belum dikembalikan ke lab)
        $timbanganList = Timbangan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
            ->whereNotNull('status_line')
            ->orderBy('kode_asset')
            ->get();

        return view('perbaikan.index', compact('perbaikan', 'timbanganList'));
    }

    // ──────────────────────────────────────────────
    // CREATE MODAL
    // ──────────────────────────────────────────────

    public function create($timbangan_id = null)
    {
        $timbangan = Timbangan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
            ->whereNotNull('status_line')
            ->orderBy('kode_asset')
            ->get();

        $lines           = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();
        $selectedTimbangan = $timbangan_id ? Timbangan::find($timbangan_id) : null;

        return response()->json([
            'success' => true,
            'html'    => view('perbaikan.partials.create-modal', compact('timbangan', 'lines', 'selectedTimbangan'))->render()
        ]);
    }

    // ──────────────────────────────────────────────
    // GET TIMBANGAN DATA (AJAX)
    // ──────────────────────────────────────────────

    public function getTimbanganData($id)
    {
        try {
            $timbangan = Timbangan::with(['riwayatPenggunaan' => function ($query) {
                $query->whereIn('status_penggunaan', ['Masih Digunakan', 'Selesai'])
                      ->orderBy('tanggal_pemakaian', 'desc')
                      ->limit(1);
            }])->findOrFail($id);

            $penggunaanTerakhir = $timbangan->riwayatPenggunaan->first();
            $pic = $penggunaanTerakhir ? $penggunaanTerakhir->pic : '-';

            return response()->json([
                'success' => true,
                'data'    => [
                    'line_sebelumnya'    => $timbangan->status_line ?: 'Lab',
                    'penggunaan_terakhir'=> $pic,
                    'kondisi'            => $timbangan->kondisi_saat_ini,
                    'lokasi_sekarang'    => $timbangan->status_line ?: 'Lab',
                    'kode_asset'         => $timbangan->kode_asset,
                    'merk_tipe'          => $timbangan->merk_tipe_no_seri,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getTimbanganData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data timbangan tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }

    // ──────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id'        => 'required|exists:timbangan,id',
            'line_sebelumnya'     => 'required|string',
            'deskripsi_keluhan'   => 'required|string',
            'tanggal_masuk_lab'   => 'required|date',
            'penggunaan_terakhir' => 'nullable|string',
        ]);

        $timbangan = Timbangan::findOrFail($request->timbangan_id);

        if ($timbangan->kondisi_saat_ini === 'Baik') {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan dalam kondisi baik. Tidak perlu perbaikan.',
            ], 422);
        }

        if ($timbangan->status_line === null) {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan sudah dikembalikan ke lab. Gunakan menu Perbaikan untuk update status.',
            ], 422);
        }

        RiwayatPerbaikan::create([
            'timbangan_id'        => $request->timbangan_id,
            'line_sebelumnya'     => $request->line_sebelumnya,
            'penggunaan_terakhir' => $request->penggunaan_terakhir,
            'deskripsi_keluhan'   => $request->deskripsi_keluhan,
            'tanggal_masuk_lab'   => $request->tanggal_masuk_lab,
            'status_perbaikan'    => 'Masuk Lab',
        ]);

        // Kembalikan timbangan ke Lab & set kondisi Dalam Perbaikan
        $timbangan->update([
            'kondisi_saat_ini' => 'Dalam Perbaikan',
            'status_line'      => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perbaikan timbangan ' . $timbangan->kode_asset . ' berhasil dicatat. Timbangan dikembalikan ke Lab.',
        ]);
    }

    // ──────────────────────────────────────────────
    // UPDATE STATUS
    // ──────────────────────────────────────────────

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_perbaikan'          => 'required|in:Masuk Lab,Perbaikan Internal,Dikirim Eksternal,Selesai',
            'tindakan_perbaikan'        => 'nullable|string',
            'perbaikan_eksternal'       => 'nullable|string',
            'tanggal_selesai_perbaikan' => 'nullable|date',

            // Wajib diisi hanya ketika status = Selesai
            'line_tujuan'               => 'nullable|string',
        ]);

        // Validasi tambahan: jika Selesai, line_tujuan harus diisi
        if ($request->status_perbaikan === 'Selesai' && empty($request->line_tujuan)) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tujuan harus dipilih ketika perbaikan selesai.',
            ], 422);
        }

        $riwayat   = RiwayatPerbaikan::findOrFail($id);
        $timbangan = $riwayat->timbangan;

        if ($riwayat->status_perbaikan === 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Perbaikan sudah selesai. Tidak dapat diupdate lagi.',
            ], 422);
        }

        $riwayat->update([
            'status_perbaikan'          => $request->status_perbaikan,
            'tindakan_perbaikan'        => $request->tindakan_perbaikan,
            'perbaikan_eksternal'       => $request->perbaikan_eksternal,
            'tanggal_selesai_perbaikan' => $request->tanggal_selesai_perbaikan,
            'line_tujuan'               => $request->line_tujuan,
        ]);

        // ── Update timbangan berdasarkan status baru ──────────────────
        switch ($request->status_perbaikan) {

            case 'Selesai':
                // line_tujuan = 'Lab' → status_line null; selainnya → isi status_line
                $statusLine = ($request->line_tujuan === 'Lab') ? null : $request->line_tujuan;

                $timbangan->update([
                    'kondisi_saat_ini'          => 'Baik',
                    'status_line'               => $statusLine,
                    'tanggal_selesai_perbaikan' => $request->tanggal_selesai_perbaikan,
                ]);

                // Jika dikirim ke Line (bukan Lab), buat riwayat penggunaan otomatis
                if ($statusLine !== null) {
                    RiwayatPenggunaan::create([
                        'timbangan_id'      => $timbangan->id,
                        'line_tujuan'       => $statusLine,
                        'tanggal_pemakaian' => $request->tanggal_selesai_perbaikan ?? now()->toDateString(),
                        'pic'               => 'Teknik (selesai perbaikan)',
                        'keterangan'        => 'Dikembalikan ke ' . $statusLine . ' setelah perbaikan selesai.',
                    ]);
                }
                break;

            case 'Dikirim Eksternal':
                // Timbangan dikirim ke vendor luar; tetap di Lab, kondisi Dalam Perbaikan
                $timbangan->update([
                    'kondisi_saat_ini'          => 'Dalam Perbaikan',
                    'status_line'               => null,
                    'tanggal_selesai_perbaikan' => null,
                ]);
                break;

            case 'Perbaikan Internal':
                // Timbangan sedang diperbaiki oleh Teknik internal; tetap di Lab
                $timbangan->update([
                    'kondisi_saat_ini'          => 'Dalam Perbaikan',
                    'status_line'               => null,
                    'tanggal_selesai_perbaikan' => null,
                ]);
                break;

            default: // 'Masuk Lab'
                $timbangan->update([
                    'kondisi_saat_ini'          => 'Dalam Perbaikan',
                    'status_line'               => null,
                    'tanggal_selesai_perbaikan' => null,
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Status perbaikan berhasil diperbarui.',
        ]);
    }
}