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
        // Spesifikasi bebas: kolom-kolom tambahan di luar kolom wajib
        // dianggap sebagai pasangan label => nilai spesifikasi.
        $kolomWajib = [
            'kode_kategori', 'kode_asset', 'merk_tipe_no_seri',
            'tanggal_datang', 'lokasi_asli', 'certificate_number',
        ];
        $spesifikasi = [];
        foreach ($row as $key => $value) {
            if (!in_array($key, $kolomWajib) && $value !== null && $value !== '') {
                $spesifikasi[$key] = $value;
            }
        }

        return new Peralatan([
            'kategori_alat_id'   => $this->resolveKategoriId($row['kode_kategori'] ?? null),
            'kode_asset'         => $row['kode_asset'] ?? null,
            'merk_tipe_no_seri'  => $row['merk_tipe_no_seri'] ?? null,
            'tanggal_datang'     => isset($row['tanggal_datang']) && $row['tanggal_datang']
                ? Carbon::parse($row['tanggal_datang'])
                : null,
            'lokasi_asli'        => $row['lokasi_asli'] ?? null,
            'status_line'        => null, // Import selalu masuk ke Lab dulu
            'kondisi_saat_ini'   => 'Baik',
            'certificate_number' => $row['certificate_number'] ?? null,
            'spesifikasi'        => $spesifikasi ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_kategori'      => 'required|exists:master_kategori_alat,kode_kategori',
            'kode_asset'         => 'required|unique:peralatan,kode_asset',
            'merk_tipe_no_seri'  => 'required',
            'tanggal_datang'     => 'required|date',
            'lokasi_asli'        => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_kategori.exists' => 'Kode kategori tidak ditemukan di Master Kategori Alat.',
            'kode_asset.unique'    => 'Kode Asset sudah ada di database.',
        ];
    }
}