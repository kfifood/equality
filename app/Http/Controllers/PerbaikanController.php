<?php

namespace App\Http\Controllers;

use App\Models\DetailTindakanPerbaikan;
use App\Models\LaporanKerusakan;
use App\Models\MasterLine;
use App\Models\MasterPic;
use App\Models\MasterTindakan;
use App\Models\RiwayatPenggunaan;
use App\Models\RiwayatPerbaikan;
use App\Models\Timbangan;
use Illuminate\Http\Request;

class PerbaikanController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Tabel utama sekarang adalah laporan_kerusakan
        $query = LaporanKerusakan::with([
            'timbangan',
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
            $query->whereHas('timbangan', fn($q) =>
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
            'timbangan',
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
                'message' => 'Pilih lokasi tujuan timbangan setelah perbaikan selesai.',
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
                'message' => 'PIC penerima harus diisi saat timbangan dikirim ke line.',
            ], 422);
        }

        $laporan   = LaporanKerusakan::with('timbangan')->findOrFail($laporan_id);
        $timbangan = $laporan->timbangan;

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
            'timbangan_id'              => $timbangan->id,
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

            foreach ($request->tindakan_ids as $tindakanId) {
                // Hindari duplikat tindakan di hari yang sama
                DetailTindakanPerbaikan::firstOrCreate([
                    'riwayat_perbaikan_id' => $perbaikan->id,
                    'master_tindakan_id'   => $tindakanId,
                    'tanggal_tindakan'     => $tanggalTindakan,
                ], [
                    'catatan' => $request->catatan_tindakan,
                ]);
            }
        }

        // ── Update status laporan & timbangan berdasarkan status perbaikan ────
        switch ($request->status_perbaikan) {

            case 'Selesai':
                $statusLine = ($request->line_tujuan === 'Lab') ? null : $request->line_tujuan;

                // Update laporan → Selesai
                $laporan->update(['status' => 'Selesai']);

                // Update timbangan
                $timbangan->update([
                    'kondisi_saat_ini'          => 'Baik',
                    'status_line'               => $statusLine,
                    'tanggal_selesai_perbaikan' => $request->tanggal_selesai_perbaikan,
                ]);

                // Jika dikirim ke Line, otomatis buat riwayat penggunaan baru
                if ($statusLine !== null) {
                    RiwayatPenggunaan::create([
                        'timbangan_id'      => $timbangan->id,
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

                // Timbangan masuk lab, kondisi Dalam Perbaikan
                $timbangan->update([
                    'kondisi_saat_ini' => 'Dalam Perbaikan',
                    'status_line'      => null,
                ]);
                break;

            default: // Menunggu Penanganan
                $laporan->update(['status' => 'Diproses']);
                // Timbangan tetap di line dengan kondisi Rusak
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Status perbaikan ' . $timbangan->kode_asset . ' berhasil diperbarui ke "' .
                         $request->status_perbaikan . '".',
        ]);
    }

    // ── DETAIL LAPORAN (AJAX — untuk modal lihat detail) ─────────────────────

    public function detail($laporan_id)
    {
        try {
            $laporan = LaporanKerusakan::with([
                'timbangan',
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

    // ── LEGACY: create & store lama (dipertahankan agar route lama tidak error) ─

    /**
     * @deprecated Gunakan prosesModal() dan prosesStore() untuk alur baru.
     *             Method ini dipertahankan untuk kompatibilitas jika ada route lama.
     */
    public function create($timbangan_id = null)
    {
        $timbangan = Timbangan::whereIn('kondisi_saat_ini', ['Rusak', 'Dalam Perbaikan'])
            ->whereNotNull('status_line')
            ->orderBy('kode_asset')
            ->get();

        $lines             = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();
        $selectedTimbangan = $timbangan_id ? Timbangan::find($timbangan_id) : null;

        return response()->json([
            'success' => true,
            'html'    => view('perbaikan.partials.create-modal', compact(
                'timbangan', 'lines', 'selectedTimbangan'
            ))->render(),
        ]);
    }

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

    // ── GET TIMBANGAN DATA (AJAX — dipertahankan untuk form lama) ────────────

    public function getTimbanganData($id)
    {
        try {
            $timbangan = Timbangan::with(['riwayatPenggunaan' => function ($query) {
                $query->orderBy('tanggal_pemakaian', 'desc')->limit(1);
            }])->findOrFail($id);

            $penggunaanTerakhir = $timbangan->riwayatPenggunaan->first();

            return response()->json([
                'success' => true,
                'data'    => [
                    'line_sebelumnya'     => $timbangan->status_line ?: 'Lab',
                    'penggunaan_terakhir' => $penggunaanTerakhir ? $penggunaanTerakhir->pic : '-',
                    'kondisi'             => $timbangan->kondisi_saat_ini,
                    'lokasi_sekarang'     => $timbangan->status_line ?: 'Lab',
                    'kode_asset'          => $timbangan->kode_asset,
                    'merk_tipe'           => $timbangan->merk_tipe_no_seri,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data timbangan tidak ditemukan.',
            ], 404);
        }
    }
}