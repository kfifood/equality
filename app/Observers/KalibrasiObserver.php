<?php

namespace App\Observers;

use App\Models\Kalibrasi;
use App\Models\LaporanKerusakan;
use App\Models\MasterKeluhan;
use App\Models\Timbangan;

class KalibrasiObserver
{
    /**
     * Setiap kali data kalibrasi disimpan (create/update),
     * cari kalibrasi TERBARU untuk timbangan tsb lalu tulis balik
     * ke timbangan.certificate_number & kapasitas. Ini menjaga data
     * di tabel timbangan selalu jadi cerminan dari histori kalibrasi,
     * bukan input manual yang terpisah.
     */
    public function saved(Kalibrasi $kalibrasi): void
    {
        $this->sync($kalibrasi->timbangan_id);

        // BARU — kalibrasi gagal harus berdampak nyata ke status alat,
        // bukan cuma tercatat sebagai riwayat.
        if ($kalibrasi->hasil === 'Tidak Lulus') {
            $this->tandaiRusakDariKalibrasiGagal($kalibrasi);
        }
    }

    /**
     * Kalau record kalibrasi dihapus, hitung ulang siapa yang jadi
     * "terbaru" berikutnya (bisa jadi kosong kalau itu satu-satunya).
     */
    public function deleted(Kalibrasi $kalibrasi): void
    {
        $this->sync($kalibrasi->timbangan_id);
    }

    protected function sync(?int $timbanganId): void
    {
        if (!$timbanganId) {
            return;
        }

        $terbaru = Kalibrasi::where('timbangan_id', $timbanganId)
            ->orderByDesc('tanggal_pelaksanaan')
            ->first();

        $data = [
            'certificate_number' => $terbaru?->certificate_number,
        ];

        // 'beda_maksimum' di form/import kalibrasi dipakai untuk menyimpan
        // "Capacity & Deviation" (mis. "150000gr/ 10gr"), bukan cuma toleransi kecil.
        // Ini adalah sumber data kapasitas timbangan yang sebenarnya, jadi kita
        // teruskan ke timbangan.kapasitas juga.
        // Hanya ditimpa kalau ada isinya — supaya kalau ada record kalibrasi baru
        // yang beda_maksimum-nya kosong, data kapasitas yang sudah ada tidak hilang.
        if ($terbaru?->beda_maksimum) {
            $data['kapasitas'] = $terbaru->beda_maksimum;
        }

        Timbangan::whereKey($timbanganId)->update($data);
    }

    /**
     * Kalibrasi 'Tidak Lulus' → buat laporan_kerusakan otomatis (kalau belum
     * ada yang aktif) dan tandai timbangan 'Rusak', supaya masuk alur kerja
     * Perbaikan yang sama seperti laporan manual dari menu Penggunaan.
     */
    protected function tandaiRusakDariKalibrasiGagal(Kalibrasi $kalibrasi): void
    {
        $timbangan = $kalibrasi->timbangan;

        if (!$timbangan) {
            return;
        }

        $sudahAdaLaporanAktif = LaporanKerusakan::where('timbangan_id', $timbangan->id)
            ->whereIn('status', ['Menunggu', 'Diproses'])
            ->exists();

        if (!$sudahAdaLaporanAktif) {
            $laporan = LaporanKerusakan::create([
                'timbangan_id'          => $timbangan->id,
                'riwayat_penggunaan_id' => null,
                'line_asal'             => $kalibrasi->dept_bagian
                                            ?? $timbangan->status_line
                                            ?? $timbangan->lokasi_asli
                                            ?? 'Lab',
                'pic_pelapor'           => $kalibrasi->pelaksana ?? 'Lab Internal',
                'tanggal_laporan'       => $kalibrasi->tanggal_pelaksanaan,
                'keterangan_tambahan'   => 'Otomatis dibuat karena hasil kalibrasi Tidak Lulus (kalibrasi #' . $kalibrasi->id . ').',
                'status'                => 'Menunggu',
            ]);

            $keluhanId = MasterKeluhan::where('nama_keluhan', 'Tidak bisa dikalibrasi')->value('id');
            if ($keluhanId) {
                $laporan->keluhanList()->attach($keluhanId);
            }
        }

        if ($timbangan->kondisi_saat_ini !== 'Rusak') {
            $timbangan->update(['kondisi_saat_ini' => 'Rusak']);
        }
    }
}