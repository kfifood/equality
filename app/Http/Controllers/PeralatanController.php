<?php

namespace App\Http\Controllers;

 use App\Models\Peralatan;
 use App\Models\MasterLine;
 use App\Models\MasterKategoriAlat;
 use Illuminate\Http\Request;
 use App\Imports\PeralatanImport;
 use App\Exports\PeralatanExport;
 use Maatwebsite\Excel\Facades\Excel;
 use PhpOffice\PhpSpreadsheet\Spreadsheet;
 use PhpOffice\PhpSpreadsheet\Style\Fill;
 use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
 use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
 use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            'tanggal_datang'    => 'nullable|date',
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
            'tanggal_datang'    => 'nullable|date',
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

     /**
     * Import massal dari Excel. Dipanggil via AJAX dari modal import.
     * Response selalu JSON: {success, message, errors?[]}
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
        ]);
 
        $import = new PeralatanImport;
 
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'File tidak bisa diproses: ' . $e->getMessage(),
            ]);
        }
 
        if (!empty($import->errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Import dibatalkan, tidak ada data yang disimpan. Perbaiki error berikut lalu upload ulang:',
                'errors'  => $import->errors,
            ]);
        }
 
        return response()->json([
            'success' => true,
            'message' => $import->jumlahBerhasil . ' peralatan berhasil diimport.',
        ]);
    }
 
    /**
     * Export data peralatan. PeralatanExport sekarang butuh ($year, $month, $format, $line)
     * karena dipakai juga untuk laporan; untuk daftar peralatan saja pakai format 'peralatan'
     * (PeralatanSheet tidak memakai year/month).
     */
    public function export()
    {
        return Excel::download(
            new PeralatanExport(now()->year, now()->month, 'peralatan'),
            'peralatan-' . date('Y-m-d') . '.xlsx'
        );
    }
 
    /**
     * Template Excel dibuat on-the-fly supaya dropdown kategori & lokasi selalu
     * sinkron dengan Master Kategori Alat dan Master Line yang aktif.
     * (Tidak lagi butuh file statis resources/templates/template-import-peralatan.xlsx)
     */
    public function downloadTemplate()
    {
        $kategori = MasterKategoriAlat::aktif()->orderBy('nama_kategori')->get(['nama_kategori', 'kode_kategori']);
        $lokasi   = collect(['Lab'])->merge(MasterLine::aktif()->orderBy('nama_line')->pluck('nama_line'));
 
        $spreadsheet = new Spreadsheet();
 
        // ── Sheet 1: Data Peralatan (yang diisi user & diimport) ─────────────
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Data Peralatan');
 
        $headers = [
            'kategori (wajib)', 'kode_asset (wajib)', 'merk (wajib)', 'type', 'serial_number',
            'tanggal_datang', 'lokasi_asli (wajib)', 'calibration_number',
            'kapasitas', 'range_suhu', // contoh kolom spesifikasi — boleh ganti / tambah
        ];
        $ws->fromArray($headers, null, 'A1');
 
        $ws->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4361EE']],
        ]);
        // Kolom WAJIB = merah (dan bertuliskan "(wajib)"), opsional = biru, spesifikasi = abu-abu
        foreach (['A1:C1', 'G1'] as $rangeWajib) {
            $ws->getStyle($rangeWajib)->getFill()->getStartColor()->setRGB('DC3545');
        }
        $ws->getStyle('I1:J1')->getFill()->getStartColor()->setRGB('6C757D');
 
        foreach (['A' => 22, 'B' => 22, 'C' => 18, 'D' => 16, 'E' => 16, 'F' => 16, 'G' => 22, 'H' => 22, 'I' => 18, 'J' => 18] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }
        $ws->freezePane('A2');
 
        // Format Text agar Excel tidak mengubah "007" jadi 7 atau No. Seri panjang jadi 1.2E+11
        foreach (['B', 'E', 'H'] as $col) {
            $ws->getStyle("{$col}2:{$col}1000")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }
        $ws->getStyle('F2:F1000')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
 
        // ── Sheet 2: Referensi (isi dropdown) ────────────────────────────────
        $ref = $spreadsheet->createSheet();
        $ref->setTitle('Referensi');
        $ref->fromArray(['Kategori (nama)', 'Kode'], null, 'A1');
        $r = 2;
        foreach ($kategori as $k) {
            $ref->setCellValueExplicit("A{$r}", $k->nama_kategori, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $ref->setCellValueExplicit("B{$r}", $k->kode_kategori, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $r++;
        }
        $lastKat = max($r - 1, 2);
 
        $ref->setCellValue('D1', 'Lokasi Asli');
        $r = 2;
        foreach ($lokasi as $l) {
            $ref->setCellValueExplicit("D{$r}", $l, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $r++;
        }
        $lastLok = max($r - 1, 2);
 
        $ref->getStyle('A1:D1')->getFont()->setBold(true);
        $ref->getColumnDimension('A')->setWidth(24);
        $ref->getColumnDimension('B')->setWidth(10);
        $ref->getColumnDimension('D')->setWidth(18);
 
        // ── Dropdown ─────────────────────────────────────────────────────────
        $dvKategori = new DataValidation();
        $dvKategori->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Kategori tidak valid')
            ->setError('Pilih kategori dari daftar (lihat sheet Referensi).')
            ->setFormula1('=Referensi!$A$2:$A$' . $lastKat);
        $ws->setDataValidation('A2:A1000', $dvKategori);
 
        $dvLokasi = new DataValidation();
        $dvLokasi->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Lokasi tidak valid')
            ->setError('Pilih "Lab" atau salah satu Line dari daftar.')
            ->setFormula1('=Referensi!$D$2:$D$' . $lastLok);
        $ws->setDataValidation('G2:G1000', $dvLokasi);
 
        // ── Sheet 3: Petunjuk ────────────────────────────────────────────────
        $info = $spreadsheet->createSheet();
        $info->setTitle('Petunjuk');
        $baris = [
            ['PETUNJUK PENGISIAN'],
            [''],
            ['1. Isi data mulai baris 2 di sheet "Data Peralatan". Jangan ubah / hapus baris header (baris 1).'],
            ['2. Kolom WAJIB (header merah, bertanda "(wajib)"): kategori, kode_asset, merk, lokasi_asli.'],
            ['   Header biru = opsional, header abu-abu = spesifikasi (opsional). Tulisan "(wajib)" jangan dihapus/diubah.'],
            ['3. Kolom opsional: type, serial_number, tanggal_datang (dd/mm/yyyy), calibration_number.'],
            ['4. kategori boleh diisi nama (mis. "Timbangan") atau kode kategori; pilih dari dropdown.'],
            ['5. lokasi_asli: "Lab" atau nama Line yang aktif; pilih dari dropdown.'],
            ['6. kode_asset harus unik — tidak boleh sama dengan data yang sudah ada maupun baris lain di file ini.'],
            ['7. Kolom I dst. (kapasitas, range_suhu, ...) = SPESIFIKASI. Nama kolom jadi label spesifikasi.'],
            ['   Boleh ganti nama kolom atau tambah kolom baru di sebelah kanan. Sel yang kosong dilewati.'],
            ['   Gunakan nama kolom tanpa simbol, mis. "range_suhu" (bukan "Range (°C)").'],
            ['8. Alat yang diimport otomatis berstatus Baik dan berada di Lab (sama seperti tombol Tambah).'],
            ['9. Kalau ada SATU baris saja yang salah, seluruh import dibatalkan dan nomor baris yang salah ditampilkan.'],
            ['10. Hanya sheet pertama ("Data Peralatan") yang dibaca.'],
        ];
        $info->fromArray($baris, null, 'A1');
        $info->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $info->getColumnDimension('A')->setWidth(110);
 
        $spreadsheet->setActiveSheetIndex(0);
 
        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template-import-peralatan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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