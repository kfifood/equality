<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MigrasiDataLamaSeeder
 *
 * Disesuaikan dengan database production klab dump 04-05-2026.
 *
 * Yang dilakukan seeder ini:
 * 1. Seed master_keluhan dari kategori keluhan yang ada di data riwayat_perbaikan
 * 2. Seed master_tindakan dari tindakan yang tercatat di data riwayat_perbaikan
 * 3. Untuk setiap record riwayat_perbaikan lama:
 *    a. Buat laporan_kerusakan dari field lama (line_sebelumnya, penggunaan_terakhir, dll)
 *    b. Petakan deskripsi_keluhan → master_keluhan (cocokkan kata kunci)
 *    c. Buat laporan_kerusakan_keluhan (pivot)
 *    d. Update riwayat_perbaikan.laporan_kerusakan_id
 *    e. Petakan tindakan_perbaikan & perbaikan_eksternal → detail_tindakan_perbaikan
 */
class MigrasiDataLamaSeeder extends Seeder
{
    // ── Master Keluhan yang akan di-seed ──────────────────────────────────────
    // Disusun berdasarkan analisis deskripsi_keluhan di database production
    private array $masterKeluhan = [
        'Fluktuasi',
        'Angka jauh dari range',
        'Tidak bisa dikalibrasi',
        'Kalibrasi',
        'Display error / digit berjalan',
        'Tidak bisa di-charge',
        'Tombol / keypad rusak',
        'Adaptor error',
        'Baterai drop / lemah',
        'IC rusak',
        'Loadcell error',
        'Kerusakan fisik (karat / patah)',
        'Error sistem (kode error)',
        'Tidak bisa menyala',
    ];

    // ── Master Tindakan yang akan di-seed ─────────────────────────────────────
    // Disusun berdasarkan analisis tindakan_perbaikan & perbaikan_eksternal
    private array $masterTindakan = [
        'Kalibrasi ulang',
        'Cleaning / pembersihan',
        'Ganti loadcell',
        'Ganti baterai',
        'Ganti keypad / tombol',
        'Ganti adaptor charger',
        'Ganti IC power',
        'Ganti display',
        'Perbaikan kabel / sambungan',
        'Perbaikan mekanik (las / patah)',
        'Perbaikan internal oleh teknisi',
        'Dikirim ke vendor eksternal',
        'Adjusment / setting ulang',
        'Pengeringan komponen',
        'Ganti komponen lain',
    ];

    // ── Peta kata kunci deskripsi_keluhan → index master_keluhan ─────────────
    // Key = kata kunci (lowercase), Value = nama di master_keluhan
    private array $petaKeluhan = [
        'fluktuasi'        => 'Fluktuasi',
        'fluktu'           => 'Fluktuasi',
        'jauh dari range'  => 'Angka jauh dari range',
        'angka jauh'       => 'Angka jauh dari range',
        'tidak bisa dikalibrasi' => 'Tidak bisa dikalibrasi',
        'kalibrasi'        => 'Kalibrasi',
        'display'          => 'Display error / digit berjalan',
        'digit berjalan'   => 'Display error / digit berjalan',
        'error0'           => 'Display error / digit berjalan',
        'erorr0'           => 'Display error / digit berjalan',
        'tidak keluar digit' => 'Display error / digit berjalan',
        'tidak bisa di charge' => 'Tidak bisa di-charge',
        'tidak bisa dicas' => 'Tidak bisa di-charge',
        'tidak bisa nyala' => 'Tidak bisa menyala',
        'lowbat'           => 'Baterai drop / lemah',
        'baterai drop'     => 'Baterai drop / lemah',
        'indikasi baterai' => 'Baterai drop / lemah',
        'tombol'           => 'Tombol / keypad rusak',
        'keypad'           => 'Tombol / keypad rusak',
        'adaptor'          => 'Adaptor error',
        'ic rusak'         => 'IC rusak',
        'loadcell eror'    => 'Loadcell error',
        'loadcell error'   => 'Loadcell error',
        'berkarat'         => 'Kerusakan fisik (karat / patah)',
        'karat'            => 'Kerusakan fisik (karat / patah)',
        'patah'            => 'Kerusakan fisik (karat / patah)',
        'error 9'          => 'Error sistem (kode error)',
        'error01'          => 'Display error / digit berjalan',
        'eror'             => 'Fluktuasi',  // default fallback untuk "eror" umum
    ];

    // ── Peta kata kunci tindakan → index master_tindakan ─────────────────────
    private array $petaTindakan = [
        'kalibrasi'        => 'Kalibrasi ulang',
        'terkalibrasi'     => 'Kalibrasi ulang',
        'adjusment'        => 'Adjusment / setting ulang',
        'adjustment'       => 'Adjusment / setting ulang',
        'cleaning'         => 'Cleaning / pembersihan',
        'cleanig'          => 'Cleaning / pembersihan',
        'bersih'           => 'Cleaning / pembersihan',
        'ganti loadcell'   => 'Ganti loadcell',
        'ganti baterai'    => 'Ganti baterai',
        'baterai'          => 'Ganti baterai',
        'loacell'          => 'Ganti loadcell',
        'loadcell'         => 'Ganti loadcell',
        'ganti keypad'     => 'Ganti keypad / tombol',
        'keypad'           => 'Ganti keypad / tombol',
        'keypath'          => 'Ganti keypad / tombol',
        'ganti adaptor'    => 'Ganti adaptor charger',
        'adaptor'          => 'Ganti adaptor charger',
        'charger'          => 'Ganti adaptor charger',
        'ganti ic'         => 'Ganti IC power',
        'ic power'         => 'Ganti IC power',
        'ganti display'    => 'Ganti display',
        'display set'      => 'Ganti display',
        'las'              => 'Perbaikan mekanik (las / patah)',
        'kabel'            => 'Perbaikan kabel / sambungan',
        'sambungan'        => 'Perbaikan kabel / sambungan',
        'teknik'           => 'Perbaikan internal oleh teknisi',
        'by teknik'        => 'Perbaikan internal oleh teknisi',
        'pengeringan'      => 'Pengeringan komponen',
        'eksternal'        => 'Dikirim ke vendor eksternal',
        'servis'           => 'Dikirim ke vendor eksternal',
        'ganti komponen'   => 'Ganti komponen lain',
        'repair'           => 'Perbaikan kabel / sambungan',
    ];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ── 1. Seed master_keluhan ────────────────────────────────────────────
        $this->command->info('Seeding master_keluhan...');
        foreach ($this->masterKeluhan as $nama) {
            DB::table('master_keluhan')->insertOrIgnore([
                'nama_keluhan' => $nama,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
        $this->command->info('  ✓ ' . count($this->masterKeluhan) . ' keluhan di-seed.');

        // ── 2. Seed master_tindakan ───────────────────────────────────────────
        $this->command->info('Seeding master_tindakan...');
        foreach ($this->masterTindakan as $nama) {
            DB::table('master_tindakan')->insertOrIgnore([
                'nama_tindakan' => $nama,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
        $this->command->info('  ✓ ' . count($this->masterTindakan) . ' tindakan di-seed.');

        // Cache ID master keluhan & tindakan
        $keluhanMap  = DB::table('master_keluhan')->pluck('id', 'nama_keluhan')->toArray();
        $tindakanMap = DB::table('master_tindakan')->pluck('id', 'nama_tindakan')->toArray();

        // ── 3. Migrasi riwayat_perbaikan lama ────────────────────────────────
        $this->command->info('Migrasi data riwayat_perbaikan...');

        $riwayatLama = DB::table('riwayat_perbaikan')
            ->whereNull('laporan_kerusakan_id')
            ->orderBy('id')
            ->get();

        $this->command->info("  Ditemukan {$riwayatLama->count()} record untuk dimigrasi.");

        $berhasil = 0;
        $gagal    = 0;

        foreach ($riwayatLama as $riwayat) {
            try {
                // a. Tentukan status laporan dari status_perbaikan
                $statusLaporan = match ($riwayat->status_perbaikan) {
                    'Selesai'           => 'Selesai',
                    'Dikirim Eksternal' => 'Diproses',
                    'Perbaikan Internal'=> 'Diproses',
                    default             => 'Diproses', // Masuk Lab / lainnya
                };

                // b. Buat laporan_kerusakan
                $laporanId = DB::table('laporan_kerusakan')->insertGetId([
                    'timbangan_id'          => $riwayat->timbangan_id,
                    'riwayat_penggunaan_id' => null, // data lama tidak punya link ini
                    'line_asal'             => $riwayat->line_sebelumnya,
                    'pic_pelapor'           => $riwayat->penggunaan_terakhir,
                    'tanggal_laporan'       => $riwayat->tanggal_masuk_lab,
                    'keterangan_tambahan'   => null,
                    'status'                => $statusLaporan,
                    'created_at'            => $riwayat->created_at,
                    'updated_at'            => $riwayat->updated_at,
                ]);

                // c. Petakan deskripsi_keluhan → master_keluhan
                $keluhanIds = $this->petakanKeluhan(
                    $riwayat->deskripsi_keluhan,
                    $keluhanMap
                );

                // Jika tidak ada yang cocok, fallback ke 'Fluktuasi'
                if (empty($keluhanIds)) {
                    $keluhanIds = [$keluhanMap['Fluktuasi']];
                }

                // d. Insert pivot laporan_kerusakan_keluhan
                foreach ($keluhanIds as $keluhanId) {
                    DB::table('laporan_kerusakan_keluhan')->insertOrIgnore([
                        'laporan_kerusakan_id' => $laporanId,
                        'master_keluhan_id'    => $keluhanId,
                        'created_at'           => $riwayat->created_at,
                        'updated_at'           => $riwayat->updated_at,
                    ]);
                }

                // e. Update riwayat_perbaikan → tautkan ke laporan_kerusakan
                DB::table('riwayat_perbaikan')
                    ->where('id', $riwayat->id)
                    ->update(['laporan_kerusakan_id' => $laporanId]);

                // f. Petakan tindakan_perbaikan → detail_tindakan_perbaikan
                if (!empty($riwayat->tindakan_perbaikan)) {
                    $tindakanIds = $this->petakanTindakan(
                        $riwayat->tindakan_perbaikan,
                        $tindakanMap
                    );

                    foreach ($tindakanIds as $tindakanId) {
                        DB::table('detail_tindakan_perbaikan')->insertOrIgnore([
                            'riwayat_perbaikan_id' => $riwayat->id,
                            'master_tindakan_id'   => $tindakanId,
                            'tanggal_tindakan'     => $riwayat->tanggal_selesai_perbaikan
                                                      ?? $riwayat->tanggal_masuk_lab,
                            'catatan'              => null,
                            'created_at'           => $riwayat->updated_at,
                            'updated_at'           => $riwayat->updated_at,
                        ]);
                    }
                }

                // g. Petakan perbaikan_eksternal → detail_tindakan_perbaikan
                //    (khusus untuk status Dikirim Eksternal)
                if (!empty($riwayat->perbaikan_eksternal)) {
                    $tindakanEksternalId = $tindakanMap['Dikirim ke vendor eksternal'];

                    // Hindari duplikat jika sudah di-insert dari tindakan_perbaikan
                    $sudahAda = DB::table('detail_tindakan_perbaikan')
                        ->where('riwayat_perbaikan_id', $riwayat->id)
                        ->where('master_tindakan_id', $tindakanEksternalId)
                        ->exists();

                    if (!$sudahAda) {
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

                $berhasil++;

            } catch (\Exception $e) {
                $gagal++;
                $this->command->warn(
                    "  ⚠ Gagal migrasi riwayat_perbaikan ID {$riwayat->id}: " . $e->getMessage()
                );
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Ringkasan ─────────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ Migrasi selesai!');
        $this->command->info('   master_keluhan   : ' . DB::table('master_keluhan')->count() . ' data');
        $this->command->info('   master_tindakan  : ' . DB::table('master_tindakan')->count() . ' data');
        $this->command->info('   laporan_kerusakan: ' . DB::table('laporan_kerusakan')->count() . ' data');
        $this->command->info('   detail_tindakan  : ' . DB::table('detail_tindakan_perbaikan')->count() . ' data');
        $this->command->info("   Berhasil: {$berhasil} | Gagal: {$gagal}");

        if ($gagal > 0) {
            $this->command->warn('   Cek log Laravel untuk detail error.');
        }
    }

    // ── Helper: Petakan teks keluhan ke ID master_keluhan ────────────────────

    private function petakanKeluhan(string $deskripsi, array $keluhanMap): array
    {
        $deskripsiLower = strtolower($deskripsi);
        $hasilIds       = [];

        // Cek setiap kata kunci di peta
        foreach ($this->petaKeluhan as $kataKunci => $namaKeluhan) {
            if (str_contains($deskripsiLower, $kataKunci)) {
                if (isset($keluhanMap[$namaKeluhan])) {
                    $id = $keluhanMap[$namaKeluhan];
                    if (!in_array($id, $hasilIds)) {
                        $hasilIds[] = $id;
                    }
                }
            }
        }

        // Khusus: "tidak bisa di charge" / "tidak bisa dicas"
        if (str_contains($deskripsiLower, 'charge') || str_contains($deskripsiLower, 'dicas')) {
            $id = $keluhanMap['Tidak bisa di-charge'] ?? null;
            if ($id && !in_array($id, $hasilIds)) {
                $hasilIds[] = $id;
            }
        }

        // Khusus: "angka jauh dari range" atau "jauh dari range"
        if (str_contains($deskripsiLower, 'range') && str_contains($deskripsiLower, 'jauh')) {
            $id = $keluhanMap['Angka jauh dari range'] ?? null;
            if ($id && !in_array($id, $hasilIds)) {
                $hasilIds[] = $id;
            }
        }

        // Khusus: tombol (#) atau keypad
        if (str_contains($deskripsiLower, 'tombol') || str_contains($deskripsiLower, 'keypad')) {
            $id = $keluhanMap['Tombol / keypad rusak'] ?? null;
            if ($id && !in_array($id, $hasilIds)) {
                $hasilIds[] = $id;
            }
        }

        // Batasi maksimal 3 keluhan per laporan agar tidak overly broad
        return array_slice($hasilIds, 0, 3);
    }

    // ── Helper: Petakan teks tindakan ke ID master_tindakan ──────────────────

    private function petakanTindakan(string $tindakanTeks, array $tindakanMap): array
    {
        // Pisah per baris (karena data lama pakai \r\n sebagai separator)
        $baris   = preg_split('/[\r\n,]+/', $tindakanTeks);
        $hasilIds = [];

        foreach ($baris as $brs) {
            $brsLower = strtolower(trim($brs));
            if (empty($brsLower)) continue;

            $cocok = false;
            foreach ($this->petaTindakan as $kataKunci => $namaTindakan) {
                if (str_contains($brsLower, $kataKunci)) {
                    if (isset($tindakanMap[$namaTindakan])) {
                        $id = $tindakanMap[$namaTindakan];
                        if (!in_array($id, $hasilIds)) {
                            $hasilIds[] = $id;
                        }
                        $cocok = true;
                        break;
                    }
                }
            }

            // Jika tidak ada peta yang cocok, masukkan ke "Perbaikan internal"
            if (!$cocok && !empty(trim($brs))) {
                // Hanya jika baris ini bukan sekadar catatan
                if (strlen(trim($brs)) > 3) {
                    $id = $tindakanMap['Perbaikan internal oleh teknisi'] ?? null;
                    if ($id && !in_array($id, $hasilIds)) {
                        $hasilIds[] = $id;
                    }
                }
            }
        }

        return $hasilIds;
    }
}