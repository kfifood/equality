<?php

namespace App\Http\Controllers;

use App\Models\MasterKategoriAlat;
use Illuminate\Http\Request;

class MasterKategoriAlatController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterKategoriAlat::withCount('peralatan');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_kategori', 'like', '%' . $search . '%')
                  ->orWhere('nama_kategori', 'like', '%' . $search . '%');
            });
        }

        // Filter status aktif
        if ($request->filled('status_aktif')) {
            $query->where('status_aktif', $request->status_aktif);
        }

        $perPage = $request->get('per_page', 10);
        $kategoriList = $query->orderBy('nama_kategori')
            ->paginate($perPage)
            ->withQueryString();

        return view('master-kategori-alat.index', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori'  => 'required|string|max:50|unique:master_kategori_alat,kode_kategori',
            'nama_kategori'  => 'required|string|max:255',
            'satuan_default' => 'nullable|string|max:100',
            'status_aktif'   => 'boolean',
        ], [
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
        ]);

        MasterKategoriAlat::create([
            'kode_kategori'  => strtoupper($request->kode_kategori),
            'nama_kategori'  => $request->nama_kategori,
            'satuan_default' => $request->satuan_default,
            'status_aktif'   => $request->boolean('status_aktif', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori alat berhasil ditambahkan.',
        ]);
    }

    /**
     * Ambil data satu kategori (dipakai untuk mengisi form edit di modal
     * yang sudah tersedia di halaman index, tanpa perlu render partial baru)
     */
    public function edit($id)
    {
        $kategori = MasterKategoriAlat::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $kategori,
        ]);
    }

    public function update(Request $request, $id)
    {
        $kategori = MasterKategoriAlat::findOrFail($id);

        $request->validate([
            'kode_kategori'  => 'required|string|max:50|unique:master_kategori_alat,kode_kategori,' . $id,
            'nama_kategori'  => 'required|string|max:255',
            'satuan_default' => 'nullable|string|max:100',
            'status_aktif'   => 'boolean',
        ], [
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
        ]);

        $kategori->update([
            'kode_kategori'  => strtoupper($request->kode_kategori),
            'nama_kategori'  => $request->nama_kategori,
            'satuan_default' => $request->satuan_default,
            'status_aktif'   => $request->boolean('status_aktif', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori alat berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $kategori = MasterKategoriAlat::findOrFail($id);

        // Jangan hapus kategori yang masih dipakai oleh data peralatan
        if ($kategori->peralatan()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh data peralatan.',
            ], 422);
        }

        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori alat berhasil dihapus.',
        ]);
    }

    /**
     * Endpoint ringan untuk dropdown kategori aktif.
     * Dipakai oleh form Peralatan (create/edit) via AJAX/select2.
     */
    public function listAktif()
    {
        $kategori = MasterKategoriAlat::aktif()
            ->orderBy('nama_kategori')
            ->get(['id', 'kode_kategori', 'nama_kategori', 'satuan_default']);

        return response()->json([
            'success' => true,
            'data'    => $kategori,
        ]);
    }
}