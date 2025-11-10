<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use App\Models\MasterLine;
use App\Models\RiwayatPerbaikan;
use App\Models\RiwayatPenggunaan;
use Illuminate\Http\Request;
use App\Imports\TimbanganImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimbanganExport;

class TimbanganController extends Controller
{
    public function index(Request $request)
{
    $query = Timbangan::query();

    // Filter berdasarkan kondisi
    if ($request->has('kondisi') && $request->kondisi != '') {
        $query->where('kondisi_saat_ini', $request->kondisi);
    }
    // Filter berdasarkan lokasi asli
if ($request->has('lokasi_asli') && $request->lokasi_asli != '') {
    $query->where('lokasi_asli', $request->lokasi_asli);
}
    // Filter berdasarkan status line
    if ($request->has('status_line') && $request->status_line != '') {
        if ($request->status_line == 'Lab') {
            $query->whereNull('status_line');
        } else {
            $query->where('status_line', $request->status_line);
        }
    }

    // Search
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('kode_asset', 'like', '%' . $search . '%')
              ->orWhere('merk_tipe_no_seri', 'like', '%' . $search . '%')
              ->orWhere('lokasi_asli', 'like', '%' . $search . '%'); // TAMBAH INI
        });
    }

    $timbangan = $query->orderBy('kode_asset', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
    
    $lineList = MasterLine::where('status_aktif', true)
        ->orderBy('nama_line')
        ->pluck('nama_line');

    return view('timbangan.index', compact('timbangan', 'lineList'));
}

    // Method untuk create modal
public function create()
{
    $lineList = MasterLine::where('status_aktif', true)
        ->orderBy('nama_line')
        ->pluck('nama_line');
        
    return response()->json([
        'success' => true,
        'html' => view('timbangan.partials.create-modal', compact('lineList'))->render()
    ]);
}

    public function store(Request $request)
{
    $request->validate([
        'kode_asset' => 'required|unique:timbangan,kode_asset',
        'merk_tipe_no_seri' => 'required|string',
        'tanggal_datang' => 'required|date',
        'lokasi_asli' => 'required|string' // TAMBAH VALIDASI
    ], [
        'kode_asset.unique' => 'Kode Asset sudah ada.',
        'lokasi_asli.required' => 'Lokasi asli harus dipilih.'
    ]);

    Timbangan::create([
        'kode_asset' => $request->kode_asset,
        'merk_tipe_no_seri' => $request->merk_tipe_no_seri,
        'tanggal_datang' => $request->tanggal_datang,
        'lokasi_asli' => $request->lokasi_asli, // SIMPAN LOKASI ASLI
        'status_line' => null, // Default di Lab
        'kondisi_saat_ini' => 'Baik'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Timbangan berhasil ditambahkan.'
    ]);
}

    // Method untuk edit modal
public function edit($id)
{
    $timbangan = Timbangan::findOrFail($id);
    $lineList = MasterLine::where('status_aktif', true)
        ->orderBy('nama_line')
        ->pluck('nama_line');
        
    return response()->json([
        'success' => true,
        'html' => view('timbangan.partials.edit-modal', compact('timbangan', 'lineList'))->render()
    ]);
}

public function update(Request $request, $id)
{
    $request->validate([
        'kode_asset' => 'required|unique:timbangan,kode_asset,' . $id,
        'merk_tipe_no_seri' => 'required|string',
        'tanggal_datang' => 'required|date',
        'lokasi_asli' => 'required|string' // TAMBAH VALIDASI
    ], [
        'kode_asset.unique' => 'Kode Asset sudah ada.',
        'lokasi_asli.required' => 'Lokasi asli harus dipilih.'
    ]);

    $timbangan = Timbangan::findOrFail($id);
    $timbangan->update([
        'kode_asset' => $request->kode_asset,
        'merk_tipe_no_seri' => $request->merk_tipe_no_seri,
        'tanggal_datang' => $request->tanggal_datang,
        'lokasi_asli' => $request->lokasi_asli // UPDATE LOKASI ASLI
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Timbangan berhasil diperbarui.'
    ]);
}

    public function destroy($id)
    {
        $timbangan = Timbangan::findOrFail($id);
        $timbangan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Timbangan berhasil dihapus.'
        ]);
    }

    public function riwayat($id)
    {
        \Log::info('=== RIIWAYAT METHOD CALLED ===');
        \Log::info('ID: ' . $id);
        
        try {
            // Enable detailed error reporting
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            // Validasi ID
            if (!is_numeric($id) || $id <= 0) {
                \Log::error('Invalid ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'ID timbangan tidak valid: ' . $id
                ], 400);
            }

            // Cari timbangan dengan exception handling
            $timbangan = Timbangan::find($id);
            
            if (!$timbangan) {
                \Log::error('Timbangan not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Timbangan tidak ditemukan dengan ID: ' . $id
                ], 404);
            }

            \Log::info('Timbangan found: ' . $timbangan->kode_asset);

            // Debug relations
            \Log::info('Loading riwayatPerbaikan...');
            $riwayatPerbaikan = $timbangan->riwayatPerbaikan()->orderBy('created_at', 'desc')->get();
            \Log::info('Riwayat Perbaikan count: ' . $riwayatPerbaikan->count());
            
            \Log::info('Loading riwayatPenggunaan...');
            $riwayatPenggunaan = $timbangan->riwayatPenggunaan()->orderBy('created_at', 'desc')->get();
            \Log::info('Riwayat Penggunaan count: ' . $riwayatPenggunaan->count());

            // Render view dengan try-catch
            try {
                $html = view('timbangan.partials.riwayat-modal', compact('timbangan'))->render();
                \Log::info('View rendered successfully, length: ' . strlen($html));
                
                return response()->json([
                    'success' => true,
                    'html' => $html
                ]);
                
            } catch (\Exception $e) {
                \Log::error('View rendering error: ' . $e->getMessage());
                \Log::error('View file: ' . $e->getFile() . ':' . $e->getLine());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error rendering view: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Exception in riwayat method: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new TimbanganImport, $request->file('file'));
            return redirect()->route('timbangan.index')
                ->with('success', 'Data timbangan berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('timbangan.index')
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new TimbanganExport, 'timbangan-' . date('Y-m-d') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $filePath = resource_path('templates/template-import-timbangan.xlsx');
        
        if (!file_exists($filePath)) {
            return redirect()->route('timbangan.index')
                ->with('error', 'Template tidak ditemukan.');
        }

        return response()->download($filePath, 'template-import-timbangan.xlsx');
    }

    // Tambahkan method ini di TimbanganController
    public function updateKondisi($id, Request $request)
    {
        $request->validate([
            'kondisi_saat_ini' => 'required|in:Baik,Rusak,Dalam Perbaikan'
        ]);

        $timbangan = Timbangan::findOrFail($id);
        $kondisiSebelumnya = $timbangan->kondisi_saat_ini;
        
        $timbangan->update([
            'kondisi_saat_ini' => $request->kondisi_saat_ini
        ]);

        // Sinkronisasi ke riwayat penggunaan yang aktif
        if ($kondisiSebelumnya !== $request->kondisi_saat_ini) {
            app(PenggunaanController::class)->updateKondisi($id, $request->kondisi_saat_ini);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kondisi timbangan berhasil diperbarui.'
        ]);
    }

    // Method baru untuk menandai timbangan rusak
    public function tandaiRusak($id)
    {
        $timbangan = Timbangan::findOrFail($id);

        // Validasi: hanya timbangan yang Baik dan di Line yang bisa ditandai rusak
        if ($timbangan->kondisi_saat_ini !== 'Baik' || $timbangan->status_line === null) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya timbangan dengan kondisi Baik yang sedang digunakan di Line yang bisa ditandai rusak.'
            ], 422);
        }

        // Update kondisi menjadi Rusak (tetap di line yang sama)
        $timbangan->update([
            'kondisi_saat_ini' => 'Rusak'
            // status_line tetap sama (masih di line)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Timbangan berhasil ditandai rusak. Sekarang bisa dicatat di menu Perbaikan.'
        ]);
    }
}