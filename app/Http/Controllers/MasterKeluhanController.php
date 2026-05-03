<?php

namespace App\Http\Controllers;

use App\Models\MasterKeluhan;
use Illuminate\Http\Request;

class MasterKeluhanController extends Controller
{
    public function index()
    {
        $keluhan = MasterKeluhan::orderBy('nama_keluhan')->paginate(15);
        return view('master-keluhan.index', compact('keluhan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_keluhan' => 'required|string|max:255|unique:master_keluhan,nama_keluhan',
        ], [
            'nama_keluhan.unique' => 'Nama keluhan ini sudah ada.',
        ]);

        MasterKeluhan::create(['nama_keluhan' => $request->nama_keluhan]);

        return response()->json([
            'success' => true,
            'message' => 'Keluhan "' . $request->nama_keluhan . '" berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        $keluhan = MasterKeluhan::findOrFail($id);
        return response()->json(['success' => true, 'data' => $keluhan]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_keluhan' => 'required|string|max:255|unique:master_keluhan,nama_keluhan,' . $id,
        ], [
            'nama_keluhan.unique' => 'Nama keluhan ini sudah ada.',
        ]);

        $keluhan = MasterKeluhan::findOrFail($id);
        $keluhan->update(['nama_keluhan' => $request->nama_keluhan]);

        return response()->json([
            'success' => true,
            'message' => 'Keluhan berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $keluhan = MasterKeluhan::findOrFail($id);

        // Cek apakah masih dipakai di laporan kerusakan
        if ($keluhan->laporanKerusakan()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Keluhan tidak bisa dihapus karena masih digunakan di ' .
                             $keluhan->laporanKerusakan()->count() . ' laporan kerusakan.',
            ], 422);
        }

        $keluhan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keluhan berhasil dihapus.',
        ]);
    }
}