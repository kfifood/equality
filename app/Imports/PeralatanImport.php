<?php

namespace App\Imports;

use App\Models\MasterKategoriAlat;
use App\Models\MasterLine;
use App\Models\Peralatan;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import massal peralatan dari Excel (sheet pertama).
 *
 * Alur: SEMUA baris divalidasi dulu. Kalau ada satu saja yang salah, tidak ada
 * data yang disimpan dan daftar error (lengkap dengan nomor baris Excel)
 * tersedia di $errors. Kalau semua valid, disimpan dalam satu transaksi.
 *
 * Hanya SHEET PERTAMA yang dibaca (sheet Referensi & Petunjuk di template diabaikan).
 * Heading boleh diberi tanda "(wajib)" atau "*", mis. "kode_asset (wajib)" — tanda itu
 * dibuang otomatis.
 *
 * Kolom (nama heading di baris 1):
 *   wajib   : kategori (nama ATAU kode kategori), kode_asset, merk, lokasi_asli
 *   opsional: type, serial_number, tanggal_datang, calibration_number
 *   lainnya : kolom tambahan apa pun dianggap spesifikasi (label = nama kolom)
 */
class PeralatanImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    /** @var string[] Pesan error, satu entri per baris bermasalah */
    public array $errors = [];

    public int $jumlahBerhasil = 0;

    private const KOLOM_TETAP = [
        'kategori', 'kode_kategori', 'kode_asset', 'merk', 'type',
        'serial_number', 'tanggal_datang', 'lokasi_asli', 'calibration_number',
    ];

    /**
     * Tanpa ini Maatwebsite menjalankan collection() untuk SEMUA sheet di file.
     * Index 0 = sheet pertama saja.
     */
    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $rows): void
    {
        // Samakan heading: "kode_asset (wajib)" -> slug "kode_asset_wajib" -> "kode_asset"
        $rows = $rows->map(function ($r) {
            $r = $r instanceof Collection ? $r->toArray() : (array) $r;
            $baru = [];
            foreach ($r as $kolom => $nilai) {
                $baru[is_string($kolom) ? $this->normalisasiHeading($kolom) : $kolom] = $nilai;
            }
            return $baru;
        });

        if ($rows->isEmpty()) {
            $this->errors[] = 'File kosong atau tidak ada data di bawah baris header.';
            return;
        }

        // ── Cek heading ──────────────────────────────────────────────────────
        $headers = array_keys($rows->first());
        $hilang  = [];
        foreach (['kode_asset', 'merk', 'lokasi_asli'] as $h) {
            if (!in_array($h, $headers, true)) {
                $hilang[] = $h;
            }
        }
        if (!in_array('kategori', $headers, true) && !in_array('kode_kategori', $headers, true)) {
            $hilang[] = 'kategori';
        }
        if ($hilang) {
            $this->errors[] = 'Kolom wajib tidak ditemukan di baris 1: ' . implode(', ', $hilang)
                . '. Gunakan template terbaru dari tombol "Template".';
            return;
        }

        // ── Data referensi (sekali query, bukan per baris) ───────────────────
        $kategoriMap = [];
        foreach (MasterKategoriAlat::aktif()->get() as $k) {
            $kategoriMap[mb_strtolower(trim($k->nama_kategori))] = $k->id;
            $kategoriMap[mb_strtolower(trim($k->kode_kategori))] = $k->id;
        }

        $lokasiMap = ['lab' => 'Lab'];
        foreach (MasterLine::aktif()->pluck('nama_line') as $nama) {
            $lokasiMap[mb_strtolower(trim($nama))] = $nama;
        }

        // kode_asset yang sudah ada di database (case-insensitive, sama seperti collation MySQL)
        $kodeAda = [];
        foreach (Peralatan::pluck('kode_asset') as $kode) {
            $kodeAda[mb_strtoupper(trim($kode))] = true;
        }

        // ── Validasi tiap baris ──────────────────────────────────────────────
        $valid       = [];
        $kodeDiFile  = []; // kode (uppercase) => nomor baris pertama kemunculan

        foreach ($rows as $i => $row) {
            $baris = $i + 2; // baris 1 = header

            if ($this->barisKosong($row)) {
                continue;
            }

            $masalah = [];

            $kode     = $this->teks($row['kode_asset'] ?? null);
            $kategori = $this->teks($row['kategori'] ?? $row['kode_kategori'] ?? null);
            $merk     = $this->teks($row['merk'] ?? null);
            $type     = $this->teks($row['type'] ?? null);
            $serial   = $this->teks($row['serial_number'] ?? null);
            $calNo    = $this->teks($row['calibration_number'] ?? null);
            $lokasi   = $this->teks($row['lokasi_asli'] ?? null);

            // kode_asset
            if ($kode === null) {
                $masalah[] = 'kode_asset kosong';
            } else {
                $key = mb_strtoupper($kode);
                if (mb_strlen($kode) > 255) {
                    $masalah[] = 'kode_asset terlalu panjang (maks 255 karakter)';
                }
                if (isset($kodeAda[$key])) {
                    $masalah[] = "kode_asset '{$kode}' sudah ada di database";
                } elseif (isset($kodeDiFile[$key])) {
                    $masalah[] = "kode_asset '{$kode}' duplikat dengan baris {$kodeDiFile[$key]} di file ini";
                } else {
                    $kodeDiFile[$key] = $baris;
                }
            }

            // kategori
            $kategoriId = null;
            if ($kategori === null) {
                $masalah[] = 'kategori kosong';
            } else {
                $kategoriId = $kategoriMap[mb_strtolower($kategori)] ?? null;
                if (!$kategoriId) {
                    $masalah[] = "kategori '{$kategori}' tidak ditemukan di Master Kategori Alat (atau tidak aktif)";
                }
            }

            // merk
            if ($merk === null) {
                $masalah[] = 'merk kosong';
            }

            foreach (['merk' => $merk, 'type' => $type, 'serial_number' => $serial, 'calibration_number' => $calNo] as $nama => $nilai) {
                if ($nilai !== null && mb_strlen($nilai) > 255) {
                    $masalah[] = "{$nama} terlalu panjang (maks 255 karakter)";
                }
            }

            // lokasi_asli
            $lokasiFinal = null;
            if ($lokasi === null) {
                $masalah[] = 'lokasi_asli kosong';
            } else {
                $lokasiFinal = $lokasiMap[mb_strtolower($lokasi)] ?? null;
                if (!$lokasiFinal) {
                    $masalah[] = "lokasi_asli '{$lokasi}' bukan 'Lab' atau Line yang aktif";
                }
            }

            // tanggal_datang (opsional)
            $tanggal = null;
            try {
                $tanggal = $this->parseTanggal($row['tanggal_datang'] ?? null);
            } catch (\InvalidArgumentException $e) {
                $masalah[] = $e->getMessage();
            }

            // spesifikasi = kolom di luar kolom tetap
            $spesifikasi = [];
            foreach ($row as $kolom => $nilai) {
                if (!is_string($kolom) || $kolom === '' || in_array($kolom, self::KOLOM_TETAP, true)) {
                    continue;
                }
                $nilai = $this->teks($nilai);
                if ($nilai === null) {
                    continue;
                }
                $label = trim(mb_strtolower(str_replace('_', ' ', $kolom)));
                if ($label !== '') {
                    $spesifikasi[$label] = $nilai;
                }
            }

            if ($masalah) {
                $this->errors[] = "Baris {$baris}: " . implode('; ', $masalah) . '.';
                continue;
            }

            $valid[] = [
                'kategori_alat_id'   => $kategoriId,
                'kode_asset'         => $kode,
                'merk'               => $merk,
                'type'               => $type,
                'serial_number'      => $serial,
                'tanggal_datang'     => $tanggal?->format('Y-m-d'),
                'lokasi_asli'        => $lokasiFinal,
                'status_line'        => null,   // sama seperti form tambah: masuk Lab dulu
                'kondisi_saat_ini'   => 'Baik',
                'calibration_number' => $calNo,
                'spesifikasi'        => $spesifikasi ?: null,
            ];
        }

        if ($this->errors) {
            return; // tolak semua
        }

        if (empty($valid)) {
            $this->errors[] = 'Tidak ada baris data yang bisa diimport.';
            return;
        }

        // ── Simpan: semua atau tidak sama sekali ─────────────────────────────
        DB::transaction(function () use ($valid) {
            foreach ($valid as $data) {
                Peralatan::create($data); // create() agar cast 'spesifikasi' => array jalan
            }
        });

        $this->jumlahBerhasil = count($valid);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    /** Buang penanda "wajib" dari heading yang sudah di-slug oleh Maatwebsite. */
    private function normalisasiHeading(string $kolom): string
    {
        return preg_replace('/_?wajib$/', '', $kolom) ?: $kolom;
    }

    private function barisKosong(array $row): bool
    {
        foreach ($row as $nilai) {
            if ($nilai !== null && trim((string) $nilai) !== '') {
                return false;
            }
        }
        return true;
    }

    /** Rapikan nilai sel jadi string (atau null kalau kosong). */
    private function teks($nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }
        // Excel sering mengirim angka sebagai float: 12345.0 -> "12345"
        if (is_float($nilai) && floor($nilai) == $nilai) {
            $nilai = (int) $nilai;
        }
        $nilai = trim((string) $nilai);

        return $nilai === '' ? null : $nilai;
    }

    /**
     * Terima tanggal dari sel Excel bertipe date (angka serial), atau teks
     * dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd. Kosong => null.
     */
    private function parseTanggal($nilai): ?Carbon
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }

        if (is_numeric($nilai)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $nilai))->startOfDay();
        }

        $teks = trim((string) $nilai);
        foreach (['j/n/Y', 'j-n-Y', 'Y-n-j'] as $format) {
            $dt  = DateTime::createFromFormat('!' . $format, $teks);
            $err = DateTime::getLastErrors();
            $bersih = $err === false || ($err['warning_count'] === 0 && $err['error_count'] === 0);
            if ($dt && $bersih) {
                return Carbon::instance($dt);
            }
        }

        throw new \InvalidArgumentException("format tanggal_datang '{$teks}' tidak dikenali (pakai dd/mm/yyyy)");
    }
}