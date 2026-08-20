{{--
    resources/views/kalibrasi/sticker.blade.php
    Cetak & Download sticker kalibrasi — versi canvas (sama pola dengan
    generator sticker Asset): sticker digambar ke <canvas>, dijadikan PNG,
    lalu PNG itu yang ditampilkan/di-download/dicetak. Dengan begini hasil
    cetak SELALU sama persis dengan preview di layar — tidak ada lagi
    ketergantungan pada CSS @page / aspect-ratio yang kerap meleset saat
    print di berbagai browser.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Sticker Kalibrasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sticker-w: 600px; /* lebar preview di layar, diikuti otomatis oleh img (aspect ratio asli 2:1) */
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

        .cp-size-group { display: flex; align-items: center; gap: 8px; }
        .cp-size-label { font-size: 0.78rem; font-weight: 700; color: #444; white-space: nowrap; }
        .cp-size-select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            color: #333;
            background: #fff;
            cursor: pointer;
        }

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

        .sticker-block { display: flex; flex-direction: column; align-items: center; gap: 12px; }

        .sticker-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #4361EE;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sticker-actions { display: flex; gap: 8px; }
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
        .btn-dl-png    { background: #198754; color: #fff; }
        .btn-dl-png:hover    { background: #146c43; }
        .btn-print-one { background: #4361EE; color: #fff; }
        .btn-print-one:hover { background: #2d47c4; }

        /* ── STICKER WRAP (menampung <img> hasil canvas) ─────────── */
        /* Pola kotak-kotak menandai area transparan pada PNG hasil cetak */
        .sticker-wrap {
            width: var(--sticker-w);
            display: inline-block;
            background-image:
                linear-gradient(45deg,#bbb 25%,transparent 25%),
                linear-gradient(-45deg,#bbb 25%,transparent 25%),
                linear-gradient(45deg,transparent 75%,#bbb 75%),
                linear-gradient(-45deg,transparent 75%,#bbb 75%);
            background-size: 14px 14px;
            background-position: 0 0, 0 7px, 7px -7px, -7px 0;
            padding: 6px;
            border-radius: 4px;
            box-shadow: 0 0 0 1px #8899cc, 0 8px 32px rgba(0,0,0,.18);
        }
        .sticker-img { display: block; width: 100%; height: auto; border-radius: 3px; }

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
    </style>
</head>
<body>

{{-- Canvas "pabrik" tersembunyi — dipakai ulang untuk menggambar tiap sticker --}}
<canvas id="factoryCanvas" style="display:none;"></canvas>

{{-- ── LOADING OVERLAY ── --}}
<div id="loadingOverlay">
    <div class="spinner"></div>
    <div id="loadingText">Memproses…</div>
</div>

{{-- ── CONTROL PANEL ── --}}
<div class="control-panel">
    <h1>
        🏷️ Sticker Kalibrasi
        <span class="info-badge">{{ $kalibrasiList->count() }} sticker</span>
    </h1>
    <div class="cp-btns">
        <div class="cp-size-group">
            <label for="stickerSize" class="cp-size-label">Ukuran Cetak</label>
            <select id="stickerSize" class="cp-size-select">
                <option value="300" selected>Kecil (30×60 mm)</option>
                <option value="400">Standar (40×80 mm)</option>
                <option value="500">Sedang (50×100 mm)</option>
                <option value="600">Besar (60×120 mm)</option>
            </select>
        </div>
        <a href="{{ route('kalibrasi.index') }}" class="btn-cp secondary">← Kembali</a>
        @if($kalibrasiList->count() > 1)
            <button class="btn-cp success" onclick="downloadAllPng()">
                ⬇ Download Semua PNG
            </button>
        @endif
        <button class="btn-cp primary" onclick="printAll()">
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

<div class="sticker-block"
     data-id="{{ $item->id }}"
     data-alat="{{ $alat }}"
     data-merk="{{ $merk }}"
     data-kapasitas="{{ $kapasitas }}"
     data-kode="{{ $kode }}"
     data-dept="{{ $dept }}"
     data-tgl="{{ $tgl }}"
     data-pelaksana="{{ $pelaksana }}"
     data-beda="{{ $beda }}"
     data-nomor="{{ $nomor }}"
     data-filename="{{ $filename }}">

    <div class="sticker-label">Sticker #{{ $i + 1 }} — {{ $kode }}</div>

    <div class="sticker-wrap">
        <img class="sticker-img" alt="Sticker {{ $kode }}">
    </div>

    <div class="sticker-actions">
        <button class="btn-dl-png" onclick="downloadOnePng('{{ $item->id }}')">
            ⬇ Download PNG
        </button>
        <button class="btn-print-one" onclick="printOne('{{ $item->id }}')">
            🖨 Cetak ini
        </button>
    </div>
</div>
@endforeach

</div>{{-- end preview-area --}}

<script>
/* ══════════════════════════════════════════════════════════════════════
   PENDEKATAN: gambar sticker ke <canvas> → jadikan PNG (dataURL) →
   PNG itulah yang ditampilkan di layar, di-download, dan dicetak.
   Ini pola yang sama seperti generator sticker Asset — hasil cetak jadi
   WYSIWYG karena yang dicetak cuma satu gambar utuh, bukan HTML/CSS
   kompleks yang layout-nya bisa berbeda antara render layar & render print.
   ══════════════════════════════════════════════════════════════════════ */

const factory = document.getElementById('factoryCanvas');
const fctx    = factory.getContext('2d');

// key = value <option> di dropdown "Ukuran Cetak" = tinggi canvas (px),
// lebar selalu 2× tinggi (rasio fisik sticker = 2:1), dan 10px mewakili 1mm
// (jadi H=300 → 30mm, H=400 → 40mm, dst) — dipakai juga untuk ukuran fisik
// saat dicetak (mm).
function currentSize() {
    const base = parseInt(document.getElementById('stickerSize').value, 10);
    const H = base + 5; 
    const W = base * 2;
    return { H, W, hmm: H / 10, wmm: W / 10 };
}

let logoImg = null;
let logoLoadAttempted = false;
function loadLogo(cb) {
    if (logoLoadAttempted) return cb(logoImg);
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload  = () => { logoImg = img; logoLoadAttempted = true; cb(logoImg); };
    img.onerror = () => { logoImg = null; logoLoadAttempted = true; cb(null); };
    img.src = "{{ asset('images/logo.png') }}" + '?_=' + Date.now();
}

function line(ctx, ax, ay, bx, by) {
    ctx.beginPath(); ctx.moveTo(ax, ay); ctx.lineTo(bx, by); ctx.stroke();
}

/**
 * Gambar satu sticker kalibrasi ke factoryCanvas dan kembalikan dataURL PNG.
 */
function drawKalibrasiSticker(data, size, logo) {
    const { H, W } = size;
    factory.width  = W;
    factory.height = H;
    const ctx = fctx;
    ctx.clearRect(0, 0, W, H);

    const lw = Math.max(1, Math.round(H / 150));
    const mg = Math.round(H * 0.05); // margin tepi ~5% dari tinggi
    const x0 = mg, y0 = mg, x1 = W - mg, y1 = H - mg;
    const iW = x1 - x0, iH = y1 - y0;

    ctx.strokeStyle = '#000';
    ctx.fillStyle   = '#000';

    // Border luar (dobel tebal)
    ctx.lineWidth = lw * 1;
    ctx.strokeRect(x0, y0, iW, iH);
    ctx.lineWidth = lw;

    const colX    = x0 + Math.round(iW * 0.33); // batas kolom kiri (label/logo) vs kanan (value/judul)
    const headerH = Math.round(iH * 0.26);
    const bodyY   = y0 + headerH;
    const bodyH   = iH - headerH;

    const rows = [
        ['Alat Ukur',           data.alat],
        ['Merk / Kapasitas',    data.merk + ' / ' + data.kapasitas],
        ['Kode Alat',           data.kode],
        ['SBU / Dept / Bagian', data.dept],
        ['Tanggal / Pelaksana', data.tgl + ' / ' + data.pelaksana],
        ['Beda Maksimum',       data.beda],
    ];
    const rowH = bodyH / rows.length;

    // ── Garis-garis pembatas ──
    line(ctx, x0, bodyY, x1, bodyY);   // pemisah header/body
    line(ctx, colX, y0, colX, bodyY);  // pemisah logo | judul (header)
    for (let i = 1; i < rows.length; i++) {
        const ry = bodyY + rowH * i;
        line(ctx, x0, ry, x1, ry);      // pemisah antar-row
    }
    for (let i = 0; i < rows.length; i++) {
        const ry = bodyY + rowH * i;
        line(ctx, colX, ry, colX, ry + rowH); // pemisah label | value tiap row
    }

    // ── Logo (sel kiri header) ──
    if (logo) {
        const pad   = Math.round(headerH * 0.12);
        const areaW = colX - x0 - pad * 2;
        const areaH = headerH - pad * 2;
        const sc    = Math.min(areaW / logo.naturalWidth, areaH / logo.naturalHeight);
        const lw2   = logo.naturalWidth  * sc * 1.3; // stretch lebar sedikit, sama seperti versi Asset
        const lh2   = logo.naturalHeight * sc;
        ctx.drawImage(logo, x0 + (colX - x0 - lw2) / 2, y0 + (headerH - lh2) / 2, lw2, lh2);
    } else {
        ctx.font = `900 ${Math.round(headerH * 0.18)}px Montserrat, Arial, sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('LOGO', x0 + (colX - x0) / 2, y0 + headerH / 2);
    }

    // ── Judul & Nomor (sel kanan header, 2 baris) ──
    const titleAreaX = colX, titleAreaW = x1 - colX;
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';

    let fsTitle = Math.round(headerH * 0.30);
    ctx.font = `700 ${fsTitle}px Montserrat, Arial, sans-serif`;
    const titleText = 'KALIBRASI ALAT UKUR';
    while (ctx.measureText(titleText).width > titleAreaW - 16 && fsTitle > 8) {
        fsTitle--; ctx.font = `700 ${fsTitle}px Montserrat, Arial, sans-serif`;
    }
    ctx.fillText(titleText, titleAreaX + titleAreaW / 2, y0 + headerH * 0.32);

    line(ctx, titleAreaX, y0 + headerH * 0.58, x1, y0 + headerH * 0.58);

    let fsNomor = Math.round(headerH * 0.24);
    const nomorText = 'Nomor : ' + data.nomor;
    ctx.font = `700 ${fsNomor}px Montserrat, Arial, sans-serif`;
    while (ctx.measureText(nomorText).width > titleAreaW - 16 && fsNomor > 8) {
        fsNomor--; ctx.font = `700 ${fsNomor}px Montserrat, Arial, sans-serif`;
    }
    ctx.fillText(nomorText, titleAreaX + titleAreaW / 2, y0 + headerH * 0.80);

    // ── Baris body: label (kiri) + value (kanan, auto-shrink agar muat) ──
    const padCell = Math.round(iW * 0.02);
    const fs      = Math.round(rowH * 0.45);
    ctx.textAlign = 'left'; ctx.textBaseline = 'middle';

    // ── Helper: pecah teks jadi beberapa baris berdasarkan lebar maksimum ──
function wrapWords(ctx, text, maxW) {
    const words = String(text).split(' ');
    const lines = [];
    let cur = '';
    for (const w of words) {
        const test = cur ? cur + ' ' + w : w;
        if (ctx.measureText(test).width > maxW && cur) {
            lines.push(cur);
            cur = w;
        } else {
            cur = test;
        }
    }
    if (cur) lines.push(cur);
    return lines;
}

// ── Helper: cari ukuran font terbesar yang muat, wrap ke maks 2 baris ──
function fitValueLines(ctx, text, maxW, maxH, baseFs) {
    const family = 'Montserrat, Arial, sans-serif';
    const minFs  = Math.max(6, Math.round(baseFs * 0.55)); // jangan lebih kecil dari ini
    let fs = baseFs;

    while (fs >= minFs) {
        ctx.font = `600 ${fs}px ${family}`;
        if (ctx.measureText(text).width <= maxW) {
            return { fs, lines: [text] }; // muat 1 baris, tidak perlu wrap
        }
        const lines = wrapWords(ctx, text, maxW);
        const lineH = fs * 1.15;
        const fitsWidth  = lines.every(l => ctx.measureText(l).width <= maxW);
        const fitsHeight = lines.length * lineH <= maxH;
        if (lines.length <= 2 && fitsWidth && fitsHeight) {
            return { fs, lines };
        }
        fs -= 1; // belum muat → kecilkan font, coba lagi
    }

    // fallback: sudah di font terkecil, paksa 2 baris, potong sisa dgn "…"
    ctx.font = `600 ${minFs}px ${family}`;
    let lines = wrapWords(ctx, text, maxW);
    if (lines.length > 2) {
        let rest = lines.slice(1).join(' ');
        while (ctx.measureText(rest).width > maxW && rest.length > 3) rest = rest.slice(0, -1);
        lines = [lines[0], rest.slice(0, -1) + '…'];
    }
    return { fs: minFs, lines };
}

// ── Render baris body ──
rows.forEach(([label, value], i) => {
    const ry = bodyY + rowH * i;
    const cy = ry + rowH / 2;

    ctx.font = `700 ${fs}px Montserrat, Arial, sans-serif`;
    ctx.fillText(label, x0 + padCell, cy);

    const maxW = (x1 - colX) - padCell * 2;
    const maxH = rowH * 0.88; // sedikit jarak dari garis atas/bawah row
    const fitted = fitValueLines(ctx, String(value ?? '-'), maxW, maxH, fs);

    ctx.font = `600 ${fitted.fs}px Montserrat, Arial, sans-serif`;
    const lineH  = fitted.fs * 1.15;
    const totalH = fitted.lines.length * lineH;
    let ly = cy - totalH / 2 + lineH / 2;
    fitted.lines.forEach(line => {
        ctx.fillText(line, colX + padCell, ly);
        ly += lineH;
    });
});

    return factory.toDataURL('image/png');
}

/* ── Render ulang semua sticker (dipanggil saat load & saat ukuran diganti) ── */
const stickerData = {}; // id -> { dataUrl, wmm, hmm, filename }

function renderAll() {
    const size = currentSize();
    loadLogo(logo => {
        document.querySelectorAll('.sticker-block').forEach(block => {
            const id = block.dataset.id;
            const data = {
                alat: block.dataset.alat, merk: block.dataset.merk, kapasitas: block.dataset.kapasitas,
                kode: block.dataset.kode, dept: block.dataset.dept, tgl: block.dataset.tgl,
                pelaksana: block.dataset.pelaksana, beda: block.dataset.beda, nomor: block.dataset.nomor,
            };
            const dataUrl = drawKalibrasiSticker(data, size, logo);
            stickerData[id] = { dataUrl, wmm: size.wmm, hmm: size.hmm, filename: block.dataset.filename };
            block.querySelector('.sticker-img').src = dataUrl;
        });
    });
}

document.getElementById('stickerSize').addEventListener('change', renderAll);
renderAll();

/* ── Helpers loading overlay ── */
function showLoading(text) {
    document.getElementById('loadingText').textContent = text || 'Memproses…';
    document.getElementById('loadingOverlay').classList.add('show');
}
function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('show');
}

/* ── Download PNG ── */
function downloadOnePng(id) {
    const d = stickerData[id];
    if (!d) return;
    const a = document.createElement('a');
    a.download = d.filename + '.png';
    a.href = d.dataUrl;
    a.click();
}

function downloadAllPng() {
    const ids = Object.keys(stickerData);
    if (ids.length === 0) return;
    showLoading('Mempersiapkan ' + ids.length + ' sticker…');
    ids.forEach((id, i) => {
        setTimeout(() => {
            downloadOnePng(id);
            if (i === ids.length - 1) hideLoading();
        }, i * 400);
    });
}

/* ── Cetak: buka window baru berisi PNG, ukuran halaman = ukuran fisik sticker (mm) ──
   Ini kunci kenapa cetak jadi selalu benar: yang dicetak cuma <img>, bukan
   HTML/CSS kompleks — jadi tidak ada lagi salah hitung tinggi/lebar. */
function buildPrintWindow(items) {
    if (!items.length) return;
    const win = window.open('', '_blank');
    if (!win) {
        alert('Popup diblokir browser. Izinkan popup untuk situs ini agar bisa mencetak.');
        return;
    }
    const first = items[0];
    const pages = items.map(it =>
        `<div class="p"><img src="${it.dataUrl}" alt="sticker"></div>`
    ).join('');

    win.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cetak Sticker Kalibrasi</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#f5f5f5;display:flex;flex-direction:column;align-items:center;
     padding:24px;font-family:Montserrat,Arial,sans-serif}
.p{width:${first.wmm}mm;margin-bottom:14mm}
.p img{display:block;width:100%;height:auto}
.btns{display:flex;gap:10px;margin-bottom:16px}
button{padding:10px 26px;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:700}
.bp{background:#4361EE;color:#fff}
.bc{background:#6c757d;color:#fff}
@media print{
    body{background:transparent!important;padding:0}
    .btns{display:none!important}
    .p{margin:0!important;page-break-after:always;break-after:page}
    .p:last-child{page-break-after:auto;break-after:auto}
    @page{ size: ${first.wmm}mm ${first.hmm}mm; margin: 0; }
}
</style>
</head>
<body>
<div class="btns">
    <button class="bp" onclick="window.print()">🖨 Cetak Sekarang</button>
    <button class="bc" onclick="window.close()">✕ Tutup</button>
</div>
${pages}
</body></html>`);
    win.document.close();
}

function printOne(id) {
    const d = stickerData[id];
    if (!d) return;
    buildPrintWindow([{ dataUrl: d.dataUrl, wmm: d.wmm, hmm: d.hmm }]);
}

function printAll() {
    const items = Object.values(stickerData).map(d => ({ dataUrl: d.dataUrl, wmm: d.wmm, hmm: d.hmm }));
    buildPrintWindow(items);
}
</script>

</body>
</html>