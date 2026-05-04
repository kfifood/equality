{{--
    resources/views/kalibrasi/sticker.blade.php
    Halaman cetak sticker kalibrasi — satuan & batch
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Sticker Kalibrasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* Sticker: rasio 2:1 */
            --sticker-w: 180mm;
            --sticker-h: 90mm;
            /* Lebar kolom kiri (logo & label) — sejajar */
            --col-left: 32%;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #dde3f0;
            color: #000;
        }

        /* ── CONTROL PANEL (layar saja) ── */
        .control-panel {
            background: #fff;
            border-bottom: 3px solid #4361EE;
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 16px rgba(67,97,238,.15);
        }
        .control-panel h1 {
            font-size: 1rem;
            font-weight: 800;
            color: #4361EE;
        }
        .control-panel h1 span {
            font-weight: 400;
            color: #888;
            font-size: 0.82rem;
            margin-left: 8px;
        }
        .btn-print {
            background: #4361EE;
            color: #fff;
            border: none;
            padding: 9px 22px;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .btn-print:hover { background: #2d47c4; }
        .btn-back {
            background: #f1f3f5;
            color: #555;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back:hover { background: #e2e6ea; }

        /* ── PREVIEW AREA ── */
        .preview-area {
            padding: 36px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
        }

        /* ── STICKER WRAPPER
           Background transparan sepenuhnya.
           Di layar: shadow tipis supaya batas terlihat.
        ── */
        .sticker-wrap {
            width: var(--sticker-w);
            height: var(--sticker-h);
            background: transparent;
            position: relative;
            box-shadow: 0 0 0 1px #99a8c8, 0 8px 32px rgba(0,0,0,.14);
            border-radius: 3px;
        }

        /* ── STICKER INNER
           Margin dalam dari tepi sticker (agar tidak mepet tepi bahan)
        ── */
        .sticker-inner {
            position: absolute;
            inset: 5.5mm;
            border: 2px solid #000;
            display: flex;
            flex-direction: column;
            background: transparent;
        }

        /* ─────────────────────────────────────────
           HEADER
           Kolom kiri: logo  → lebar = --col-left
           Kolom kanan: judul + nomor
        ───────────────────────────────────────── */
        .s-header {
            display: grid;
            grid-template-columns: var(--col-left) 1fr;
            border-bottom: 2px solid #000;
        }

        /* Logo cell */
        .s-logo-cell {
            border-right: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5mm 3mm;
            background: transparent;
        }
        .s-logo-cell img {
            max-width: 100%;
            max-height: 17mm;
            object-fit: contain;
        }
        .s-logo-placeholder {
            font-size: 10pt;
            font-weight: 900;
            letter-spacing: 2px;
            color: #000;
            text-align: center;
        }

        /* Title + Nomor cell */
        .s-title-cell {
            display: flex;
            flex-direction: column;
        }
        .s-title-main {
            font-size: 11pt;
            font-weight: 900;
            letter-spacing: .8px;
            text-align: center;
            padding: 2.5mm 3mm;
            border-bottom: 1.5px solid #000;
            text-transform: uppercase;
            color: #000;
            line-height: 1.2;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .s-title-nomor {
            font-size: 9.5pt;
            font-weight: 700;
            padding: 2mm 3mm;
            color: #000;
            flex: 1;
            display: flex;
            align-items: center;
        }

        /* ─────────────────────────────────────────
           BODY — baris-baris data
           Kolom label kiri = --col-left (sejajar logo)
        ───────────────────────────────────────── */
        .s-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .s-row {
            display: grid;
            grid-template-columns: var(--col-left) 1fr;
            border-bottom: 1.5px solid #000;
            flex: 1;
            min-height: 0;
        }
        .s-row:last-child {
            border-bottom: none;
        }

        .s-label {
            font-size: 9pt;
            font-weight: 700;
            padding: 1.6mm 3mm;
            border-right: 1.5px solid #000;
            display: flex;
            align-items: center;
            background: transparent;
            color: #000;
            line-height: 1.3;
        }

        .s-value {
            font-size: 9pt;
            font-weight: 600;
            padding: 1.6mm 3mm;
            display: flex;
            align-items: center;
            background: transparent;
            color: #000;
            word-break: break-word;
            line-height: 1.3;
        }

        /* ── PRINT STYLES ── */
        @media print {
            @page {
                size: auto;
                margin: 10mm;
            }
            body { background: transparent !important; }
            .control-panel { display: none !important; }
            .preview-area {
                padding: 0 !important;
                gap: 0 !important;
                align-items: center;
            }
            .sticker-wrap {
                box-shadow: none !important;
                width: var(--sticker-w) !important;
                height: var(--sticker-h) !important;
                margin: 6mm auto;
                page-break-inside: avoid;
                break-inside: avoid;
                background: transparent !important;
            }
            .sticker-inner { background: transparent !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

{{-- ── CONTROL PANEL ── --}}
<div class="control-panel">
    <h1>🏷️ Cetak Sticker Kalibrasi
        <span>{{ $kalibrasiList->count() }} sticker</span>
    </h1>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="{{ route('kalibrasi.index') }}" class="btn-back">← Kembali</a>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Sekarang</button>
    </div>
</div>

{{-- ── STICKER LIST ── --}}
<div class="preview-area">

@foreach($kalibrasiList as $item)
@php
    $timbangan = $item->timbangan;
    $alat      = $timbangan->jenis_alat_ukur ?? 'Timbangan';
    $merk      = $timbangan->merk_tipe_no_seri ?? '-';
    $kapasitas = $timbangan->kapasitas ?? '-';
    $kode      = $timbangan->kode_asset ?? '-';
    $dept      = $item->dept_bagian ?? '-';
    $tgl       = $item->tanggal_pelaksanaan?->format('d/m/Y') ?? '-';
    $pelaksana = $item->pelaksana ?? '-';
    $beda      = $item->beda_maksimum ?? '-';
    $nomor     = $item->certificate_number ?? '-';
@endphp

<div class="sticker-wrap">
    <div class="sticker-inner">

        {{-- HEADER: Logo kiri | Judul + Nomor kanan --}}
        <div class="s-header">
            <div class="s-logo-cell">
                <img src="{{ asset('images/logo.png') }}" alt="Logo"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                <div class="s-logo-placeholder" style="display:none;">LOGO</div>
            </div>
            <div class="s-title-cell">
                <div class="s-title-main">KALIBRASI ALAT UKUR</div>
                <div class="s-title-nomor">Nomor :&nbsp; {{ $nomor }}</div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="s-body">
            <div class="s-row">
                <div class="s-label">Alat Ukur</div>
                <div class="s-value">{{ $alat }}</div>
            </div>
            <div class="s-row">
                <div class="s-label">Merk/Kapasitas</div>
                <div class="s-value">{{ $merk }} / {{ $kapasitas }}</div>
            </div>
            <div class="s-row">
                <div class="s-label">Kode Alat</div>
                <div class="s-value">{{ $kode }}</div>
            </div>
            <div class="s-row">
                <div class="s-label">SBU/Dept/Bagian</div>
                <div class="s-value">{{ $dept }}</div>
            </div>
            <div class="s-row">
                <div class="s-label">Tanggal/Pelaksana</div>
                <div class="s-value">{{ $tgl }} / {{ $pelaksana }}</div>
            </div>
            <div class="s-row">
                <div class="s-label">Beda Maksimum</div>
                <div class="s-value">{{ $beda }}</div>
            </div>
        </div>

    </div>
</div>

@endforeach

</div>

</body>
</html>