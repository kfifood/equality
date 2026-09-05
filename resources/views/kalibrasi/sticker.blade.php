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
        .text-size-hint {
            cursor: help;
            color: #4361EE;
            font-weight: 700;
            font-size: 0.9rem;
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

        /* ── PRINT STAGE ──────────────────────────────────────────
           Dipakai untuk cetak langsung dari halaman ini (tanpa buka
           tab/window baru). Saat print dipicu, semua elemen lain di
           halaman DIHAPUS DARI ALUR halaman (display:none — bukan
           visibility:hidden, yang cuma menyembunyikan tapi tetap
           memakan tinggi/ruang, dan kalau digabung posisi fixed malah
           bikin kontennya "diulang" di tiap halaman cetak → itu
           penyebab satu sticker bisa jadi berhalaman-halaman).
           Dengan display:none, tinggi dokumen saat print jadi cuma
           setinggi isi #printStage sendiri → jumlah halaman fisik
           persis sejumlah item yang dicetak. Ukuran fisik halaman
           (@page) diatur dinamis lewat <style> terpisah yang
           disisipkan JS sebelum window.print().

           .p SENGAJA diberi ukuran mm EKSPLISIT (inline style, per
           item, lihat printViaStage()) — bukan width:100% dari
           kertas. Kalau pakai 100%, sticker-nya baru tampil benar
           kalau kertas fisik yang benar-benar dipakai printer PERSIS
           sama dengan ukuran sticker; begitu dites pakai kertas biasa
           (mis. A4, karena dialog print belum diarahkan ke ukuran
           custom), sticker-nya malah ikut melar/center menyesuaikan
           kertas besar itu. Dengan ukuran mm eksplisit + anchor
           kiri-atas (margin:0, tanpa flex/center apa pun), sticker
           akan selalu tercetak di UKURAN SEBENARNYA dan MENEMPEL DI
           POJOK KIRI-ATAS halaman — baik dicetak di kertas sticker
           asli maupun di kertas biasa untuk uji coba. */
        #printStage { display: none; }
        @media print {
            body > *:not(#printStage) { display: none !important; }
            #printStage {
                display: block !important;
                margin: 0;
                padding: 0;
            }
            #printStage .p {
                /* width & height diisi inline (mm) oleh JS per item */
                margin: 0;
                page-break-after: always;
                break-after: page;
            }
            #printStage .p:last-child { page-break-after: auto; break-after: auto; }
            #printStage .p img { display: block; width: 100%; height: 100%; }
            @page { margin: 0; }
        }
    </style>
</head>

<body>

{{-- Canvas "pabrik" tersembunyi — dipakai ulang untuk menggambar tiap sticker --}}
<canvas id="factoryCanvas" style="display:none;"></canvas>

{{-- Panggung cetak tersembunyi — diisi JS lalu window.print() dipanggil
     langsung di halaman ini, tanpa buka tab/window baru. Lihat CSS
     #printStage di <head> dan fungsi printViaStage() di bawah. --}}
<div id="printStage"></div>

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
            @php
                // Rasio lebar:tinggi berbeda per kategori alat (lihat currentSizeFor()
                // di JS: Timbangan/lainnya = 2:1, Thermometer 'TRM' lebar dikunci
                // 30mm & cuma tingginya yang membesar). Supaya
                // yang nyetak paham persis berapa mm hasilnya, label tiap opsi
                // dihitung di sini (server-side, PHP), dibuat cocok dengan rumus
                // yang sama persis dipakai canvas — bukan cuma angka statis, dan
                // otomatis menyesuaikan kategori alat yang ada di halaman ini.
                $sizeBases = ['300' => 'Kecil', '400' => 'Standar', '500' => 'Sedang', '600' => 'Besar'];

                $kategoriHadir = [];
                foreach ($kalibrasiList as $item) {
                    $kode = $item->peralatan->kategoriAlat->kode_kategori ?? '';
                    $nama = $item->peralatan->kategoriAlat->nama_kategori ?? 'Alat Ukur';
                    $kategoriHadir[$kode] = $nama;
                }
                if (empty($kategoriHadir)) {
                    $kategoriHadir[''] = 'Alat Ukur';
                }

                // Sama persis dengan currentSizeFor() di JS: TRM lebarnya DIKUNCI
                // 30mm (tidak ikut skala dropdown), cuma tingginya yang membesar
                // (30mm di "Kecil" s/d 40mm di "Besar"). Kategori lain (mis.
                // Timbangan) tetap pakai rasio 2:1 lama (H = base+5, W = base*2).
                $hitungUkuranMm = function (int $base, string $kode) {
                    if ($kode === 'TRM') {
                        $w = 300;
                        $h = (int) round($base / 3 + 200);
                    } else {
                        $h = $base + 5;
                        $w = $base * 2;
                    }
                    $fmt = fn ($v) => rtrim(rtrim(number_format($v / 10, 1), '0'), '.');
                    return $fmt($w) . '×' . $fmt($h) . 'mm';
                };
            @endphp
            <select id="stickerSize" class="cp-size-select">
                @foreach($sizeBases as $base => $label)
                    @php
                        $dims = collect($kategoriHadir)
                            ->map(fn ($nama, $kode) => $nama . ' ' . $hitungUkuranMm((int) $base, $kode))
                            ->implode(' · ');
                    @endphp
                    <option value="{{ $base }}" {{ $base == 300 ? 'selected' : '' }}>
                        {{ $label }} ({{ $dims }})
                    </option>
                @endforeach
            </select>
            <span class="text-size-hint" title="Ukuran ditulis Lebar × Tinggi dalam mm, dihitung otomatis sesuai kategori alat yang sedang dicetak.">ⓘ</span>
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
    $peralatan = $item->peralatan;
    // kode_kategori dipakai untuk menentukan tata letak sticker per jenis alat
    // (mis. 'TMB' = Timbangan, 'TRM' = Thermometer). Alat baru cukup daftarkan
    // kategorinya di master_kategori_alat, lalu tambahkan cabang layout di JS
    // drawKalibrasiSticker() bila butuh tampilan berbeda dari default.
    $kategoriKode = $peralatan->kategoriAlat->kode_kategori ?? null;
    $alat      = $peralatan->kategoriAlat->nama_kategori ?? 'Alat Ukur';
    $merk      = $peralatan->merk_tipe_lengkap ?? '-';
    $kapasitas = $peralatan->spesifikasi['kapasitas'] ?? '-';
    $kode      = $peralatan->kode_asset ?? '-';
    $dept      = $item->dept_bagian ?? '-';
    $tgl       = $item->tanggal_pelaksanaan?->format('d/m/Y') ?? '-';
    $pelaksana = $item->pelaksana ?? '-';
    $beda      = $item->beda_maksimum ?? '-';
    $nomor     = $item->calibration_number ?? '-';
    $filename  = 'sticker-' . str_replace(['/', ' '], '-', $kode) . '-' . str_replace('/', '-', $tgl);

    // Data pengukuran generik (mis. suhu alat/master untuk Thermometer),
    // dibaca dari kolom JSON data_pengukuran — kosong untuk kategori yang
    // tidak memakainya (mis. Timbangan, yang pakai 'beda_maksimum').
    $pengukuran = $item->pengukuran;
@endphp

<div class="sticker-block"
     data-id="{{ $item->id }}"
     data-kategori="{{ $kategoriKode }}"
     data-alat="{{ $alat }}"
     data-merk="{{ $merk }}"
     data-kapasitas="{{ $kapasitas }}"
     data-kode="{{ $kode }}"
     data-dept="{{ $dept }}"
     data-tgl="{{ $tgl }}"
     data-pelaksana="{{ $pelaksana }}"
     data-beda="{{ $beda }}"
     data-nomor="{{ $nomor }}"
     data-pengukuran="{{ json_encode($pengukuran) }}"
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

// key = value <option> di dropdown "Ukuran Cetak", dipakai sebagai basis
// ukuran (px, 10px mewakili 1mm) — tapi rasio lebar:tinggi berbeda per
// kategori alat, karena tiap kategori punya template sticker sendiri
// dengan bentuk fisik yang berbeda:
// - Timbangan (& kategori lain yang belum punya template khusus):
//   rasio lebar:tinggi = 2:1, mis. "Kecil" → 30×60mm (perilaku lama).
// - Thermometer ('TRM'): LEBAR DIKUNCI 30mm (mentok, tidak ikut skala
//   dropdown), hanya TINGGI yang membesar mengikuti pilihan "Ukuran
//   Cetak" (30mm di "Kecil" s/d 40mm di "Besar") — supaya tulisan
//   "Alat (°C)" / "Master (°C)" & angka suhunya tidak kegepengan/kecil,
//   tanpa melebarkan sticker-nya (lebar 3cm tetap harus pas di label).
function currentSizeFor(kategori) {
    const base = parseInt(document.getElementById('stickerSize').value, 10);

    if (kategori === 'TRM') {
        const W = 300; // lebar dikunci 30mm, berapa pun "Ukuran Cetak" yang dipilih
        const H = Math.round(base / 3 + 200); // base 300→300(30mm) ... 600→400(40mm)
        return { H, W, hmm: H / 10, wmm: W / 10 };
    }

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

// ── Helper: pecah teks jadi beberapa baris berdasarkan lebar maksimum ──
// Global (bukan nested di dalam salah satu fungsi draw...) supaya dipakai
// bersama oleh drawKalibrasiSticker (Timbangan) & drawThermometerSticker.
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
// Global juga, dengan alasan yang sama seperti wrapWords di atas.
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

/**
 * Gambar satu sticker kalibrasi ke factoryCanvas dan kembalikan dataURL PNG.
 * Template berbeda per kategori alat: Thermometer ('TRM') punya bentuk fisik
 * & tata letak sendiri (lihat drawThermometerSticker), kategori lain (mis.
 * Timbangan) pakai template default di bawah ini.
 */
function drawKalibrasiSticker(data, size, logo) {
    if (data.kategori === 'TRM') {
        return drawThermometerSticker(data, size, logo);
    }

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

    // Baris body sticker (Timbangan / kategori lain yang belum punya
    // template khusus): tampilkan Merk & Kapasitas + Beda Maksimum.
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

    // ── Render baris body ──
    // (helper wrapWords & fitValueLines dipindah ke scope global — lihat
    // di atas drawKalibrasiSticker — supaya bisa dipakai juga oleh
    // drawThermometerSticker, bukan cuma di dalam fungsi ini)
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

/**
 * Template sticker khusus kategori Thermometer ('TRM') — bentuk fisik &
 * tata letak beda total dari template Timbangan: header (logo | judul +
 * nomor), baris "Tgl Kalibrasi" penuh, lalu tabel 2 kolom "Alat (°C)" /
 * "Master (°C)" dengan 3 baris hasil pembacaan. Lebar dikunci 30mm,
 * tinggi membesar 30–40mm mengikuti "Ukuran Cetak" (lihat currentSizeFor())
 * supaya teks header kolom & angka suhu tidak kegepengan/kecil.
 */
function drawThermometerSticker(data, size, logo) {
    const { H, W } = size;
    factory.width  = W;
    factory.height = H;
    const ctx = fctx;
    ctx.clearRect(0, 0, W, H);

    const lw = Math.max(1, Math.round(H / 100));
    // Margin dihitung dari sisi TERPENDEK (bukan cuma H) — sejak lebar TRM
    // dikunci 30mm sementara tinggi bisa membesar sampai 40mm, W & H tidak
    // lagi selalu skala bareng, jadi kalau margin dihitung dari H saja,
    // marginnya bisa "memakan" porsi lebar secara tidak proporsional.
    const mg = Math.round(Math.min(W, H) * 0.06); // margin tepi
    const x0 = mg, y0 = mg, x1 = W - mg, y1 = H - mg;
    const iW = x1 - x0, iH = y1 - y0;

    ctx.strokeStyle = '#000';
    ctx.fillStyle   = '#000';
    ctx.lineWidth   = lw;
    ctx.strokeRect(x0, y0, iW, iH);

    // ── Pembagian tinggi tiap section ──
    const headerH  = Math.round(iH * 0.30); // baris judul + nomor
    const tglH     = Math.round(iH * 0.16); // baris "Tgl Kalibrasi"
    const colHeadH = Math.round(iH * 0.16); // baris header "Alat (°C)" / "Master (°C)"
    const dataH    = iH - headerH - tglH - colHeadH; // sisa untuk 3 baris data
    const rowH     = dataH / 3;

    const colX = x0 + Math.round(iW * 0.34); // batas kolom: logo | judul+nomor (header)
    const midX = x0 + iW / 2;                // batas kolom: Alat | Master (tabel bawah)

    const yHeaderMid = y0 + headerH / 2; // batas judul | nomor (dalam header)
    const y1h        = y0 + headerH;     // batas bawah header
    const y2h         = y1h + tglH;      // batas bawah baris "Tgl Kalibrasi"
    const y3h        = y2h + colHeadH;   // batas bawah header kolom Alat/Master

    // ── Garis-garis pembatas ──
    line(ctx, x0, y1h, x1, y1h);            // header | Tgl Kalibrasi
    line(ctx, x0, y2h, x1, y2h);             // Tgl Kalibrasi | header kolom
    line(ctx, x0, y3h, x1, y3h);            // header kolom | data
    for (let i = 1; i < 3; i++) {
        const ry = y3h + rowH * i;
        line(ctx, x0, ry, x1, ry);           // antar baris data
    }
    line(ctx, colX, y0, colX, y1h);          // header: logo | judul+nomor
    line(ctx, colX, yHeaderMid, x1, yHeaderMid); // header: judul | nomor
    line(ctx, midX, y3h, midX, y1);          // header kolom + data: Alat | Master

    // ── Logo (sel kiri header, menyatu penuh tinggi header) ──
    if (logo) {
        const pad   = Math.round(headerH * 0.14);
        const areaW = colX - x0 - pad * 2;
        const areaH = headerH - pad * 2;
        const sc    = Math.min(areaW / logo.naturalWidth, areaH / logo.naturalHeight);
        const lw2   = logo.naturalWidth  * sc;
        const lh2   = logo.naturalHeight * sc;
        ctx.drawImage(logo, x0 + (colX - x0 - lw2) / 2, y0 + (headerH - lh2) / 2, lw2, lh2);
    } else {
        ctx.font = `900 ${Math.round(headerH * 0.18)}px Montserrat, Arial, sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText('LOGO', x0 + (colX - x0) / 2, y0 + headerH / 2);
    }

    // ── Judul "KALIBRASI ALAT UKUR" (sub-baris atas header, kanan) ──
    const titleAreaX = colX, titleAreaW = x1 - colX;
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    let fsTitle = Math.round(headerH * 0.30);
    ctx.font = `700 ${fsTitle}px Montserrat, Arial, sans-serif`;
    const titleText = 'KALIBRASI ALAT UKUR';
    while (ctx.measureText(titleText).width > titleAreaW - 12 && fsTitle > 6) {
        fsTitle--; ctx.font = `700 ${fsTitle}px Montserrat, Arial, sans-serif`;
    }
    ctx.fillText(titleText, titleAreaX + titleAreaW / 2, y0 + headerH * 0.25);

    // ── "Nomor : ..." (sub-baris bawah header, kanan, rata kiri) ──
    ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
    let fsNomor = Math.round(headerH * 0.24);
    ctx.font = `600 ${fsNomor}px Montserrat, Arial, sans-serif`;
    const nomorText = 'Nomor : ' + (data.nomor ?? '-');
    while (ctx.measureText(nomorText).width > titleAreaW - 16 && fsNomor > 6) {
        fsNomor--; ctx.font = `600 ${fsNomor}px Montserrat, Arial, sans-serif`;
    }
    ctx.fillText(nomorText, titleAreaX + Math.round(titleAreaW * 0.06), y0 + headerH * 0.75);

    // ── "Tgl Kalibrasi : ..." (baris penuh, rata kiri) ──
    ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
    let fsTgl = Math.round(tglH * 0.5);
    ctx.font = `600 ${fsTgl}px Montserrat, Arial, sans-serif`;
    const tglText = 'Tgl Kalibrasi : ' + (data.tgl ?? '-');
    while (ctx.measureText(tglText).width > iW - 16 && fsTgl > 6) {
        fsTgl--; ctx.font = `600 ${fsTgl}px Montserrat, Arial, sans-serif`;
    }
    ctx.fillText(tglText, x0 + Math.round(iW * 0.03), y1h + tglH / 2);

    // ── Header kolom "Alat (°C)" / "Master (°C)" ──
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    let fsColHead = Math.round(colHeadH * 0.44);
    ctx.font = `700 ${fsColHead}px Montserrat, Arial, sans-serif`;
    ctx.fillText('Alat (°C)',   x0 + (midX - x0) / 2, y2h + colHeadH / 2);
    ctx.fillText('Master (°C)', midX + (x1 - midX) / 2, y2h + colHeadH / 2);

    // ── 3 baris data pengukuran (Suhu Alat / Suhu Master) ──
    // Kosong (belum diisi) kalau data_pengukuran belum ada — kotak tetap
    // tercetak supaya bisa diisi manual, sama seperti template kertas asli.
    const pengukuran = Array.isArray(data.pengukuran) ? data.pengukuran : [];
    const padCell    = Math.round(iW * 0.02);
    const baseFs     = Math.round(rowH * 0.42);

    for (let i = 0; i < 3; i++) {
        const p  = pengukuran[i] || {};
        const ry = y3h + rowH * i;
        const cy = ry + rowH / 2;

        const alatText   = p.suhu_alat   ? p.suhu_alat   + '°C' : '';
        const masterText = p.suhu_master ? p.suhu_master + '°C' : '';
        const maxW       = (midX - x0) - padCell * 2;
        const maxH       = rowH * 0.85;

        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';

        if (alatText) {
            const fitted = fitValueLines(ctx, alatText, maxW, maxH, baseFs);
            ctx.font = `600 ${fitted.fs}px Montserrat, Arial, sans-serif`;
            ctx.fillText(fitted.lines[0], x0 + (midX - x0) / 2, cy);
        }
        if (masterText) {
            const fitted = fitValueLines(ctx, masterText, maxW, maxH, baseFs);
            ctx.font = `600 ${fitted.fs}px Montserrat, Arial, sans-serif`;
            ctx.fillText(fitted.lines[0], midX + (x1 - midX) / 2, cy);
        }
    }

    return factory.toDataURL('image/png');
}

/* ── Render ulang semua sticker (dipanggil saat load & saat ukuran diganti) ── */
const stickerData = {}; // id -> { dataUrl, wmm, hmm, filename }

function renderAll() {
    loadLogo(logo => {
        document.querySelectorAll('.sticker-block').forEach(block => {
            const id = block.dataset.id;
            const data = {
                kategori: block.dataset.kategori,
                alat: block.dataset.alat, merk: block.dataset.merk, kapasitas: block.dataset.kapasitas,
                kode: block.dataset.kode, dept: block.dataset.dept, tgl: block.dataset.tgl,
                pelaksana: block.dataset.pelaksana, beda: block.dataset.beda, nomor: block.dataset.nomor,
                pengukuran: JSON.parse(block.dataset.pengukuran || '[]'),
            };
            // Ukuran fisik dihitung per item — rasio berbeda per kategori
            // (lihat currentSizeFor()), jadi tidak bisa dihitung sekali di luar loop.
            const size = currentSizeFor(data.kategori);
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

/* ── Cetak: isi #printStage di halaman ini sendiri, atur ukuran fisik
   halaman (@page) sesuai ukuran sticker (mm), lalu langsung panggil
   window.print() — browser langsung menampilkan dialog cetak native,
   TANPA membuka tab/window baru sama sekali. Sisi kanan tetap sama
   seperti sebelumnya: yang dicetak cuma <img> hasil canvas, jadi hasil
   cetak selalu WYSIWYG. */
function setPrintPageSize(wmm, hmm) {
    let styleEl = document.getElementById('dynamicPrintPageStyle');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'dynamicPrintPageStyle';
        document.head.appendChild(styleEl);
    }
    // Catatan: satu @page hanya bisa punya satu ukuran. Kalau item yang
    // dicetak sekaligus (Cetak Semua) berbeda ukuran fisiknya (mis. campur
    // Timbangan & Thermometer, atau ukuran cetak beda), semua halaman akan
    // memakai ukuran item PERTAMA. Untuk hasil terbaik saat mencampur
    // kategori, cetak per-item lewat "Cetak ini" satu-satu.
    styleEl.textContent = `@media print { @page { size: ${wmm}mm ${hmm}mm; margin: 0; } }`;
}

function printViaStage(items) {
    if (!items.length) return;
    const stage = document.getElementById('printStage');
    // width/height (mm) diisi eksplisit per item (bukan 100% dari kertas) —
    // supaya ukuran & posisi sticker tetap benar (menempel di pojok
    // kiri-atas, ukuran sebenarnya) walau kertas fisik yang dipakai
    // printer bukan ukuran khusus sticker (mis. lagi tes pakai kertas A4).
    stage.innerHTML = items.map(it =>
        `<div class="p" style="width:${it.wmm}mm;height:${it.hmm}mm;"><img src="${it.dataUrl}" alt="sticker"></div>`
    ).join('');
    setPrintPageSize(items[0].wmm, items[0].hmm);
    window.print();
}

// Bersihkan panggung cetak setelah dialog print ditutup (baik dicetak
// maupun dibatalkan), supaya tidak menyimpan data gambar besar di DOM.
window.addEventListener('afterprint', function () {
    document.getElementById('printStage').innerHTML = '';
});

function printOne(id) {
    const d = stickerData[id];
    if (!d) return;
    printViaStage([{ dataUrl: d.dataUrl, wmm: d.wmm, hmm: d.hmm }]);
}

function printAll() {
    const items = Object.values(stickerData).map(d => ({ dataUrl: d.dataUrl, wmm: d.wmm, hmm: d.hmm }));
    printViaStage(items);
}
</script>

</body>
</html>