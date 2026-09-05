<?php

namespace App\Imports;

use App\Models\Peralatan;
use App\Models\MasterKategoriAlat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PeralatanImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * Cache kode_kategori => id supaya tidak query berulang tiap baris
     */
    protected function resolveKategoriId(?string $kodeKategori): ?int
    {
        if (!$kodeKategori) {
            return null;
        }

        $kodeKategori = strtoupper(trim($kodeKategori));

        return Cache::remember('kategori-alat-id-' . $kodeKategori, 60, function () use ($kodeKategori) {
            return MasterKategoriAlat::where('kode_kategori', $kodeKategori)->value('id');
        });
    }

    public function model(array $row)
    {
        // Kolom wajib mengikuti nama kolom asli di tabel peralatan.
        // FIX: sebelumnya pakai 1 kolom gabungan 'merk_tipe_no_seri' yang tidak
        // pernah cocok dengan $fillable Peralatan (merk, type, serial_number
        // adalah 3 kolom terpisah) — akibatnya data merk/type/serial selalu
        // hilang diam-diam saat import. Sekarang dipisah jadi 3 kolom asli.
        $kolomWajib = [
            'kode_kategori', 'kode_asset', 'merk', 'type', 'serial_number',
            'tanggal_datang', 'lokasi_asli', 'calibration_number',
        ];

        // Spesifikasi bebas: kolom-kolom tambahan di luar kolom wajib
        // dianggap sebagai pasangan label => nilai spesifikasi
        // (mis. 'kapasitas' untuk Timbangan, 'range' untuk Thermometer, dst).
        $spesifikasi = [];
        foreach ($row as $key => $value) {
            if (!in_array($key, $kolomWajib) && $value !== null && $value !== '') {
                $spesifikasi[$key] = $value;
            }
        }

        return new Peralatan([
            'kategori_alat_id'   => $this->resolveKategoriId($row['kode_kategori'] ?? null),
            'kode_asset'         => $row['kode_asset'] ?? null,
            'merk'               => $row['merk'] ?? null,
            'type'               => $row['type'] ?? null,
            'serial_number'      => $row['serial_number'] ?? null,
            'tanggal_datang'     => isset($row['tanggal_datang']) && $row['tanggal_datang']
                ? Carbon::parse($row['tanggal_datang'])
                : null,
            'lokasi_asli'        => $row['lokasi_asli'] ?? null,
            'status_line'        => null, // Import selalu masuk ke Lab dulu
            'kondisi_saat_ini'   => 'Baik',
            'calibration_number' => $row['calibration_number'] ?? null,
            'spesifikasi'        => $spesifikasi ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_kategori'      => 'required|exists:master_kategori_alat,kode_kategori',
            'kode_asset'         => 'required|unique:peralatan,kode_asset',
            'merk'               => 'required',
            'type'               => 'nullable',
            'serial_number'      => 'nullable',
            'tanggal_datang'     => 'required|date',
            'lokasi_asli'        => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_kategori.exists' => 'Kode kategori tidak ditemukan di Master Kategori Alat.',
            'kode_asset.unique'    => 'Kode Asset sudah ada di database.',
            'merk.required'        => 'Merk wajib diisi.',
        ];
    }
}