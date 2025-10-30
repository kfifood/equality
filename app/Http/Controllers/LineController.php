<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
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
}