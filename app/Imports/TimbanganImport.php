<?php

namespace App\Imports;

use App\Models\Timbangan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TimbanganImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Timbangan([
            'kode_asset' => $row['kode_asset'] ?? $row['kode_asset'],
            'merk_tipe_no_seri' => $row['merk_tipe_no_seri'] ?? $row['merk_tipe_no_seri'],
            'tanggal_datang' => isset($row['tanggal_datang']) ? Carbon::createFromFormat('Y-m-d', $row['tanggal_datang']) : null,
            'tanggal_pemakaian' => isset($row['tanggal_pemakaian']) ? Carbon::createFromFormat('Y-m-d', $row['tanggal_pemakaian']) : null,
            'tanggal_kerusakan' => isset($row['tanggal_kerusakan']) ? Carbon::createFromFormat('Y-m-d', $row['tanggal_kerusakan']) : null,
            'keluhan' => $row['keluhan'] ?? null,
            'perbaikan' => $row['perbaikan'] ?? null,
            'perbaikan_eksternal' => $row['perbaikan_eksternal'] ?? null,
            'tanggal_rilis' => isset($row['tanggal_rilis']) ? Carbon::createFromFormat('Y-m-d', $row['tanggal_rilis']) : null,
            'status_line' => $row['status_line'] ?? null,
            'kondisi_saat_ini' => $row['kondisi_saat_ini'] ?? 'Baik',
            'lokasi_saat_ini' => $row['lokasi_saat_ini'] ?? 'Gudang',
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_asset' => 'required|unique:timbangan,kode_asset',
            'merk_tipe_no_seri' => 'required',
            'tanggal_datang' => 'required|date',
            'lokasi_saat_ini' => 'required'
        ];
    }
}