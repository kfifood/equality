<?php

namespace App\Http\Controllers;

use App\Models\Peralatan;
use App\Models\MasterLine;
use App\Models\MasterKategoriAlat;
use Illuminate\Http\Request;
use App\Imports\PeralatanImport; // TODO: buat/rename dari App\Imports\TimbanganImport
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PeralatanExport; // TODO: buat/rename dari App\Exports\TimbanganExport

class PeralatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peralatan::with('kategoriAlat');

        // Filter berdasarkan kategori alat
        if ($request->filled('kategori_alat_id')) {
            $query->where('kategori_alat_id', $request->kategori_alat_id);
        }

        // Filter berdasarkan kondisi
        if ($request->filled('kondisi')) {
            $query->where('kondisi_saat_ini', $request->kondisi);
        }

        // Filter berdasarkan lokasi asli
        if ($request->filled('lokasi_asli')) {
            $query->where('lokasi_asli', $request->lokasi_asli);
        }

        // Filter berdasarkan status line
        if ($request->filled('status_line')) {
            if ($request->status_line == 'Lab') {
                $query->whereNull('status_line');
            } else {
                $query->where('status_line', $request->status_line);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_asset', 'like', '%' . $search . '%')
                  ->orWhere('merk', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%')
                  ->orWhere('lokasi_asli', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sortBy  = $request->get('sort_by');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc']) ? $request->get('sort_dir') : 'asc';

        $allowedSorts = [
            'kode_asset'     => 'kode_asset',
            'merk_tipe'      => 'merk',
            'tanggal_datang' => 'tanggal_datang',
        ];

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderBy('kode_asset', 'asc')->orderBy('created_at', 'desc');
        }

        // Pagination dengan fallback
        $perPage   = $request->get('per_page', 10);
        $peralatan = $query->paginate($perPage)->withQueryString();

        $lineList     = MasterLine::aktif()->orderBy('nama_line')->pluck('nama_line');
        $kategoriList = MasterKategoriAlat::aktif()->orderBy('nama_kategori')->get();

        return view('peralatan.index', compact('peralatan', 'lineList', 'kategoriList'));
    }

    // Method untuk create modal
    public function create()
    {
        $lineList     = MasterLine::aktif()->orderBy('nama_line')->pluck('nama_line');
        $kategoriList = MasterKategoriAlat::aktif()->orderBy('nama_kategori')->get();

        return response()->json([
            'success' => true,
            'html'    => view('peralatan.partials.create-modal', compact('lineList', 'kategoriList'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_alat_id'  => 'required|exists:master_kategori_alat,id',
            'kode_asset'        => 'required|unique:peralatan,kode_asset',
            'merk'              => 'required|string|max:255',
            'type'              => 'nullable|string|max:255',
            'serial_number'     => 'nullable|string|max:255',
            'tanggal_datang'    => 'required|date',
            'lokasi_asli'       => 'required|string',
        ], [
            'kategori_alat_id.required' => 'Kategori alat harus dipilih.',
            'kategori_alat_id.exists'   => 'Kategori alat tidak valid.',
            'kode_asset.unique'         => 'Kode Asset sudah ada.',
            'lokasi_asli.required'      => 'Lokasi asli harus dipilih.',
        ]);

        Peralatan::create([
            'kategori_alat_id'   => $request->kategori_alat_id,
            'kode_asset'         => $request->kode_asset,
            'merk'               => $request->merk,
            'type'               => $request->type,
            'serial_number'      => $request->serial_number,
            'tanggal_datang'     => $request->tanggal_datang,
            'lokasi_asli'        => $request->lokasi_asli,
            'status_line'        => null, // Default di Lab
            'kondisi_saat_ini'   => 'Baik',
            'calibration_number' => $request->calibration_number,
            'spesifikasi'        => $this->buildSpesifikasi($request),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil ditambahkan.',
        ]);
    }

    // Method untuk edit modal
    public function edit($id)
    {
        $peralatan    = Peralatan::findOrFail($id);
        $lineList     = MasterLine::aktif()->orderBy('nama_line')->pluck('nama_line');
        $kategoriList = MasterKategoriAlat::aktif()->orderBy('nama_kategori')->get();

        return response()->json([
            'success' => true,
            'html'    => view('peralatan.partials.edit-modal', compact('peralatan', 'lineList', 'kategoriList'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_alat_id'  => 'required|exists:master_kategori_alat,id',
            'kode_asset'        => 'required|unique:peralatan,kode_asset,' . $id,
            'merk'              => 'required|string|max:255',
            'type'              => 'nullable|string|max:255',
            'serial_number'     => 'nullable|string|max:255',
            'tanggal_datang'    => 'required|date',
            'lokasi_asli'       => 'required|string',
        ], [
            'kategori_alat_id.required' => 'Kategori alat harus dipilih.',
            'kategori_alat_id.exists'   => 'Kategori alat tidak valid.',
            'kode_asset.unique'         => 'Kode Asset sudah ada.',
            'lokasi_asli.required'      => 'Lokasi asli harus dipilih.',
        ]);

        $peralatan = Peralatan::findOrFail($id);
        $peralatan->update([
            'kategori_alat_id'   => $request->kategori_alat_id,
            'kode_asset'         => $request->kode_asset,
            'merk'               => $request->merk,
            'type'               => $request->type,
            'serial_number'      => $request->serial_number,
            'tanggal_datang'     => $request->tanggal_datang,
            'lokasi_asli'        => $request->lokasi_asli,
            'calibration_number' => $request->calibration_number,
            'spesifikasi'        => $this->buildSpesifikasi($request),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $peralatan = Peralatan::findOrFail($id);
        $peralatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil dihapus.',
        ]);
    }

    public function riwayat($id)
    {
        \Log::info('=== RIWAYAT METHOD CALLED ===');
        \Log::info('ID: ' . $id);

        try {
            if (!is_numeric($id) || $id <= 0) {
                \Log::error('Invalid ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'ID peralatan tidak valid: ' . $id,
                ], 400);
            }

            $peralatan = Peralatan::find($id);

            if (!$peralatan) {
                \Log::error('Peralatan not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Peralatan tidak ditemukan dengan ID: ' . $id,
                ], 404);
            }

            \Log::info('Peralatan found: ' . $peralatan->kode_asset);

            $riwayatPerbaikan = $peralatan->riwayatPerbaikan()->orderBy('created_at', 'desc')->get();
            \Log::info('Riwayat Perbaikan count: ' . $riwayatPerbaikan->count());

            $riwayatPenggunaan = $peralatan->riwayatPenggunaan()->orderBy('created_at', 'desc')->get();
            \Log::info('Riwayat Penggunaan count: ' . $riwayatPenggunaan->count());

            try {
                $html = view('peralatan.partials.riwayat-modal', compact('peralatan'))->render();
                \Log::info('View rendered successfully, length: ' . strlen($html));

                return response()->json([
                    'success' => true,
                    'html'    => $html,
                ]);
            } catch (\Exception $e) {
                \Log::error('View rendering error: ' . $e->getMessage());
                \Log::error('View file: ' . $e->getFile() . ':' . $e->getLine());

                return response()->json([
                    'success' => false,
                    'message' => 'Error rendering view: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Exception in riwayat method: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function detail($id)
    {
        \Log::info('=== DETAIL METHOD CALLED ===');
        \Log::info('ID: ' . $id);

        try {
            if (!is_numeric($id) || $id <= 0) {
                \Log::error('Invalid ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'ID peralatan tidak valid: ' . $id,
                ], 400);
            }

            $peralatan = Peralatan::with([
                'kategoriAlat',
                'riwayatPenggunaan' => fn($q) => $q->orderBy('created_at', 'desc'),
                'riwayatPerbaikan'  => fn($q) => $q->orderBy('created_at', 'desc'),
                'kalibrasi'         => fn($q) => $q->orderBy('tanggal_pelaksanaan', 'desc'),
                'laporanKerusakan'  => fn($q) => $q->orderBy('tanggal_laporan', 'desc'),
                'laporanKerusakan.keluhanList',
            ])->find($id);

            if (!$peralatan) {
                \Log::error('Peralatan not found: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Peralatan tidak ditemukan dengan ID: ' . $id,
                ], 404);
            }

            \Log::info('Peralatan found: ' . $peralatan->kode_asset);

            try {
                $html = view('peralatan.partials.detail-modal', compact('peralatan'))->render();
                \Log::info('Detail view rendered successfully, length: ' . strlen($html));

                return response()->json([
                    'success' => true,
                    'html'    => $html,
                ]);
            } catch (\Exception $e) {
                \Log::error('View rendering error: ' . $e->getMessage());
                \Log::error('View file: ' . $e->getFile() . ':' . $e->getLine());

                return response()->json([
                    'success' => false,
                    'message' => 'Error rendering view: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Exception in detail method: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new PeralatanImport, $request->file('file'));
            return redirect()->route('peralatan.index')
                ->with('success', 'Data peralatan berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('peralatan.index')
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new PeralatanExport, 'peralatan-' . date('Y-m-d') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $filePath = resource_path('templates/template-import-peralatan.xlsx');

        if (!file_exists($filePath)) {
            return redirect()->route('peralatan.index')
                ->with('error', 'Template tidak ditemukan.');
        }

        return response()->download($filePath, 'template-import-peralatan.xlsx');
    }

    // Sinkronisasi kondisi peralatan (dipanggil dari luar / AJAX cepat)
    public function updateKondisi($id, Request $request)
    {
        $request->validate([
            'kondisi_saat_ini' => 'required|in:Baik,Rusak,Dalam Perbaikan',
        ]);

        $peralatan          = Peralatan::findOrFail($id);
        $kondisiSebelumnya  = $peralatan->kondisi_saat_ini;

        $peralatan->update([
            'kondisi_saat_ini' => $request->kondisi_saat_ini,
        ]);

        // Sinkronisasi ke riwayat penggunaan yang aktif
        if ($kondisiSebelumnya !== $request->kondisi_saat_ini) {
            app(PenggunaanController::class)->updateKondisi($id, $request->kondisi_saat_ini);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kondisi peralatan berhasil diperbarui.',
        ]);
    }

    // Menandai peralatan rusak
    public function tandaiRusak($id)
    {
        $peralatan = Peralatan::findOrFail($id);

        // Validasi: hanya peralatan yang Baik dan sedang di Line yang bisa ditandai rusak
        if ($peralatan->kondisi_saat_ini !== 'Baik' || $peralatan->status_line === null) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peralatan dengan kondisi Baik yang sedang digunakan di Line yang bisa ditandai rusak.',
            ], 422);
        }

        $peralatan->update([
            'kondisi_saat_ini' => 'Rusak',
            // status_line tetap sama (masih di line)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peralatan berhasil ditandai rusak. Sekarang bisa dicatat di menu Perbaikan.',
        ]);
    }

    /**
     * Susun array asosiatif spesifikasi dari input dinamis (label + nilai)
     * yang dikirim form sebagai spesifikasi_label[] dan spesifikasi_value[].
     * Setiap kategori alat bisa punya field spesifikasi yang berbeda-beda,
     * jadi ini sengaja dibuat generik (bukan field tetap per kategori).
     */
    private function buildSpesifikasi(Request $request): ?array
    {
        if (!$request->has('spesifikasi_label')) {
            return null;
        }

        $labels = $request->input('spesifikasi_label', []);
        $values = $request->input('spesifikasi_value', []);
        $spesifikasi = [];

        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            $spesifikasi[$label] = trim((string) ($values[$i] ?? ''));
        }

        return $spesifikasi ?: null;
    }
}