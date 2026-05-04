<?php

namespace App\Exports\Concerns;

use App\Models\RiwayatPerbaikan;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait ExcelHelpers
{
    protected const HEADER_COLOR = '4A4A4A';
    protected const HEADER_ALT   = 'F2F2F2';

    protected function resolveKeluhan(RiwayatPerbaikan $perbaikan): string
    {
        if ($perbaikan->laporanKerusakan && $perbaikan->laporanKerusakan->keluhanList->count()) {
            return $perbaikan->laporanKerusakan->keluhanList
                ->map(fn($k) => $k->nama_keluhan ?? $k->keluhan ?? '-')
                ->join(', ');
        }
        return $perbaikan->deskripsi_keluhan ?? '-';
    }

    protected function resolveTindakan(RiwayatPerbaikan $perbaikan): string
    {
        if ($perbaikan->detailTindakan->count()) {
            return $perbaikan->detailTindakan
                ->map(fn($d) => $d->masterTindakan->nama_tindakan ?? '-')
                ->unique()->join(', ');
        }
        return $perbaikan->tindakan_perbaikan ?? '-';
    }

    protected function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_COLOR]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ];
    }

    protected function dataRowStyle(string $lastCol, int $maxRow = 1000): array
    {
        return [
            "A2:{$lastCol}{$maxRow}" => [
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'DDDDDD'],
                    ],
                ],
            ],
        ];
    }
}