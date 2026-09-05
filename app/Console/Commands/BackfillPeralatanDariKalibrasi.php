<?php

namespace App\Console\Commands;

use App\Models\Peralatan;
use Illuminate\Console\Command;

class BackfillPeralatanDariKalibrasi extends Command
{
    /**
     * php artisan kalibrasi:backfill-peralatan
     *
     * Jalankan SEKALI setelah KalibrasiObserver dipasang, untuk merapikan
     * data kalibrasi yang sudah lebih dulu ada sebelum Observer aktif
     * (misal dari bulk input / import Excel yang sudah dilakukan).
     * Setelah ini, Observer yang akan menjaga sinkronnya ke depan.
     */
    protected $signature = 'kalibrasi:backfill-peralatan';

    protected $description = 'Sinkronkan ulang calibration_number & kapasitas peralatan dari data kalibrasi terbaru yang sudah ada';

    public function handle(): int
    {
        $peralatanList = Peralatan::has('kalibrasi')->with('kalibrasiTerakhir')->get();

        $this->info("Ditemukan {$peralatanList->count()} peralatan yang punya histori kalibrasi.");

        $updated = 0;

        foreach ($peralatanList as $peralatan) {
            $terbaru = $peralatan->kalibrasiTerakhir;

            if (!$terbaru) {
                continue;
            }

            $data = [
                'calibration_number' => $terbaru->calibration_number,
            ];

            // FIX: 'kapasitas' sudah bukan kolom sendiri di tabel peralatan lagi,
            // sekarang bagian dari JSON 'spesifikasi'. Ambil dulu spesifikasi yang
            // sudah ada lalu di-merge, supaya field lain (mis. 'range' pada
            // Thermometer) tidak ikut tertimpa/hilang.
            $kapasitasLama = null;
            if ($terbaru->beda_maksimum) {
                $spesifikasi = $peralatan->spesifikasi ?? [];
                $kapasitasLama = $spesifikasi['kapasitas'] ?? null;
                $spesifikasi['kapasitas'] = $terbaru->beda_maksimum;
                $data['spesifikasi'] = $spesifikasi;
            }

            $peralatan->update($data);
            $updated++;

            $this->line("✓ {$peralatan->kode_asset} → cert: " . ($terbaru->calibration_number ?? '-')
                . ' | kapasitas: ' . ($terbaru->beda_maksimum ?? '(tidak diubah)'));
        }

        $this->info("Selesai. {$updated} peralatan diperbarui.");

        return self::SUCCESS;
    }
}