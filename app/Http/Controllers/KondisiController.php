<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use Illuminate\Http\Request;

class KondisiController extends Controller
{
    public function index()
    {
        $timbangan = Timbangan::orderBy('kondisi_saat_ini')->orderBy('kode_asset')->get();
        
        $statistik = [
            'total' => $timbangan->count(),
            'baik' => $timbangan->where('kondisi_saat_ini', 'Baik')->count(),
            'rusak' => $timbangan->where('kondisi_saat_ini', 'Rusak')->count(),
            'perbaikan' => $timbangan->where('kondisi_saat_ini', 'Dalam Perbaikan')->count()
        ];

        return view('kondisi.index', compact('timbangan', 'statistik'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kondisi_saat_ini' => 'required|in:Baik,Rusak,Dalam Perbaikan'
        ]);

        $timbangan = Timbangan::findOrFail($id);
        $timbangan->update([
            'kondisi_saat_ini' => $request->kondisi_saat_ini
        ]);

        return redirect()->route('kondisi.index')
            ->with('success', 'Kondisi timbangan berhasil diperbarui.');
    }
}