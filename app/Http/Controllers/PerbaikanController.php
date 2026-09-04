<?php

namespace App\Http\Controllers;

use App\Models\DetailTindakanPerbaikan;
use App\Models\Kalibrasi;
use App\Models\LaporanKerusakan;
use App\Models\MasterLine;
use App\Models\MasterPic;
use App\Models\MasterTindakan;
use App\Models\RiwayatPenggunaan;
use App\Models\RiwayatPerbaikan;
use Illuminate\Http\Request;

class PerbaikanController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Tabel utama sekarang adalah laporan_kerusakan
        $query = LaporanKerusakan::with([
            'peralatan',
            'keluhanList',
            'riwayatPerbaikan' => fn($q) => $q->latest(),
        ]);

        // Filter status laporan
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter tanggal laporan
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_laporan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_laporan', '<=', $request->tanggal_sampai);
        }

        // Filter kode asset
        if ($request->filled('kode_asset')) {
            $query->whereHas('peralatan', fn($q) =>
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%')
            );
        }

        $laporanList = $query->orderBy('created_at', 'desc')->paginate(10);

        // Data untuk filter dropdown
        $statusOptions = ['Menunggu', 'Diproses', 'Selesai'];

        return view('perbaikan.index', compact('laporanList', 'statusOptions'));
    }

    // ── BUKA MODAL PROSES PERBAIKAN ───────────────────────────────────────────

    public function prosesModal($laporan_id)
    {
        $laporan = LaporanKerusakan::with([
            'peralatan',
            'keluhanList',
            'riwayatPenggunaan',
            'riwayatPerbaikan.detailTindakan.masterTindakan',
        ])->findOrFail($laporan_id);

        if ($laporan->isSelesai()) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan ini sudah selesai dan tidak bisa diproses lagi.',
            ], 422);
        }

        $tindakanList = MasterTindakan::orderBy('nama_tindakan')->get();
        $lineList     = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();
        $picList      = MasterPic::with('line')->aktif()->orderBy('nama_pic')->get();

        // Ambil perbaikan terakhir jika ada (untuk pre-fill form)
        $perbaikanTerakhir = $laporan->riwayatPerbaikan->first();

        return response()->json([
            'success' => true,
            'html'    => view('perbaikan.partials.proses-modal', compact(
                'laporan', 'tindakanList', 'lineList', 'picList', 'perbaikanTerakhir'
            ))->render(),
        ]);
    }

    // ── SIMPAN / UPDATE PROSES PERBAIKAN ──────────────────────────────────────

    public function prosesStore(Request $request, $laporan_id)
    {
        $request->validate([
            'status_perbaikan'          => 'required|in:Menunggu Penanganan,Masuk Lab,Perbaikan Internal,Dikirim Eksternal,Selesai',
            'tanggal_masuk_lab'         => 'required|date',
            'tindakan_ids'              => 'nullable|array',
            'tindakan_ids.*'            => 'exists:master_tindakan,id',
            'tanggal_tindakan'          => 'nullable|date',
            'catatan_tindakan'          => 'nullable|string|max:500',
            'catatan'                   => 'nullable|string|max:500',
            'tanggal_selesai_perbaikan' => 'nullable|date',
            'line_tujuan'               => 'nullable|string',
            'pic_penerima'              => 'nullable|string|max:100',
        ]);

        // Wajib isi line_tujuan jika status Selesai
        if ($request->status_perbaikan === 'Selesai' && empty($request->line_tujuan)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih lokasi tujuan peralatan setelah perbaikan selesai.',
            ], 422);
        }

        // Wajib isi tanggal_selesai jika status Selesai
        if ($request->status_perbaikan === 'Selesai' && empty($request->tanggal_selesai_perbaikan)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal selesai perbaikan harus diisi.',
            ], 422);
        }

        // Wajib isi pic_penerima jika dikirim ke Line (bukan Lab)
        if ($request->status_perbaikan === 'Selesai'
            && $request->line_tujuan !== 'Lab'
            && !empty($request->line_tujuan)
            && empty($request->pic_penerima)) {
            return response()->json([
                'success' => false,
                'message' => 'PIC penerima harus diisi saat peralatan dikirim ke line.',
            ], 422);
        }

        $laporan   = LaporanKerusakan::with('peralatan')->findOrFail($laporan_id);
        $peralatan = $laporan->peralatan;

        if ($laporan->isSelesai()) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan ini sudah selesai.',
            ], 422);
        }

        // ── Cari atau buat RiwayatPerbaikan untuk laporan ini ─────────────────
        $perbaikan = RiwayatPerbaikan::firstOrNew(['laporan_kerusakan_id' => $laporan->id]);

        $perbaikan->fill([
            'laporan_kerusakan_id'      => $laporan->id,
            'peralatan_id'              => $peralatan->id,
            'line_sebelumnya'           => $laporan->line_asal,
            'tanggal_masuk_lab'         => $request->tanggal_masuk_lab,
            'status_perbaikan'          => $request->status_perbaikan,
            'catatan'                   => $request->catatan,
            'tanggal_selesai_perbaikan' => $request->tanggal_selesai_perbaikan,
            'line_tujuan'               => $request->line_tujuan,
            // Pertahankan field lama untuk kompatibilitas
            'penggunaan_terakhir'       => $laporan->pic_pelapor,
            'deskripsi_keluhan'         => $laporan->keluhan_ringkas,
        ]);
        $perbaikan->save();

        // ── Simpan tindakan-tindakan yang dipilih ─────────────────────────────
        if ($request->filled('tindakan_ids')) {
            $tanggalTindakan = $request->tanggal_tindakan ?? now()->toDateString();

            // Cari id master_tindakan "Kalibrasi ulang" by nama (bukan hardcode id,
            // supaya tidak rapuh kalau data master_tindakan diubah urutannya)
            $idTindakanKalibrasi = MasterTindakan::where('nama_tindakan', 'Kalibrasi ulang')->value('id');

            foreach ($request->tindakan_ids as $tindakanId) {
                // Hindari duplikat tindakan di hari yang sama
                DetailTindakanPerbaikan::firstOrCreate([
                    'riwayat_perbaikan_id' => $perbaikan->id,
                    'master_tindakan_id'   => $tindakanId,
                    'tanggal_tindakan'     => $tanggalTindakan,
                ], [
                    'catatan' => $request->catatan_tindakan,
                ]);

                // ── BARU: kalau tindakannya "Kalibrasi ulang", catat juga
                // sebagai data resmi di modul Kalibrasi, bukan cuma teks tindakan.
                // KalibrasiObserver otomatis menyinkronkan peralatan.certificate_number
                // begitu record ini tersimpan.
                if ($idTindakanKalibrasi && $tindakanId == $idTindakanKalibrasi) {
                    Kalibrasi::firstOrCreate([
                        'peralatan_id'        => $peralatan->id,
                        'tanggal_pelaksanaan' => $tanggalTindakan,
                    ], [
                        'dept_bagian' => $request->line_tujuan ?? $laporan->line_asal,
                        'hasil'       => 'Lulus',
                        'pelaksana'   => 'Lab Internal',
                        'catatan'     => 'Otomatis tercatat dari proses perbaikan #' . $perbaikan->id,
                    ]);
                }
            }
        }

        // ── Update status laporan & peralatan berdasarkan status perbaikan ────
        switch ($request->status_perbaikan) {

            case 'Selesai':
                $statusLine = ($request->line_tujuan === 'Lab') ? null : $request->line_tujuan;

                // Update laporan → Selesai
                $laporan->update(['status' => 'Selesai']);

                // Update peralatan
                $peralatan->update([
                    'kondisi_saat_ini'          => 'Baik',
                    'status_line'               => $statusLine,
                    'tanggal_selesai_perbaikan' => $request->tanggal_selesai_perbaikan,
                ]);

                // Jika dikirim ke Line, otomatis buat riwayat penggunaan baru
                if ($statusLine !== null) {
                    RiwayatPenggunaan::create([
                        'peralatan_id'      => $peralatan->id,
                        'line_tujuan'       => $statusLine,
                        'tanggal_pemakaian' => $request->tanggal_selesai_perbaikan ?? now()->toDateString(),
                        'pic'               => $request->pic_penerima ?? '-',
                        'keterangan'        => 'Dikembalikan ke ' . $statusLine . ' setelah perbaikan selesai.',
                    ]);
                }
                break;

            case 'Masuk Lab':
            case 'Perbaikan Internal':
            case 'Dikirim Eksternal':
                // Update laporan → Diproses
                $laporan->update(['status' => 'Diproses']);

                // Peralatan masuk lab, kondisi Dalam Perbaikan
                $peralatan->update([
                    'kondisi_saat_ini' => 'Dalam Perbaikan',
                    'status_line'      => null,
                ]);
                break;

            default: // Menunggu Penanganan
                $laporan->update(['status' => 'Diproses']);
                // Peralatan tetap di line dengan kondisi Rusak
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Status perbaikan ' . $peralatan->kode_asset . ' berhasil diperbarui ke "' .
                         $request->status_perbaikan . '".',
        ]);
    }

    // ── DETAIL LAPORAN (AJAX — untuk modal lihat detail) ─────────────────────

    public function detail($laporan_id)
    {
        try {
            $laporan = LaporanKerusakan::with([
                'peralatan',
                'keluhanList',
                'riwayatPenggunaan',
                'riwayatPerbaikan.detailTindakan.masterTindakan',
            ])->findOrFail($laporan_id);

            return response()->json([
                'success' => true,
                'html'    => view('perbaikan.partials.detail-modal', compact('laporan'))->render(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }

    // ── LEGACY: dipertahankan agar route lama tidak error ─────────────────────

    /**
     * @deprecated Gunakan prosesStore() untuk alur baru.
     */
    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Gunakan tombol "Laporkan Rusak" di menu Penggunaan Alat untuk mencatat kerusakan.',
        ], 422);
    }

    /**
     * @deprecated Gunakan prosesStore() untuk alur baru.
     */
    public function updateStatus(Request $request, $id)
    {
        // Redirect ke prosesStore jika masih ada yang memanggil
        $riwayat = RiwayatPerbaikan::findOrFail($id);

        if (!$riwayat->laporan_kerusakan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Data perbaikan lama. Silakan proses melalui menu Perbaikan Alat.',
            ], 422);
        }

        return $this->prosesStore($request, $riwayat->laporan_kerusakan_id);
    }
}