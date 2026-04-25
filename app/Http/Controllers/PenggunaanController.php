<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\MasterPic;
use App\Models\RiwayatPenggunaan;
use App\Models\Timbangan;
use Illuminate\Http\Request;

class PenggunaanController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatPenggunaan::with(['timbangan']);

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
            $query->whereHas('timbangan', function ($q) use ($request) {
                $q->where('kode_asset', 'like', '%' . $request->kode_asset . '%');
            });
        }
        if ($request->filled('kondisi')) {
            $query->whereHas('timbangan', function ($q) use ($request) {
                $q->where('kondisi_saat_ini', $request->kondisi);
            });
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

    // ── Create modal (AJAX) ───────────────────────────────────────────────────
    public function create($timbangan_id = null)
    {
        $timbangan = Timbangan::where('kondisi_saat_ini', 'Baik')
                              ->orderBy('kode_asset')
                              ->get();

        $lines = MasterLine::where('status_aktif', true)->orderBy('nama_line')->get();

        // Ambil semua PIC aktif beserta relasi line-nya
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

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id'     => 'required|exists:timbangan,id',
            'line_tujuan'      => 'required|string',
            'tanggal_pemakaian'=> 'required|date',
            'pic'              => 'nullable|string|max:255',
            'keterangan'       => 'nullable|string',
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
            'timbangan_id'     => $request->timbangan_id,
            'line_tujuan'      => $request->line_tujuan,
            'tanggal_pemakaian'=> $request->tanggal_pemakaian,
            'pic'              => $request->pic,
            'keterangan'       => $request->keterangan,
        ]);

        $timbangan->update(['status_line' => $request->line_tujuan]);

        $message = 'Penggunaan timbangan ' . $timbangan->kode_asset . ' berhasil dicatat. ';
        $message .= 'Timbangan dipindahkan dari '
                  . ($lineSebelumnya ?? 'Lab')
                  . ' ke ' . $request->line_tujuan;

        return response()->json(['success' => true, 'message' => $message]);
    }
}