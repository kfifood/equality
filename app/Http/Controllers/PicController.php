<?php

namespace App\Http\Controllers;

use App\Models\MasterLine;
use App\Models\MasterPic;
use Illuminate\Http\Request;

class PicController extends Controller
{
    public function index()
    {
        $pics = MasterPic::with('line')->latest()->get();

        return view('pic.index', compact('pics'));
    }

    // ── Auto-generate kode PIC format: PIC-001, PIC-002, dst ─────────────────
    private function generateKodePic(): string
    {
        $last = MasterPic::where('kode_pic', 'like', 'PIC-%')
            ->orderByRaw('CAST(SUBSTRING(kode_pic, 5) AS UNSIGNED) DESC')
            ->value('kode_pic');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'PIC-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pic'    => 'required|string|max:255',
            'jabatan'     => 'nullable|string|max:100',
            'line_id'     => 'required|exists:master_line,id',
            'status_aktif'=> 'required|boolean',
        ], [
            'line_id.exists' => 'Line tidak valid.',
        ]);

        MasterPic::create([
            'kode_pic'    => $this->generateKodePic(),
            'nama_pic'    => $request->nama_pic,
            'jabatan'     => $request->jabatan,
            'line_id'     => $request->line_id,
            'status_aktif'=> $request->status_aktif,
        ]);

        return redirect()->route('pic.index')
                         ->with('success', 'PIC berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $pic = MasterPic::findOrFail($id);

        $request->validate([
            'nama_pic'    => 'required|string|max:255',
            'jabatan'     => 'nullable|string|max:100',
            'line_id'     => 'required|exists:master_line,id',
            'status_aktif'=> 'required|boolean',
        ], [
            'line_id.exists' => 'Line tidak valid.',
        ]);

        // kode_pic tidak diubah saat edit
        $pic->update([
            'nama_pic'    => $request->nama_pic,
            'jabatan'     => $request->jabatan,
            'line_id'     => $request->line_id,
            'status_aktif'=> $request->status_aktif,
        ]);

        return redirect()->route('pic.index')
                         ->with('success', 'PIC berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pic = MasterPic::findOrFail($id);
        $pic->delete();

        return redirect()->route('pic.index')
                         ->with('success', 'PIC berhasil dihapus.');
    }

    // Endpoint untuk dropdown AJAX (opsional, berguna di form penggunaan)
    public function listAktif()
    {
        $pics = MasterPic::with('line')->aktif()->orderBy('nama_pic')->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'nama_pic' => $p->nama_pic,
                'jabatan'  => $p->jabatan,
                'line'     => $p->line->nama_line ?? '-',
            ]);

        return response()->json($pics);
    }
}