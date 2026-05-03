<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MigrasiDataLamaSeeder
 *
 * Tugasnya:
 * 1. Ambil semua data dari master_keluhan & master_tindakan awal (seed default)
 * 2. Untuk setiap record riwayat_perbaikan LAMA (laporan_kerusakan_id masih null):
 *    a. Buat record laporan_kerusakan dari field lama
 *    b. Cocokkan deskripsi_keluhan ke master_keluhan (atau buat baru)
 *    c. Buat laporan_kerusakan_keluhan (pivot)
 *    d. Update riwayat_perbaikan.laporan_kerusakan_id
 *    e. Jika ada perbaikan_eksternal, buat detail_tindakan_perbaikan
 *    f. Jika ada tindakan_perbaikan, buat detail_tindakan_perbaikan
 */
class MigrasiDataLamaSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Seed master_keluhan default ───────────────────────────────────
        $keluhanDefault = [
            'Fluktuasi angka',
            'Tidak akurat',
            'Display mati',
            'Angka tidak stabil',
            'Timbangan tidak menyala',
            'Hasil timbang berbeda tiap pengukuran',
            'Kerusakan fisik (kabel/body)',
        ];

        foreach ($keluhanDefault as $nama) {
            DB::table('master_keluhan')->insertOrIgnore([
                'nama_keluhan' => $nama,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ── 2. Seed master_tindakan default ──────────────────────────────────
        $tindakanDefault = [
            'Kalibrasi ulang',
            'Pembersihan sensor',
            'Penggantian baterai',
            'Perbaikan kabel',
            'Perbaikan internal oleh teknisi',
            'Dikirim ke vendor eksternal',
            'Penggantian komponen',
            'Reset sistem',
        ];

        foreach ($tindakanDefault as $nama) {
            DB::table('master_tindakan')->insertOrIgnore([
                'nama_tindakan' => $nama,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // ── 3. Migrasi data riwayat_perbaikan lama ───────────────────────────
        $riwayatLama = DB::table('riwayat_perbaikan')
            ->whereNull('laporan_kerusakan_id')
            ->get();

        foreach ($riwayatLama as $riwayat) {

            // a. Buat laporan_kerusakan dari data lama
            $laporanId = DB::table('laporan_kerusakan')->insertGetId([
                'timbangan_id'         => $riwayat->timbangan_id,
                'riwayat_penggunaan_id'=> null, // data lama tidak punya referensi ini
                'line_asal'            => $riwayat->line_sebelumnya,
                'pic_pelapor'          => $riwayat->penggunaan_terakhir,
                'tanggal_laporan'      => $riwayat->tanggal_masuk_lab,
                'keterangan_tambahan'  => null,
                // Jika perbaikan sudah selesai → status laporan Selesai, else Diproses
                'status'               => $riwayat->status_perbaikan === 'Selesai' ? 'Selesai' : 'Diproses',
                'created_at'           => $riwayat->created_at,
                'updated_at'           => $riwayat->updated_at,
            ]);

            // b. Cocokkan deskripsi_keluhan ke master_keluhan
            //    Normalisasi: lowercase, trim, cocokkan sebagian
            if (!empty($riwayat->deskripsi_keluhan)) {
                $deskripsi  = strtolower(trim($riwayat->deskripsi_keluhan));
                $keluhanId  = null;

                // Coba cocokkan ke master_keluhan yang ada
                $masterKeluhan = DB::table('master_keluhan')->get();
                foreach ($masterKeluhan as $mk) {
                    if (str_contains($deskripsi, strtolower(trim($mk->nama_keluhan)))
                        || str_contains(strtolower(trim($mk->nama_keluhan)), $deskripsi)) {
                        $keluhanId = $mk->id;
                        break;
                    }
                }

                // Jika tidak cocok, buat master_keluhan baru dari teks asli
                if (!$keluhanId) {
                    $keluhanId = DB::table('master_keluhan')->insertGetId([
                        'nama_keluhan' => ucfirst($riwayat->deskripsi_keluhan),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                // c. Buat pivot laporan_kerusakan_keluhan
                DB::table('laporan_kerusakan_keluhan')->insert([
                    'laporan_kerusakan_id' => $laporanId,
                    'master_keluhan_id'    => $keluhanId,
                    'created_at'           => $riwayat->created_at,
                    'updated_at'           => $riwayat->updated_at,
                ]);
            }

            // d. Update riwayat_perbaikan → tautkan ke laporan_kerusakan baru
            DB::table('riwayat_perbaikan')
                ->where('id', $riwayat->id)
                ->update(['laporan_kerusakan_id' => $laporanId]);

            // e. Jika ada tindakan_perbaikan, buat detail_tindakan_perbaikan
            if (!empty($riwayat->tindakan_perbaikan) && $riwayat->tindakan_perbaikan !== 'abcd') {
                $tindakanNama = trim($riwayat->tindakan_perbaikan);
                $tindakanId   = $this->cariAtauBuatTindakan($tindakanNama);

                DB::table('detail_tindakan_perbaikan')->insert([
                    'riwayat_perbaikan_id' => $riwayat->id,
                    'master_tindakan_id'   => $tindakanId,
                    'tanggal_tindakan'     => $riwayat->tanggal_masuk_lab,
                    'catatan'              => null,
                    'created_at'           => $riwayat->created_at,
                    'updated_at'           => $riwayat->updated_at,
                ]);
            }

            // f. Jika ada perbaikan_eksternal (bukan 'tidak ada' atau 'defg')
            if (!empty($riwayat->perbaikan_eksternal)
                && !in_array(strtolower(trim($riwayat->perbaikan_eksternal)), ['tidak ada', 'defg', '-'])) {

                $tindakanEksternalId = $this->cariAtauBuatTindakan('Dikirim ke vendor eksternal');

                DB::table('detail_tindakan_perbaikan')->insert([
                    'riwayat_perbaikan_id' => $riwayat->id,
                    'master_tindakan_id'   => $tindakanEksternalId,
                    'tanggal_tindakan'     => $riwayat->tanggal_masuk_lab,
                    'catatan'              => $riwayat->perbaikan_eksternal,
                    'created_at'           => $riwayat->created_at,
                    'updated_at'           => $riwayat->updated_at,
                ]);
            }
        }

        $this->command->info('✅ Migrasi data lama selesai.');
        $this->command->info('   - Master keluhan: ' . DB::table('master_keluhan')->count() . ' data');
        $this->command->info('   - Master tindakan: ' . DB::table('master_tindakan')->count() . ' data');
        $this->command->info('   - Laporan kerusakan baru: ' . DB::table('laporan_kerusakan')->count() . ' data');
        $this->command->info('   - Detail tindakan: ' . DB::table('detail_tindakan_perbaikan')->count() . ' data');
    }

    /**
     * Cari master_tindakan berdasarkan nama (cocok sebagian),
     * atau buat baru jika tidak ditemukan.
     */
    private function cariAtauBuatTindakan(string $nama): int
    {
        $namaLower = strtolower(trim($nama));
        $master    = DB::table('master_tindakan')->get();

        foreach ($master as $mt) {
            if (str_contains($namaLower, strtolower(trim($mt->nama_tindakan)))
                || str_contains(strtolower(trim($mt->nama_tindakan)), $namaLower)) {
                return $mt->id;
            }
        }

        return DB::table('master_tindakan')->insertGetId([
            'nama_tindakan' => ucfirst($nama),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}