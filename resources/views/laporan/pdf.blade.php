<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peralatan - {{ $periode }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.45;
            color: #2d2d2d;
            /* Margin halaman: DomPDF menerapkan padding body sebagai margin */
            padding: 30px 36px 30px 36px;
        }

        /* ── Header ──────────────────────────────────────────────── */
        .header {
            text-align: center;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #555;
        }
        .header h1 {
            font-size: 15px;
            color: #222;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .header .sub {
            font-size: 9.5px;
            color: #666;
            margin-top: 4px;
        }

        /* ── Summary bar (satu baris) ─────────────────────────────── */
        .summary-bar {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #ccc;
        }
        .summary-bar .s-cell {
            display: table-cell;
            text-align: center;
            padding: 7px 5px;
            border-right: 1px solid #ccc;
            vertical-align: middle;
            background: #f5f5f5;
        }
        .summary-bar .s-cell:last-child { border-right: none; }
        .summary-bar .s-val {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            display: block;
        }
        .summary-bar .s-val.green  { color: #2e7d32; }
        .summary-bar .s-val.red    { color: #c62828; }
        .summary-bar .s-val.orange { color: #e65100; }
        .summary-bar .s-val.teal   { color: #00695c; }
        .summary-bar .s-lbl {
            font-size: 8.5px;
            color: #666;
            display: block;
            margin-top: 1px;
        }

        /* ── Section title ────────────────────────────────────────── */
        .section-title {
            background: #e8e8e8;
            color: #222;
            padding: 4px 8px;
            margin: 14px 0 6px;
            font-size: 10px;
            font-weight: bold;
            border-left: 3px solid #888;
            letter-spacing: 0.4px;
        }

        /* ── Tables ───────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        th {
            background: #555;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 4px 6px;
            border: 1px solid #d4d4d4;
            vertical-align: top;
        }
        tr:nth-child(even) td { background: #f9f9f9; }

        /* ── Badges ───────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-warning { background: #fff8e1; color: #e65100; border: 1px solid #ffcc80; }
        .badge-danger  { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .badge-info    { background: #f0f0f0; color: #444;    border: 1px solid #bdbdbd; }

        /* ── Misc ─────────────────────────────────────────────────── */
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .page-break  { page-break-after: always; }
        .col-wrap    { word-wrap: break-word; }

        .footer {
            text-align: center;
            margin-top: 22px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            font-size: 8.5px;
            color: #999;
        }
    </style>
</head>
<body>

    {{-- ── Header ───────────────────────────────────────────────────── --}}
    <div class="header">
        <h1>Laporan Peralatan</h1>
        <div class="sub">
            Periode: <strong>{{ $periode }}</strong>
            &nbsp;&bull;&nbsp;
            Line: <strong>{{ $filterLine }}</strong>
            &nbsp;&bull;&nbsp;
            Dicetak: {{ $tanggalCetak }}
        </div>
    </div>

    {{-- ── Summary — satu baris ─────────────────────────────────────── --}}
    <div class="summary-bar">
        <div class="s-cell">
            <span class="s-val">{{ $statistik['total'] }}</span>
            <span class="s-lbl">Total Alat</span>
        </div>
        <div class="s-cell">
            <span class="s-val green">{{ $statistik['baik'] }}</span>
            <span class="s-lbl">Baik ({{ $statistik['persentase_baik'] }}%)</span>
        </div>
        <div class="s-cell">
            <span class="s-val red">{{ $statistik['rusak'] }}</span>
            <span class="s-lbl">Rusak</span>
        </div>
        <div class="s-cell">
            <span class="s-val orange">{{ $statistik['perbaikan'] }}</span>
            <span class="s-lbl">Dalam Perbaikan</span>
        </div>
        <div class="s-cell">
            <span class="s-val teal">{{ $penggunaanPeriod }}</span>
            <span class="s-lbl">Penggunaan Bln Ini</span>
        </div>
        <div class="s-cell">
            <span class="s-val orange">{{ $perbaikanPeriod }}</span>
            <span class="s-lbl">Perbaikan Bln Ini</span>
        </div>
        @if($filterLine === 'Semua Line')
        <div class="s-cell">
            <span class="s-val">{{ $statistik['di_lab'] }}</span>
            <span class="s-lbl">Di Lab</span>
        </div>
        <div class="s-cell">
            <span class="s-val">{{ $statistik['di_line'] }}</span>
            <span class="s-lbl">Di Line</span>
        </div>
        @endif
    </div>

    {{-- ── Distribusi per Line ───────────────────────────────────────── --}}
    <div class="section-title">DISTRIBUSI PER LINE</div>
    <table>
        <thead>
            <tr>
                <th>Line</th>
                <th class="text-center" style="width:70px">Jumlah</th>
                <th class="text-center" style="width:80px">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distribusiLine as $dist)
            <tr>
                <td>{{ $dist->status_line }}</td>
                <td class="text-center">{{ $dist->total }}</td>
                <td class="text-center">
                    {{ $statistik['di_line'] > 0 ? round(($dist->total / $statistik['di_line']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center">Tidak ada peralatan di line</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Riwayat Penggunaan ───────────────────────────────────────── --}}
    <div class="section-title">RIWAYAT PENGGUNAAN TERBARU</div>
    <table>
        <thead>
            <tr>
                <th style="width:90px">Kode Asset</th>
                <th>Merk / Seri</th>
                <th style="width:80px">Line Tujuan</th>
                <th style="width:65px">Tanggal</th>
                <th style="width:80px">PIC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPenggunaan as $penggunaan)
            <tr>
                <td>{{ $penggunaan->peralatan->kode_asset ?? '-' }}</td>
                <td class="col-wrap">{{ $penggunaan->peralatan->merk_tipe_lengkap ?? '-' }}</td>
                <td>{{ $penggunaan->line_tujuan }}</td>
                <td>{{ \Carbon\Carbon::parse($penggunaan->tanggal_pemakaian)->format('d/m/Y') }}</td>
                <td>{{ $penggunaan->pic ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">Tidak ada data penggunaan</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Riwayat Perbaikan ────────────────────────────────────────── --}}
    <div class="section-title">RIWAYAT PERBAIKAN TERBARU</div>
    <table>
        <thead>
            <tr>
                <th style="width:85px">Kode Asset</th>
                <th style="width:75px">Line Sebelum</th>
                <th style="width:60px">Tgl Masuk</th>
                <th style="width:60px">Tgl Selesai</th>
                <th>Keluhan</th>
                <th>Tindakan Perbaikan</th>
                <th style="width:80px">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPerbaikan as $perbaikan)
            @php
                /* Keluhan */
                if ($perbaikan->laporanKerusakan && $perbaikan->laporanKerusakan->keluhanList->count()) {
                    $keluhanText = $perbaikan->laporanKerusakan->keluhanList
                        ->map(fn($k) => $k->nama_keluhan ?? $k->keluhan ?? '-')
                        ->join(', ');
                } else {
                    $keluhanText = $perbaikan->deskripsi_keluhan ?? '-';
                }

                /* Tindakan */
                if ($perbaikan->relationLoaded('detailTindakan') && $perbaikan->detailTindakan->count()) {
                    $tindakanText = $perbaikan->detailTindakan
                        ->map(fn($d) => $d->masterTindakan->nama_tindakan ?? '-')
                        ->unique()->join(', ');
                } else {
                    $tindakanText = $perbaikan->tindakan_perbaikan ?? '-';
                }

                $badgeClass = match($perbaikan->status_perbaikan) {
                    'Selesai'             => 'badge-success',
                    'Perbaikan Internal',
                    'Menunggu Penanganan' => 'badge-warning',
                    'Masuk Lab',
                    'Dikirim Eksternal'   => 'badge-info',
                    default               => 'badge-info',
                };
            @endphp
            <tr>
                <td>{{ $perbaikan->peralatan->kode_asset ?? '-' }}</td>
                <td>{{ $perbaikan->line_sebelumnya ?? '-' }}</td>
                <td>{{ $perbaikan->tanggal_masuk_lab ? \Carbon\Carbon::parse($perbaikan->tanggal_masuk_lab)->format('d/m/Y') : '-' }}</td>
                <td>{{ $perbaikan->tanggal_selesai_perbaikan ? \Carbon\Carbon::parse($perbaikan->tanggal_selesai_perbaikan)->format('d/m/Y') : '-' }}</td>
                <td class="col-wrap">{{ $keluhanText }}</td>
                <td class="col-wrap">{{ $tindakanText }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $perbaikan->status_perbaikan }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">Tidak ada data perbaikan</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Data Peralatan (halaman baru) ───────────────────────────── --}}
    <div class="page-break"></div>

    <div class="header" style="margin-bottom:12px;">
        <h1>Data Peralatan — {{ strtoupper($filterLine) }}</h1>
        <div class="sub">Periode: <strong>{{ $periode }}</strong> &bull; Dicetak: {{ $tanggalCetak }}</div>
    </div>

    <div class="section-title">DAFTAR PERALATAN</div>
    <table>
        <thead>
            <tr>
                <th style="width:90px">Kode Asset</th>
                <th>Merk &amp; Seri</th>
                <th style="width:80px">Lokasi Asli</th>
                <th style="width:80px">Lokasi Saat Ini</th>
                <th style="width:75px">Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peralatanList as $alat)
            @php
                $badgeClass = match($alat->kondisi_saat_ini) {
                    'Baik'            => 'badge-success',
                    'Rusak'           => 'badge-danger',
                    'Dalam Perbaikan' => 'badge-warning',
                    default           => 'badge-info',
                };
            @endphp
            <tr>
                <td>{{ $alat->kode_asset }}</td>
                <td class="col-wrap">{{ $alat->merk_tipe_lengkap }}</td>
                <td>{{ $alat->lokasi_asli ?? '-' }}</td>
                <td>{{ $alat->status_line ?: 'Lab' }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $alat->kondisi_saat_ini }}</span></td>
                <td>{{ $alat->status_lengkap }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Manajemen Peralatan QC &mdash;
        {{ config('app.name') }} &mdash; {{ date('Y') }}
    </div>

</body>
</html>