<?php

namespace App\Http\Controllers;

use App\Models\MasterTindakan;
use Illuminate\Http\Request;

class MasterTindakanController extends Controller
{
    public function index()
    {
        $tindakan = MasterTindakan::orderBy('nama_tindakan')->paginate(15);
        return view('master-tindakan.index', compact('tindakan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tindakan' => 'required|string|max:255|unique:master_tindakan,nama_tindakan',
        ], [
            'nama_tindakan.unique' => 'Nama tindakan ini sudah ada.',
        ]);

        MasterTindakan::create(['nama_tindakan' => $request->nama_tindakan]);

        return response()->json([
            'success' => true,
            'message' => 'Tindakan "' . $request->nama_tindakan . '" berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        $tindakan = MasterTindakan::findOrFail($id);
        return response()->json(['success' => true, 'data' => $tindakan]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_tindakan' => 'required|string|max:255|unique:master_tindakan,nama_tindakan,' . $id,
        ], [
            'nama_tindakan.unique' => 'Nama tindakan ini sudah ada.',
        ]);

        $tindakan = MasterTindakan::findOrFail($id);
        $tindakan->update(['nama_tindakan' => $request->nama_tindakan]);

        return response()->json([
            'success' => true,
            'message' => 'Tindakan berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $tindakan = MasterTindakan::findOrFail($id);

        // Cek apakah masih dipakai di detail perbaikan
        if ($tindakan->detailTindakan()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tindakan tidak bisa dihapus karena sudah digunakan di ' .
                             $tindakan->detailTindakan()->count() . ' catatan perbaikan.',
            ], 422);
        }

        $tindakan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tindakan berhasil dihapus.',
        ]);
    }
}