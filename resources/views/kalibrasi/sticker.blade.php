{{--
    resources/views/kalibrasi/sticker.blade.php
    Cetak & Download sticker kalibrasi — transparan (siap cetak kertas emas)
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Sticker Kalibrasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sticker-w: 720px;   /* ~180mm pada 96dpi — lebar preview layar */
            --sticker-h: 390px;   /* ~100mm */
            --col-left: 33%;
            --border: 3px solid #000;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #cdd3e0;
            color: #000;
        }

        /* ── CONTROL PANEL ─────────────────────────────────────── */
        .control-panel {
            background: #fff;
            border-bottom: 3px solid #4361EE;
            padding: 14px 28px;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .control-panel h1 span {
            font-weight: 500;
            color: #888;
            font-size: 0.82rem;
        }
        .cp-btns { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        .btn-cp {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-cp.primary  { background: #4361EE; color: #fff; }
        .btn-cp.primary:hover  { background: #2d47c4; }
        .btn-cp.success  { background: #198754; color: #fff; }
        .btn-cp.success:hover  { background: #146c43; }
        .btn-cp.secondary { background: #f1f3f5; color: #444; }
        .btn-cp.secondary:hover { background: #dee2e6; }
        .btn-cp.warning  { background: #f59e0b; color: #fff; }
        .btn-cp.warning:hover { background: #d97706; }

        .info-badge {
            background: #e8f0fe;
            color: #4361EE;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* ── PREVIEW AREA ───────────────────────────────────────── */
        .preview-area {
            padding: 36px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 48px;
        }

        /* Wrapper satu sticker */
        .sticker-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        /* Label nomor sticker */
        .sticker-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #4361EE;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Tombol aksi per sticker */
        .sticker-actions {
            display: flex;
            gap: 8px;
        }
        .sticker-actions button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.78rem;
            cursor: pointer;
            border: none;
        }
        .btn-dl-png  { background: #198754; color: #fff; }
        .btn-dl-png:hover  { background: #146c43; }
        .btn-dl-svg  { background: #6f42c1; color: #fff; }
        .btn-dl-svg:hover  { background: #5a32a3; }
        .btn-print-one { background: #4361EE; color: #fff; }
        .btn-print-one:hover { background: #2d47c4; }

        /* ── STICKER WRAPPER ────────────────────────────────────── */
        .sticker-wrap {
            width: var(--sticker-w);
            height: var(--sticker-h);
            background: transparent;
            position: relative;
            /* shadow hanya di layar */
            box-shadow: 0 0 0 1px #8899cc, 0 8px 32px rgba(0,0,0,.18);
            border-radius: 3px;
        }

        /* ── STICKER INNER ──────────────────────────────────────── */
        .sticker-inner {
            position: absolute;
            inset: 16px;           /* ≈5.5mm margin bahan */
            border: var(--border);
            display: flex;
            flex-direction: column;
            background: transparent;
        }

        /* HEADER */
        .s-header {
            display: grid;
            grid-template-columns: var(--col-left) 1fr;
            border-bottom: var(--border);
            flex-shrink: 0;
        }
        .s-logo-cell {
            border-right: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            /* padding: 8px 12px; */
        }
        .s-logo-cell img {
            max-width: 100%;
            max-height: 72px;
            transform: scaleX(1.4);
        }
        .s-logo-placeholder {
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 2px;
            color: #000;
        }
        .s-title-cell {
            display: flex;
            flex-direction: column;
        }
        .s-title-main {
            font-size: 18pt;
            font-weight: 700;
            text-align: center;
            padding: 4px 8px;
            border-bottom: 3px solid #000;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }
        .s-title-nomor {
            font-size: 16pt;
            font-weight: 700;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            flex: 1;
        }

        /* BODY */
        .s-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .s-row {
            display: grid;
            grid-template-columns: var(--col-left) 1fr;
            border-bottom: 3px solid #000;
            flex: 1;
            min-height: 0;
        }
        .s-row:last-child { border-bottom: none; }
        .s-label {
            font-size: 14pt;
            font-weight: 700;
            padding: 5px 10px;
            border-right: 3px solid #000;
            display: flex;
            align-items: center;
        }
        .s-value {
            font-size: 14pt;
            font-weight: 600;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            word-break: break-word;
            line-height: 1.35;
        }

        /* ── LOADING OVERLAY ────────────────────────────────────── */
        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            gap: 16px;
        }
        #loadingOverlay.show { display: flex; }
        .spinner {
            width: 44px; height: 44px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── PRINT STYLES ───────────────────────────────────────── */
        @media print {
            @page { size: auto; margin: 8mm; }
            body { background: transparent !important; }
            .control-panel,
            .sticker-label,
            .sticker-actions { display: none !important; }
            .preview-area { padding: 0 !important; gap: 0 !important; align-items: center; }
            .sticker-wrap {
                box-shadow: none !important;
                width: 180mm !important;
                height: 90mm !important;
                margin: 5mm auto;
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

{{-- ── LOADING OVERLAY ── --}}
<div id="loadingOverlay">
    <div class="spinner"></div>
    <div id="loadingText">Membuat PNG…</div>
</div>

{{-- ── CONTROL PANEL ── --}}
<div class="control-panel">
    <h1>
        🏷️ Sticker Kalibrasi
        <span class="info-badge">{{ $kalibrasiList->count() }} sticker</span>
    </h1>
    <div class="cp-btns">
        <a href="{{ route('kalibrasi.index') }}" class="btn-cp secondary">← Kembali</a>
        @if($kalibrasiList->count() > 1)
            <button class="btn-cp success" onclick="downloadAllPng()">
                ⬇ Download Semua PNG
            </button>
        @endif
        <button class="btn-cp primary" onclick="window.print()">
            🖨️ Cetak Sekarang
        </button>
    </div>
</div>

{{-- ── STICKER LIST ── --}}
<div class="preview-area">

@foreach($kalibrasiList as $i => $item)
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
    $filename  = 'sticker-' . str_replace(['/', ' '], '-', $kode) . '-' . str_replace('/', '-', $tgl);
@endphp

<div class="sticker-block">
    <div class="sticker-label">Sticker #{{ $i + 1 }} — {{ $kode }}</div>

    {{-- STICKER — elemen ini yang di-capture html2canvas --}}
    <div class="sticker-wrap" id="sticker-{{ $item->id }}" data-filename="{{ $filename }}">
        <div class="sticker-inner">

            {{-- HEADER --}}
            <div class="s-header">
                <div class="s-logo-cell">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo"
                         crossorigin="anonymous"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
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
                    <div class="s-label">Merk / Kapasitas</div>
                    <div class="s-value">{{ $merk }} / {{ $kapasitas }}</div>
                </div>
                <div class="s-row">
                    <div class="s-label">Kode Alat</div>
                    <div class="s-value">{{ $kode }}</div>
                </div>
                <div class="s-row">
                    <div class="s-label">SBU / Dept / Bagian</div>
                    <div class="s-value">{{ $dept }}</div>
                </div>
                <div class="s-row">
                    <div class="s-label">Tanggal / Pelaksana</div>
                    <div class="s-value">{{ $tgl }} / {{ $pelaksana }}</div>
                </div>
                <div class="s-row">
                    <div class="s-label">Beda Maksimum</div>
                    <div class="s-value">{{ $beda }}</div>
                </div>
            </div>

        </div>
    </div>

    {{-- TOMBOL PER STICKER --}}
    <div class="sticker-actions">
        <button class="btn-dl-png"
            onclick="downloadOnePng('sticker-{{ $item->id }}')">
            ⬇ Download PNG
        </button>
        <button class="btn-print-one"
            onclick="printOne('sticker-{{ $item->id }}')">
            🖨 Cetak ini
        </button>
    </div>
</div>
@endforeach

</div>{{-- end preview-area --}}

<script>
/* ── Helpers ─────────────────────────────────────────────────────────────── */

function showLoading(text) {
    document.getElementById('loadingText').textContent = text || 'Memproses…';
    document.getElementById('loadingOverlay').classList.add('show');
}
function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('show');
}

/**
 * Capture satu elemen sticker menjadi PNG transparan dan trigger download.
 * Menggunakan scale 3× untuk resolusi tinggi (cocok untuk cetak).
 */
async function capturePng(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return null;

    const scale = 3; // 3× = ~270 DPI pada layar 96dpi

    const canvas = await html2canvas(el, {
        scale          : scale,
        useCORS        : true,
        allowTaint     : false,
        backgroundColor: null,   // ← transparan
        logging        : false,
        imageTimeout   : 8000,
    });

    return canvas;
}

/**
 * Download PNG satu sticker.
 */
async function downloadOnePng(elementId) {
    showLoading('Membuat PNG…');
    try {
        const canvas = await capturePng(elementId);
        if (!canvas) throw new Error('Element tidak ditemukan');

        const el       = document.getElementById(elementId);
        const filename = (el.dataset.filename || elementId) + '.png';

        const link  = document.createElement('a');
        link.href   = canvas.toDataURL('image/png');
        link.download = filename;
        link.click();
    } catch (err) {
        alert('Gagal membuat PNG: ' + err.message);
        console.error(err);
    } finally {
        hideLoading();
    }
}

/**
 * Download semua sticker sekaligus (sequential, satu per satu).
 */
async function downloadAllPng() {
    const stickers = document.querySelectorAll('.sticker-wrap');
    if (stickers.length === 0) return;

    showLoading('Mempersiapkan ' + stickers.length + ' sticker…');

    try {
        for (let i = 0; i < stickers.length; i++) {
            const el = stickers[i];
            document.getElementById('loadingText').textContent =
                'Membuat PNG ' + (i + 1) + ' / ' + stickers.length + '…';

            const canvas   = await capturePng(el.id);
            const filename = (el.dataset.filename || el.id) + '.png';

            const link    = document.createElement('a');
            link.href     = canvas.toDataURL('image/png');
            link.download = filename;
            link.click();

            // Jeda singkat antar download agar browser tidak blokir
            await new Promise(r => setTimeout(r, 600));
        }
    } catch (err) {
        alert('Gagal membuat PNG: ' + err.message);
        console.error(err);
    } finally {
        hideLoading();
    }
}

/**
 * Cetak satu sticker saja — sembunyikan yang lain sementara.
 */
function printOne(elementId) {
    // Tambah class print-only ke elemen yang mau dicetak
    const allBlocks = document.querySelectorAll('.sticker-block');
    const target    = document.getElementById(elementId)?.closest('.sticker-block');

    allBlocks.forEach(b => b.style.display = 'none');
    if (target) target.style.removeProperty('display');

    window.print();

    // Kembalikan semua setelah print dialog tutup
    setTimeout(() => {
        allBlocks.forEach(b => b.style.removeProperty('display'));
    }, 1000);
}
</script>

</body>
</html>