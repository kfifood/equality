<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\Peralatan;
use Illuminate\Http\Request;

class LineController extends Controller
{
    public function index()
    {
        $lines = MasterLine::orderBy('nama_line')->get();
        return view('line.index', compact('lines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_line' => 'required|unique:master_line,kode_line',
            'nama_line' => 'required|string',
            'department' => 'required|string'
        ]);

        MasterLine::create($request->all());

        return redirect()->route('line.index')
            ->with('success', 'Line produksi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_line' => 'required|unique:master_line,kode_line,' . $id,
            'nama_line' => 'required|string',
            'department' => 'required|string'
        ]);

        $line = MasterLine::findOrFail($id);
        $line->update($request->all());

        return redirect()->route('line.index')
            ->with('success', 'Line produksi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $line = MasterLine::findOrFail($id);
        $line->delete();

        return redirect()->route('line.index')
            ->with('success', 'Line produksi berhasil dihapus.');
    }

    // TAMBAHAN: Method untuk melihat peralatan di line tertentu
    // NOTE: method ini sebelumnya bernama timbangan($id) — di-rename jadi
    // peralatan($id) supaya konsisten. Kalau ada route yang manggil
    // ->timbangan (mis. Route::get('/line/{id}/timbangan', [...])), route
    // itu juga perlu di-update supaya manggil ->peralatan.
    public function peralatan($id)
    {
        $line = MasterLine::findOrFail($id);

        // Ambil peralatan yang sedang digunakan di line ini (status_line = nama_line) dan kondisi Baik
        $peralatanDiLine = Peralatan::where('status_line', $line->nama_line)
            ->where('kondisi_saat_ini', 'Baik')
            ->orderBy('kode_asset')
            ->get();

        // Ambil peralatan yang lokasi aslinya di line ini (baik yang sedang di line ini maupun di tempat lain)
        $peralatanLokasiAsli = Peralatan::where('lokasi_asli', $line->nama_line)
            ->orderBy('kode_asset')
            ->get();

        return response()->json([
            'success' => true,
            // NOTE: view partial masih dipanggil dengan nama lama
            // 'line.partials.timbangan-modal' — kalau file blade-nya sudah
            // kamu rename jadi 'peralatan-modal', sesuaikan path di sini juga.
            'html' => view('line.partials.timbangan-modal', compact(
                'line',
                'peralatanDiLine',
                'peralatanLokasiAsli'
            ))->render()
        ]);
    }
}