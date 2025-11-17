<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Timbangan - {{ $periode }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header .periode {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .card .value {
            font-size: 18px;
            font-weight: bold;
            color: #4361EE;
        }
        .card .label {
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #4361EE;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px;
            margin: 15px 0 10px 0;
            font-weight: bold;
            border-left: 4px solid #4361EE;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TIMBANGAN</h1>
        <div class="periode">Periode: {{ $periode }}</div>
        <div class="periode">Tanggal Cetak: {{ $tanggalCetak }}</div>
    </div>

    <!-- Summary Cards -->
    <div class="section-title">SUMMARY</div>
    <div class="summary-cards">
        <div class="card">
            <div class="value">{{ $statistik['total'] }}</div>
            <div class="label">Total Timbangan</div>
        </div>
        <div class="card">
            <div class="value">{{ $statistik['baik'] }}</div>
            <div class="label">Timbangan Baik</div>
        </div>
        <div class="card">
            <div class="value">{{ $statistik['rusak'] }}</div>
            <div class="label">Timbangan Rusak</div>
        </div>
        <div class="card">
            <div class="value">{{ $statistik['perbaikan'] }}</div>
            <div class="label">Dalam Perbaikan</div>
        </div>
        <div class="card">
            <div class="value">{{ $penggunaanPeriod }}</div>
            <div class="label">Penggunaan</div>
        </div>
        <div class="card">
            <div class="value">{{ $perbaikanPeriod }}</div>
            <div class="label">Perbaikan</div>
        </div>
        <div class="card">
            <div class="value">{{ $statistik['di_lab'] }}</div>
            <div class="label">Di Lab</div>
        </div>
        <div class="card">
            <div class="value">{{ $statistik['di_line'] }}</div>
            <div class="label">Di Line</div>
        </div>
    </div>

    <!-- Distribusi per Line -->
    <div class="section-title">DISTRIBUSI PER LINE</div>
    <table>
        <thead>
            <tr>
                <th>Line</th>
                <th class="text-center">Jumlah</th>
                <th class="text-center">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distribusiLine as $line)
            <tr>
                <td>{{ $line->status_line }}</td>
                <td class="text-center">{{ $line->total }}</td>
                <td class="text-center">
                    {{ $statistik['di_line'] > 0 ? round(($line->total / $statistik['di_line']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">Tidak ada timbangan di line</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Riwayat Penggunaan Terbaru -->
    <div class="section-title">RIWAYAT PENGGUNAAN TERBARU</div>
    <table>
        <thead>
            <tr>
                <th>Kode Asset</th>
                <th>Line Tujuan</th>
                <th>Tanggal</th>
                <th>PIC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPenggunaan as $penggunaan)
            <tr>
                <td>{{ $penggunaan->timbangan->kode_asset ?? '-' }}</td>
                <td>{{ $penggunaan->line_tujuan }}</td>
                <td>{{ \Carbon\Carbon::parse($penggunaan->tanggal_pemakaian)->format('d/m/Y') }}</td>
                <td>{{ $penggunaan->pic ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada data penggunaan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Riwayat Perbaikan Terbaru -->
    <div class="section-title">RIWAYAT PERBAIKAN TERBARU</div>
    <table>
        <thead>
            <tr>
                <th>Kode Asset</th>
                <th>Line Sebelumnya</th>
                <th>Tanggal Masuk</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPerbaikan as $perbaikan)
            <tr>
                <td>{{ $perbaikan->timbangan->kode_asset ?? '-' }}</td>
                <td>{{ $perbaikan->line_sebelumnya }}</td>
                <td>{{ \Carbon\Carbon::parse($perbaikan->tanggal_masuk_lab)->format('d/m/Y') }}</td>
                <td>
                    @php
                        $badgeColor = match($perbaikan->status_perbaikan) {
                            'Masuk Lab' => 'badge-info',
                            'Dalam Perbaikan' => 'badge-warning',
                            'Selesai' => 'badge-success',
                            'Dikirim Eksternal' => 'badge-info',
                            default => 'badge-info'
                        };
                    @endphp
                    <span class="badge {{ $badgeColor }}">{{ $perbaikan->status_perbaikan }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada data perbaikan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Data Timbangan -->
    <div class="page-break"></div>
    <div class="section-title">DATA TIMBANGAN</div>
    <table>
        <thead>
            <tr>
                <th>Kode Asset</th>
                <th>Merk & Seri</th>
                <th>Lokasi</th>
                <th>Kondisi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($timbanganList as $timbangan)
            <tr>
                <td>{{ $timbangan->kode_asset }}</td>
                <td>{{ $timbangan->merk_tipe_no_seri }}</td>
                <td>{{ $timbangan->status_line ?: 'Lab' }}</td>
                <td>
                    @php
                        $badgeColor = match($timbangan->kondisi_saat_ini) {
                            'Baik' => 'badge-success',
                            'Rusak' => 'badge-danger',
                            'Dalam Perbaikan' => 'badge-warning',
                            default => 'badge-info'
                        };
                    @endphp
                    <span class="badge {{ $badgeColor }}">{{ $timbangan->kondisi_saat_ini }}</span>
                </td>
                <td>{{ $timbangan->getStatusLengkapAttribute() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Sistem Manajemen Timbangan<br>
        {{ config('app.name') }} - {{ date('Y') }}
    </div>
</body>
</html>