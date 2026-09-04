<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\MasterPic;
use App\Models\RiwayatPenggunaan;
use App\Models\Peralatan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PenggunaanController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // ── Base query riwayat (untuk filter) ──────────────────────────────
        $riwayatQuery = RiwayatPenggunaan::with([
            'peralatan',
            'laporanKerusakan', // untuk cek status tombol laporkan rusak
        ]);

        if ($request->filled('tanggal_dari')) {
            $riwayatQuery->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $riwayatQuery->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('line_tujuan')) {
            $riwayatQuery->where('line_tujuan', $request->line_tujuan);
        }
        if ($request->filled('kode_asset')) {
            $riwayatQuery->whereHas('peralatan', fn($q) =>
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%')
            );
        }
        if ($request->filled('kondisi')) {
            $riwayatQuery->whereHas('peralatan', fn($q) =>
                $q->where('kondisi_saat_ini', $request->kondisi)
            );
        }

        // ── Ambil daftar peralatan_id unik yang punya riwayat sesuai filter ──
        // (urut berdasarkan tanggal_pemakaian terbaru per peralatan)
        $idQuery = (clone $riwayatQuery)
            ->select('peralatan_id')
            ->selectRaw('MAX(tanggal_pemakaian) as terakhir_dipakai')
            ->groupBy('peralatan_id');

        $sortBy  = $request->get('sort_by');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc']) ? $request->get('sort_dir') : 'desc';

        if ($sortBy === 'kode_asset' || $sortBy === 'merk_tipe') {
            $kolom = $sortBy === 'kode_asset' ? 'kode_asset' : 'merk';
            $idQuery->join('peralatan', 'riwayat_penggunaan.peralatan_id', '=', 'peralatan.id')
                    ->orderBy('peralatan.' . $kolom, $sortDir)
                    ->addSelect('peralatan.' . $kolom);
        } else {
            // default & 'tanggal_pemakaian': urutkan berdasarkan pemakaian terakhir
            $idQuery->orderBy('terakhir_dipakai', $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage     = 10;
        $page        = $request->get('page', 1);
        $allGrouped  = $idQuery->get();
        $total       = $allGrouped->count();

        $pagedIds = $allGrouped
            ->slice(($page - 1) * $perPage, $perPage)
            ->pluck('peralatan_id')
            ->values();

        // ── Ambil seluruh riwayat (sesuai filter) untuk peralatan-peralatan di halaman ini ──
        $riwayatPerPeralatan = collect();
        if ($pagedIds->isNotEmpty()) {
            $riwayatPerPeralatan = (clone $riwayatQuery)
                ->whereIn('peralatan_id', $pagedIds)
                ->orderBy('tanggal_pemakaian', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('peralatan_id');
        }

        // ── Susun data per peralatan, urut sesuai $pagedIds ──────────────────
        $groupedData = $pagedIds->map(function ($peralatanId) use ($riwayatPerPeralatan) {
            $riwayatList = $riwayatPerPeralatan->get($peralatanId, collect());
            return [
                'peralatan' => $riwayatList->first()?->peralatan ?? Peralatan::find($peralatanId),
                'riwayat'   => $riwayatList,
                'jumlah'    => $riwayatList->count(),
            ];
        })->filter(fn($d) => $d['peralatan'] !== null)->values();

        // ── Bungkus sebagai paginator agar bisa pakai komponen pagination ───
        $penggunaan = new LengthAwarePaginator(
            $groupedData,
            $total,
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        $peralatanList = Peralatan::where('kondisi_saat_ini', 'Baik')
                                  ->orderBy('kode_asset')
                                  ->get();

        $lineList = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();

        return view('penggunaan.index', compact('penggunaan', 'peralatanList', 'lineList'));
    }

    // ── CREATE MODAL (AJAX) ───────────────────────────────────────────────────

    public function create($peralatan_id = null)
    {
        $peralatan = Peralatan::where('kondisi_saat_ini', 'Baik')
                              ->orderBy('kode_asset')
                              ->get();

        $lines = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();

        $picList = MasterPic::with('line')->aktif()->orderBy('nama_pic')->get();

        $selectedPeralatan = $peralatan_id ? Peralatan::find($peralatan_id) : null;

        return response()->json([
            'success' => true,
            'html'    => view(
                'penggunaan.partials.create-modal',
                compact('peralatan', 'lines', 'picList', 'selectedPeralatan')
            )->render(),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'peralatan_id'      => 'required|exists:peralatan,id',
            'line_tujuan'       => 'required|string',
            'tanggal_pemakaian' => 'required|date',
            'pic'               => 'required|string|max:255',
            'keterangan'        => 'nullable|string',
        ]);

        $peralatan = Peralatan::findOrFail($request->peralatan_id);

        if ($peralatan->kondisi_saat_ini !== 'Baik') {
            return response()->json([
                'success' => false,
                'message' => 'Peralatan tidak dalam kondisi baik. Tidak bisa digunakan.',
            ], 422);
        }

        $lineSebelumnya = $peralatan->status_line;

        RiwayatPenggunaan::create([
            'peralatan_id'      => $request->peralatan_id,
            'line_tujuan'       => $request->line_tujuan,
            'tanggal_pemakaian' => $request->tanggal_pemakaian,
            'pic'               => $request->pic,
            'keterangan'        => $request->keterangan,
        ]);

        $peralatan->update(['status_line' => $request->line_tujuan]);

        $message  = 'Penggunaan peralatan ' . $peralatan->kode_asset . ' berhasil dicatat. ';
        $message .= 'Peralatan dipindahkan dari '
                  . ($lineSebelumnya ?? 'Lab')
                  . ' ke ' . $request->line_tujuan;

        return response()->json(['success' => true, 'message' => $message]);
    }

    // ── LAPORKAN RUSAK — ambil data penggunaan untuk auto-fill modal ──────────

    /**
     * Mengembalikan data penggunaan aktif untuk keperluan auto-fill modal laporan rusak.
     * Dipanggil via AJAX ketika tombol "Laporkan Rusak" diklik.
     *
     * Catatan: HTML modal sebenarnya di-render oleh LaporanKerusakanController@create,
     * method ini hanya dipakai jika perlu data JSON tambahan.
     */
    public function getPenggunaanUntukLaporan($id)
    {
        try {
            $penggunaan = RiwayatPenggunaan::with('peralatan')->findOrFail($id);
            $peralatan  = $penggunaan->peralatan;

            return response()->json([
                'success' => true,
                'data'    => [
                    'penggunaan_id'   => $penggunaan->id,
                    'peralatan_id'    => $peralatan->id,
                    'kode_asset'      => $peralatan->kode_asset,
                    'merk_tipe'       => $peralatan->merk_tipe_lengkap,
                    'line_asal'       => $penggunaan->line_tujuan,
                    'pic_pelapor'     => $penggunaan->pic,
                    'kondisi'         => $peralatan->kondisi_saat_ini,
                    'bisa_dilaporkan' => $peralatan->bisaDilaporkanRusak(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }
    }
}