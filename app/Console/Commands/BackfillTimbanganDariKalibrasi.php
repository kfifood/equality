<?php

namespace App\Console\Commands;

use App\Models\Timbangan;
use Illuminate\Console\Command;

class BackfillTimbanganDariKalibrasi extends Command
{
    /**
     * php artisan kalibrasi:backfill-timbangan
     *
     * Jalankan SEKALI setelah KalibrasiObserver dipasang, untuk merapikan
     * data kalibrasi yang sudah lebih dulu ada sebelum Observer aktif
     * (misal dari bulk input / import Excel yang sudah dilakukan).
     * Setelah ini, Observer yang akan menjaga sinkronnya ke depan.
     */
    protected $signature = 'kalibrasi:backfill-timbangan';

    protected $description = 'Sinkronkan ulang certificate_number & kapasitas timbangan dari data kalibrasi terbaru yang sudah ada';

    public function handle(): int
    {
        $timbanganList = Timbangan::has('kalibrasi')->with('kalibrasiTerakhir')->get();

        $this->info("Ditemukan {$timbanganList->count()} timbangan yang punya histori kalibrasi.");

        $updated = 0;

        foreach ($timbanganList as $timbangan) {
            $terbaru = $timbangan->kalibrasiTerakhir;

            if (!$terbaru) {
                continue;
            }

            $data = [
                'certificate_number' => $terbaru->certificate_number,
            ];

            if ($terbaru->beda_maksimum) {
                $data['kapasitas'] = $terbaru->beda_maksimum;
            }

            $timbangan->update($data);
            $updated++;

            $this->line("✓ {$timbangan->kode_asset} → cert: " . ($terbaru->certificate_number ?? '-')
                . ' | kapasitas: ' . ($data['kapasitas'] ?? '(tidak diubah)'));
        }

        $this->info("Selesai. {$updated} timbangan diperbarui.");

        return self::SUCCESS;
    }
}