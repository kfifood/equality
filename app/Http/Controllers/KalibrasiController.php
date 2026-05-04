<?php

namespace App\Http\Controllers;

use App\Models\Kalibrasi;
use App\Models\Timbangan;
use Illuminate\Http\Request;

class KalibrasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Kalibrasi::with('timbangan');

        if ($request->filled('timbangan_id')) {
            $query->where('timbangan_id', $request->timbangan_id);
        }

        if ($request->filled('hasil')) {
            $query->where('hasil', $request->hasil);
        }

        if ($request->filled('dept_bagian')) {
            $query->where('dept_bagian', $request->dept_bagian);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', '%' . $search . '%')
                  ->orWhere('pelaksana', 'like', '%' . $search . '%')
                  ->orWhere('dept_bagian', 'like', '%' . $search . '%')
                  ->orWhereHas('timbangan', function ($q2) use ($search) {
                      $q2->where('kode_asset', 'like', '%' . $search . '%')
                         ->orWhere('merk_tipe_no_seri', 'like', '%' . $search . '%');
                  });
            });
        }

        $perPage   = $request->get('per_page', 10);
        $kalibrasi = $query->orderBy('tanggal_pelaksanaan', 'desc')
                           ->paginate($perPage)
                           ->withQueryString();

        $timbanganList = Timbangan::orderBy('kode_asset')->get(['id', 'kode_asset', 'merk_tipe_no_seri']);
        $deptList      = Kalibrasi::whereNotNull('dept_bagian')
                                   ->distinct()
                                   ->orderBy('dept_bagian')
                                   ->pluck('dept_bagian');

        return view('kalibrasi.index', compact('kalibrasi', 'timbanganList', 'deptList'));
    }

    // ── CREATE ───────────────────────────────────────────────────────────────

    public function create()
    {
        $timbanganList = Timbangan::orderBy('kode_asset')
                                  ->get(['id', 'kode_asset', 'merk_tipe_no_seri', 'certificate_number']);

        return response()->json([
            'success' => true,
            'html'    => view('kalibrasi.partials.create-modal', compact('timbanganList'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id'        => 'required|exists:timbangan,id',
            'tanggal_pelaksanaan' => 'required|date',
            'certificate_number'  => 'nullable|string|max:255',
            'dept_bagian'         => 'nullable|string|max:255',
            'beda_maksimum'       => 'nullable|string|max:100',
            'hasil'               => 'nullable|in:Lulus,Tidak Lulus',
            'pelaksana'           => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
        ], [
            'timbangan_id.required'        => 'Timbangan harus dipilih.',
            'timbangan_id.exists'          => 'Timbangan tidak ditemukan.',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan harus diisi.',
        ]);

        Kalibrasi::create([
            'timbangan_id'        => $request->timbangan_id,
            'certificate_number'  => $request->certificate_number,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'dept_bagian'         => $request->dept_bagian,
            'beda_maksimum'       => $request->beda_maksimum,
            'hasil'               => $request->hasil,
            'pelaksana'           => $request->pelaksana,
            'catatan'             => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kalibrasi berhasil ditambahkan.',
        ]);
    }

    // ── EDIT ─────────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $kalibrasi     = Kalibrasi::with('timbangan')->findOrFail($id);
        $timbanganList = Timbangan::orderBy('kode_asset')
                                  ->get(['id', 'kode_asset', 'merk_tipe_no_seri', 'certificate_number']);

        return response()->json([
            'success' => true,
            'html'    => view('kalibrasi.partials.edit-modal', compact('kalibrasi', 'timbanganList'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $kalibrasi = Kalibrasi::findOrFail($id);

        $request->validate([
            'timbangan_id'        => 'required|exists:timbangan,id',
            'tanggal_pelaksanaan' => 'required|date',
            'certificate_number'  => 'nullable|string|max:255',
            'dept_bagian'         => 'nullable|string|max:255',
            'beda_maksimum'       => 'nullable|string|max:100',
            'hasil'               => 'nullable|in:Lulus,Tidak Lulus',
            'pelaksana'           => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
        ], [
            'timbangan_id.required'        => 'Timbangan harus dipilih.',
            'timbangan_id.exists'          => 'Timbangan tidak ditemukan.',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan harus diisi.',
        ]);

        $kalibrasi->update([
            'timbangan_id'        => $request->timbangan_id,
            'certificate_number'  => $request->certificate_number,
            'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
            'dept_bagian'         => $request->dept_bagian,
            'beda_maksimum'       => $request->beda_maksimum,
            'hasil'               => $request->hasil,
            'pelaksana'           => $request->pelaksana,
            'catatan'             => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kalibrasi berhasil diperbarui.',
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        $kalibrasi = Kalibrasi::findOrFail($id);
        $kalibrasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data kalibrasi berhasil dihapus.',
        ]);
    }

    // ── STICKER ───────────────────────────────────────────────────────────────

    /**
     * Cetak sticker satu record kalibrasi.
     *
     * Route: GET /kalibrasi/{id}/sticker
     * Name:  kalibrasi.sticker
     */
    public function sticker($id)
    {
        $item = Kalibrasi::with('timbangan')->findOrFail($id);

        $kalibrasiList = collect([$item]);

        return view('kalibrasi.sticker', compact('kalibrasiList'));
    }

    /**
     * Cetak sticker batch (banyak record sekaligus).
     *
     * Route: POST /kalibrasi/sticker-batch
     * Name:  kalibrasi.sticker.batch
     *
     * Body: ids[] — array of kalibrasi IDs
     */
    public function stickerBatch(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:kalibrasi,id',
        ], [
            'ids.required' => 'Pilih minimal satu data kalibrasi.',
            'ids.min'      => 'Pilih minimal satu data kalibrasi.',
        ]);

        $kalibrasiList = Kalibrasi::with('timbangan')
                                   ->whereIn('id', $request->ids)
                                   ->orderBy('tanggal_pelaksanaan', 'desc')
                                   ->get();

        return view('kalibrasi.sticker', compact('kalibrasiList'));
    }
}