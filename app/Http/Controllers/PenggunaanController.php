<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\MasterPic;
use App\Models\RiwayatPenggunaan;
use App\Models\Timbangan;
use Illuminate\Http\Request;

class PenggunaanController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = RiwayatPenggunaan::with([
            'timbangan',
            'laporanKerusakan', // BARU — untuk cek status tombol laporkan rusak
        ]);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('line_tujuan')) {
            $query->where('line_tujuan', $request->line_tujuan);
        }
        if ($request->filled('kode_asset')) {
            $query->whereHas('timbangan', fn($q) =>
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%')
            );
        }
        if ($request->filled('kondisi')) {
            $query->whereHas('timbangan', fn($q) =>
                $q->where('kondisi_saat_ini', $request->kondisi)
            );
        }

        $penggunaan = $query->orderBy('tanggal_pemakaian', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        $timbanganList = Timbangan::where('kondisi_saat_ini', 'Baik')
                                  ->orderBy('kode_asset')
                                  ->get();

        $lineList = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();

        return view('penggunaan.index', compact('penggunaan', 'timbanganList', 'lineList'));
    }

    // ── CREATE MODAL (AJAX) ───────────────────────────────────────────────────

    public function create($timbangan_id = null)
    {
        $timbangan = Timbangan::where('kondisi_saat_ini', 'Baik')
                              ->orderBy('kode_asset')
                              ->get();

        $lines = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();

        $picList = MasterPic::with('line')->aktif()->orderBy('nama_pic')->get();

        $selectedTimbangan = $timbangan_id ? Timbangan::find($timbangan_id) : null;

        return response()->json([
            'success' => true,
            'html'    => view(
                'penggunaan.partials.create-modal',
                compact('timbangan', 'lines', 'picList', 'selectedTimbangan')
            )->render(),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id'      => 'required|exists:timbangan,id',
            'line_tujuan'       => 'required|string',
            'tanggal_pemakaian' => 'required|date',
            'pic'               => 'nullable|string|max:255',
            'keterangan'        => 'nullable|string',
        ]);

        $timbangan = Timbangan::findOrFail($request->timbangan_id);

        if ($timbangan->kondisi_saat_ini !== 'Baik') {
            return response()->json([
                'success' => false,
                'message' => 'Timbangan tidak dalam kondisi baik. Tidak bisa digunakan.',
            ], 422);
        }

        $lineSebelumnya = $timbangan->status_line;

        RiwayatPenggunaan::create([
            'timbangan_id'      => $request->timbangan_id,
            'line_tujuan'       => $request->line_tujuan,
            'tanggal_pemakaian' => $request->tanggal_pemakaian,
            'pic'               => $request->pic,
            'keterangan'        => $request->keterangan,
        ]);

        $timbangan->update(['status_line' => $request->line_tujuan]);

        $message  = 'Penggunaan timbangan ' . $timbangan->kode_asset . ' berhasil dicatat. ';
        $message .= 'Timbangan dipindahkan dari '
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
            $penggunaan = RiwayatPenggunaan::with('timbangan')->findOrFail($id);
            $timbangan  = $penggunaan->timbangan;

            return response()->json([
                'success' => true,
                'data'    => [
                    'penggunaan_id'  => $penggunaan->id,
                    'timbangan_id'   => $timbangan->id,
                    'kode_asset'     => $timbangan->kode_asset,
                    'merk_tipe'      => $timbangan->merk_tipe_no_seri,
                    'line_asal'      => $penggunaan->line_tujuan,
                    'pic_pelapor'    => $penggunaan->pic,
                    'kondisi'        => $timbangan->kondisi_saat_ini,
                    'bisa_dilaporkan'=> $timbangan->bisaDilaporkanRusak(),
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