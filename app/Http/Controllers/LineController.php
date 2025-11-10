<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\Timbangan;
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

    // TAMBAHAN: Method untuk melihat timbangan di line tertentu
    public function timbangan($id)
    {
        $line = MasterLine::findOrFail($id);
        
        // Ambil timbangan yang sedang digunakan di line ini (status_line = nama_line) dan kondisi Baik
        $timbanganDiLine = Timbangan::where('status_line', $line->nama_line)
            ->where('kondisi_saat_ini', 'Baik')
            ->orderBy('kode_asset')
            ->get();
            
        // Ambil timbangan yang lokasi aslinya di line ini (baik yang sedang di line ini maupun di tempat lain)
        $timbanganLokasiAsli = Timbangan::where('lokasi_asli', $line->nama_line)
            ->orderBy('kode_asset')
            ->get();

        return response()->json([
            'success' => true,
            'html' => view('line.partials.timbangan-modal', compact(
                'line', 
                'timbanganDiLine', 
                'timbanganLokasiAsli'
            ))->render()
        ]);
    }
}