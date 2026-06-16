<?php

namespace App\Http\Controllers;

use App\Models\Kalibrasi;
use App\Models\Timbangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class KalibrasiController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Kalibrasi::with('timbangan');

        if ($request->filled('timbangan_id')) {
            $query->where('timbangan_id', $request->timbangan_id);
        }
        if ($request->filled('hasil')) {
            $query->where('hasil', $request->hasil);
        }
        if ($request->filled('dept_bagian')) {
            $query->where('dept_bagian', $request->dept_bagian);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', '%' . $search . '%')
                  ->orWhere('pelaksana', 'like', '%' . $search . '%')
                  ->orWhere('dept_bagian', 'like', '%' . $search . '%')
                  ->orWhereHas('timbangan', fn($q2) =>
                      $q2->where('kode_asset', 'like', '%' . $search . '%')
                         ->orWhere('merk_tipe_no_seri', 'like', '%' . $search . '%')
                  );
            });
        }

        $perPage   = $request->get('per_page', 10);
        $kalibrasi = $query->orderBy('tanggal_pelaksanaan', 'desc')
                           ->paginate($perPage)
                           ->withQueryString();

        $timbanganList = Timbangan::orderBy('kode_asset')->get(['id', 'kode_asset', 'merk_tipe_no_seri']);
        $deptList      = Kalibrasi::whereNotNull('dept_bagian')->distinct()->orderBy('dept_bagian')->pluck('dept_bagian');

        return view('kalibrasi.index', compact('kalibrasi', 'timbanganList', 'deptList'));
    }

    // ── CREATE (single) ───────────────────────────────────────────────────────

    public function create()
    {
        $timbanganList = Timbangan::orderBy('kode_asset')
                                  ->get(['id', 'kode_asset', 'merk_tipe_no_seri', 'certificate_number']);

        return response()->json([
            'success' => true,
            'html'    => view('kalibrasi.partials.create-modal', compact('timbanganList'))->render(),
        ]);
    }

    // ── STORE (single) ────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'timbangan_id'        => 'required|exists:timbangan,id',
            'tanggal_pelaksanaan' => 'required|date',
            'certificate_number'  => 'nullable|string|max:255',
            'dept_bagian'         => 'nullable|string|max:255',
            'beda_maksimum'       => 'nullable|string|max:100',
            'hasil'               => 'nullable|in:Lulus,Tidak Lulus',
            'pelaksana'           => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
        ], [
            'timbangan_id.required'        => 'Timbangan harus dipilih.',
            'timbangan_id.exists'          => 'Timbangan tidak ditemukan.',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan harus diisi.',
        ]);

        Kalibrasi::create($request->only([
            'timbangan_id', 'certificate_number', 'tanggal_pelaksanaan',
            'dept_bagian', 'beda_maksimum', 'hasil', 'pelaksana', 'catatan',
        ]));

        return response()->json(['success' => true, 'message' => 'Data kalibrasi berhasil ditambahkan.']);
    }

    // ── STORE BULK ────────────────────────────────────────────────────────────

    /**
     * Simpan banyak data kalibrasi sekaligus (dari form bulk / input massal).
     *
     * Route: POST /kalibrasi/bulk
     * Body JSON: { rows: [ { timbangan_id, tanggal_pelaksanaan, ... } ] }
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'rows'                              => 'required|array|min:1|max:100',
            'rows.*.timbangan_id'               => 'required|exists:timbangan,id',
            'rows.*.tanggal_pelaksanaan'        => 'required|date',
            'rows.*.certificate_number'         => 'nullable|string|max:255',
            'rows.*.dept_bagian'                => 'nullable|string|max:255',
            'rows.*.beda_maksimum'              => 'nullable|string|max:100',
            'rows.*.hasil'                      => 'nullable|in:Lulus,Tidak Lulus',
            'rows.*.pelaksana'                  => 'nullable|string|max:255',
            'rows.*.catatan'                    => 'nullable|string',
        ], [
            'rows.required'                      => 'Tidak ada data yang dikirim.',
            'rows.*.timbangan_id.required'       => 'Setiap baris harus memilih timbangan.',
            'rows.*.timbangan_id.exists'         => 'Timbangan pada salah satu baris tidak ditemukan.',
            'rows.*.tanggal_pelaksanaan.required'=> 'Tanggal pelaksanaan wajib diisi.',
        ]);

        $rows = $request->rows;
        $now  = now();

        $insertData = array_map(fn($r) => [
            'timbangan_id'        => $r['timbangan_id'],
            'certificate_number'  => $r['certificate_number']  ?? null,
            'tanggal_pelaksanaan' => $r['tanggal_pelaksanaan'],
            'dept_bagian'         => $r['dept_bagian']         ?? null,
            'beda_maksimum'       => $r['beda_maksimum']       ?? null,
            'hasil'               => $r['hasil']               ?: null,
            'pelaksana'           => $r['pelaksana']           ?? null,
            'catatan'             => $r['catatan']             ?? null,
            'created_at'          => $now,
            'updated_at'          => $now,
        ], $rows);

        DB::beginTransaction();
        try {
            Kalibrasi::insert($insertData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => count($rows) . ' data kalibrasi berhasil disimpan.',
        ]);
    }

    // ── EDIT ─────────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $kalibrasi     = Kalibrasi::with('timbangan')->findOrFail($id);
        $timbanganList = Timbangan::orderBy('kode_asset')
                                  ->get(['id', 'kode_asset', 'merk_tipe_no_seri', 'certificate_number']);

        return response()->json([
            'success' => true,
            'html'    => view('kalibrasi.partials.edit-modal', compact('kalibrasi', 'timbanganList'))->render(),
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $kalibrasi = Kalibrasi::findOrFail($id);

        $request->validate([
            'timbangan_id'        => 'required|exists:timbangan,id',
            'tanggal_pelaksanaan' => 'required|date',
            'certificate_number'  => 'nullable|string|max:255',
            'dept_bagian'         => 'nullable|string|max:255',
            'beda_maksimum'       => 'nullable|string|max:100',
            'hasil'               => 'nullable|in:Lulus,Tidak Lulus',
            'pelaksana'           => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
        ], [
            'timbangan_id.required'        => 'Timbangan harus dipilih.',
            'timbangan_id.exists'          => 'Timbangan tidak ditemukan.',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan harus diisi.',
        ]);

        $kalibrasi->update($request->only([
            'timbangan_id', 'certificate_number', 'tanggal_pelaksanaan',
            'dept_bagian', 'beda_maksimum', 'hasil', 'pelaksana', 'catatan',
        ]));

        return response()->json(['success' => true, 'message' => 'Data kalibrasi berhasil diperbarui.']);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        Kalibrasi::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Data kalibrasi berhasil dihapus.']);
    }

    // ── MODAL BULK INPUT ──────────────────────────────────────────────────────

    /**
     * Render HTML modal input massal.
     *
     * Route: GET /kalibrasi/bulk
     * Name:  kalibrasi.bulk
     */
public function bulk()
{
    $timbanganList = Timbangan::orderBy('kode_asset')
                              ->get(['id', 'kode_asset', 'merk_tipe_no_seri', 'certificate_number']);

    // Siapkan data yang sudah di-map, bukan map di Blade
    $timbanganJson = $timbanganList->map(fn($t) => [
        'id'          => $t->id,
        'kode_asset'  => $t->kode_asset,
        'merk'        => $t->merk_tipe_no_seri,
        'label'       => $t->kode_asset . ' — ' . $t->merk_tipe_no_seri,
        'certificate' => $t->certificate_number ?? '',
    ]);

    return response()->json([
        'success' => true,
        'html'    => view('kalibrasi.partials.bulk-modal', compact('timbanganList', 'timbanganJson'))->render(),
    ]);
}

    // ── MODAL IMPORT EXCEL ────────────────────────────────────────────────────

    /**
     * Render HTML modal import Excel.
     *
     * Route: GET /kalibrasi/import
     * Name:  kalibrasi.importModal
     */
    public function importModal()
    {
        return response()->json([
            'success' => true,
            'html'    => view('kalibrasi.partials.import-modal')->render(),
        ]);
    }

    // ── DOWNLOAD TEMPLATE EXCEL ───────────────────────────────────────────────

    /**
     * Generate & download file template Excel untuk import.
     *
     * Route: GET /kalibrasi/import-template
     * Name:  kalibrasi.importTemplate
     */
    public function importTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Kalibrasi');

        // ── Header ──────────────────────────────────────────────────────────
        $headers = [
            'A' => 'kode_asset',
            'B' => 'tanggal_pelaksanaan',
            'C' => 'certificate_number',
            'D' => 'dept_bagian',
            'E' => 'beda_maksimum',
            'F' => 'hasil',
            'G' => 'pelaksana',
            'H' => 'catatan',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
        }

        // Style header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4361EE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);

        // ── Contoh data (2 baris) ────────────────────────────────────────────
        $examples = [
            ['W - 002', date('Y-m-d'), '', 'QC', '±0.5 g', 'Lulus',       'Lab Internal', ''],
            ['W - 025', date('Y-m-d'), '', 'Lab', '',       'Tidak Lulus', 'Lab Internal', 'Perlu kalibrasi ulang'],
        ];
        foreach ($examples as $i => $row) {
            foreach (array_values($row) as $ci => $val) {
                $sheet->setCellValueByColumnAndRow($ci + 1, $i + 2, $val);
            }
        }

        // Contoh style baris data
        $sheet->getStyle('A2:H3')->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9E6']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
        ]);

        // Auto-width kolom
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Sheet kedua: daftar kode asset valid ─────────────────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Daftar Kode Asset');
        $sheet2->setCellValue('A1', 'kode_asset');
        $sheet2->setCellValue('B1', 'merk_tipe_no_seri');

        $timbanganList = Timbangan::orderBy('kode_asset')->get(['kode_asset', 'merk_tipe_no_seri']);
        foreach ($timbanganList as $i => $t) {
            $sheet2->setCellValue('A' . ($i + 2), $t->kode_asset);
            $sheet2->setCellValue('B' . ($i + 2), $t->merk_tipe_no_seri);
        }
        $sheet2->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEEF0FD']],
        ]);
        $sheet2->getColumnDimension('A')->setAutoSize(true);
        $sheet2->getColumnDimension('B')->setAutoSize(true);

        // Kembali ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        $writer   = new XlsxWriter($spreadsheet);
        $filename = 'template_kalibrasi_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ── IMPORT EXCEL ──────────────────────────────────────────────────────────

    /**
     * Proses upload & simpan data dari file Excel.
     *
     * Route: POST /kalibrasi/import
     * Name:  kalibrasi.import
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

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $ws          = $spreadsheet->getActiveSheet();
            $rows        = $ws->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'File tidak bisa dibaca: ' . $e->getMessage()]);
        }

        if (count($rows) < 2) {
            return response()->json(['success' => false, 'message' => 'File kosong atau tidak ada data di bawah header.']);
        }

        // Ambil semua kode_asset → id agar bisa lookup cepat
        $kodeToId = Timbangan::pluck('id', 'kode_asset')->toArray();

        $validRows = [];
        $errors    = [];

        foreach (array_slice($rows, 1) as $lineNo => $row) {
            $realLine = $lineNo + 2; // baris excel (1-indexed + 1 header)

            // Skip baris kosong sempurna
            if (empty(array_filter(array_map('trim', $row)))) continue;

            [$kodeAsset, $tanggal, $certNo, $dept, $bedaMaks, $hasil, $pelaksana, $catatan] = array_pad($row, 8, null);

            $kodeAsset = trim($kodeAsset ?? '');
            $tanggal   = trim($tanggal   ?? '');

            // Validasi kode_asset
            if (!$kodeAsset) {
                $errors[] = "Baris {$realLine}: kolom kode_asset kosong.";
                continue;
            }
            if (!isset($kodeToId[$kodeAsset])) {
                $errors[] = "Baris {$realLine}: kode_asset '{$kodeAsset}' tidak ditemukan di sistem.";
                continue;
            }

            // Validasi tanggal
            if (!$tanggal) {
                $errors[] = "Baris {$realLine}: tanggal_pelaksanaan kosong.";
                continue;
            }
            try {
                $tanggalFormatted = Carbon::parse($tanggal)->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = "Baris {$realLine}: format tanggal '{$tanggal}' tidak dikenali.";
                continue;
            }

            // Validasi hasil (opsional)
            $hasilVal = trim($hasil ?? '');
            if ($hasilVal && !in_array($hasilVal, ['Lulus', 'Tidak Lulus'])) {
                $errors[] = "Baris {$realLine}: nilai hasil '{$hasilVal}' tidak valid. Gunakan 'Lulus' atau 'Tidak Lulus'.";
                continue;
            }

            $validRows[] = [
                'timbangan_id'        => $kodeToId[$kodeAsset],
                'tanggal_pelaksanaan' => $tanggalFormatted,
                'certificate_number'  => trim($certNo    ?? '') ?: null,
                'dept_bagian'         => trim($dept       ?? '') ?: null,
                'beda_maksimum'       => trim($bedaMaks   ?? '') ?: null,
                'hasil'               => $hasilVal                ?: null,
                'pelaksana'           => trim($pelaksana  ?? '') ?: null,
                'catatan'             => trim($catatan    ?? '') ?: null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        // Jika ada error, tolak semua
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Import dibatalkan karena ada error berikut:',
                'errors'  => $errors,
            ]);
        }

        if (empty($validRows)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada baris data yang valid untuk diimport.']);
        }

        DB::beginTransaction();
        try {
            Kalibrasi::insert($validRows);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => count($validRows) . ' data kalibrasi berhasil diimport.',
        ]);
    }

    // ── STICKER ───────────────────────────────────────────────────────────────

    public function sticker($id)
    {
        $item          = Kalibrasi::with('timbangan')->findOrFail($id);
        $kalibrasiList = collect([$item]);
        return view('kalibrasi.sticker', compact('kalibrasiList'));
    }

    public function stickerBatch(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:kalibrasi,id',
        ], [
            'ids.required' => 'Pilih minimal satu data kalibrasi.',
            'ids.min'      => 'Pilih minimal satu data kalibrasi.',
        ]);

        $kalibrasiList = Kalibrasi::with('timbangan')
                                   ->whereIn('id', $request->ids)
                                   ->orderBy('tanggal_pelaksanaan', 'desc')
                                   ->get();

        return view('kalibrasi.sticker', compact('kalibrasiList'));
    }
}