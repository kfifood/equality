<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-file-earmark-excel me-2"></i>Import Kalibrasi dari Excel
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">

    {{-- Langkah panduan --}}
    <div class="alert alert-info d-flex gap-3 align-items-start mb-4">
        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
        <div>
            <strong>Cara Import:</strong>
            <ol class="mb-0 mt-1 ps-3">
                <li>Download template Excel di bawah.</li>
                <li>Isi data sesuai kolom yang tersedia (kolom <em>kode_asset</em> wajib diisi & harus cocok dengan data sistem).</li>
                <li>Simpan file Excel, lalu upload di sini.</li>
            </ol>
        </div>
    </div>

    {{-- Download template --}}
    <div class="mb-4">
        <a href="{{ route('kalibrasi.importTemplate') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Template Excel
        </a>
        <span class="text-muted small ms-3">Format: .xlsx | Baris pertama = header, isi mulai baris kedua</span>
    </div>

    <hr>

    {{-- Upload form --}}
    <form id="importKalibrasiForm" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Upload File Excel <span class="text-danger">*</span></label>
            <input type="file" class="form-control" id="importFile" name="file"
                   accept=".xlsx,.xls" required>
            <div class="form-text">Hanya file .xlsx / .xls, maksimal 5 MB.</div>
        </div>

        {{-- Preview area --}}
        <div id="importPreviewArea" class="d-none">
            <div class="alert alert-secondary mb-2 py-2 px-3 d-flex align-items-center gap-2">
                <i class="bi bi-table"></i>
                <span id="importPreviewInfo"></span>
            </div>
            <div class="table-responsive" style="max-height: 260px; overflow-y:auto;">
                <table class="table table-sm table-bordered table-striped mb-0" id="importPreviewTable">
                    <thead style="background:#eef0fd; color:#4361EE; position:sticky; top:0;">
                        <tr id="importPreviewHead"></tr>
                    </thead>
                    <tbody id="importPreviewBody"></tbody>
                </table>
            </div>
        </div>

        {{-- Error hasil validasi server --}}
        <div id="importErrorArea" class="d-none mt-3">
            <div class="alert alert-danger mb-2 py-2 px-3">
                <strong><i class="bi bi-exclamation-triangle me-1"></i>Ditemukan error:</strong>
                <ul class="mb-0 mt-1 ps-3 small" id="importErrorList"></ul>
            </div>
        </div>
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="button" class="btn btn-success" id="btnImportSave" disabled>
        <i class="bi bi-upload me-1"></i>Import Sekarang
    </button>
</div>

<script>
(function () {
    // ── Preview saat file dipilih (client-side, pakai SheetJS CDN) ──────────
    // SheetJS sudah tersedia bila diload di layout; kalau belum, load dynamic.
    function ensureSheetJS(cb) {
        if (window.XLSX) { cb(); return; }
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    const fileInput   = document.getElementById('importFile');
    const previewArea = document.getElementById('importPreviewArea');
    const previewInfo = document.getElementById('importPreviewInfo');
    const previewHead = document.getElementById('importPreviewHead');
    const previewBody = document.getElementById('importPreviewBody');
    const errorArea   = document.getElementById('importErrorArea');
    const errorList   = document.getElementById('importErrorList');
    const btnSave     = document.getElementById('btnImportSave');

    fileInput.addEventListener('change', function () {
        errorArea.classList.add('d-none');
        previewArea.classList.add('d-none');
        btnSave.disabled = true;

        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: 'Maksimal 5 MB.' });
            this.value = '';
            return;
        }

        ensureSheetJS(() => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wb    = XLSX.read(e.target.result, { type: 'binary', cellDates: true });
                const ws    = wb.Sheets[wb.SheetNames[0]];
                const data  = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

                if (data.length < 2) {
                    Swal.fire({ icon: 'warning', title: 'File kosong', text: 'Tidak ada data di bawah header.' });
                    return;
                }

                // Render header
                previewHead.innerHTML = '';
                data[0].forEach(h => {
                    const th = document.createElement('th');
                    th.textContent = h;
                    previewHead.appendChild(th);
                });

                // Render maks 10 baris preview
                previewBody.innerHTML = '';
                const maxPreview = Math.min(data.length - 1, 10);
                for (let i = 1; i <= maxPreview; i++) {
                    const tr = document.createElement('tr');
                    data[0].forEach((_, ci) => {
                        const td = document.createElement('td');
                        const val = data[i][ci];
                        // Format tanggal jika Date object
                        if (val instanceof Date) {
                            td.textContent = val.toLocaleDateString('id-ID');
                        } else {
                            td.textContent = val ?? '';
                        }
                        tr.appendChild(td);
                    });
                    previewBody.appendChild(tr);
                }

                const totalRows = data.length - 1;
                previewInfo.textContent = `Preview ${Math.min(10, totalRows)} dari ${totalRows} baris data.`;
                previewArea.classList.remove('d-none');
                btnSave.disabled = false;
            };
            reader.readAsBinaryString(file);
        });
    });

    // ── Kirim file ke server ───────────────────────────────────────────────
    btnSave.addEventListener('click', async function () {
        const file = fileInput.files[0];
        if (!file) return;

        const csrf = document.querySelector('[name="_token"]').value;
        const fd   = new FormData();
        fd.append('_token', csrf);
        fd.append('file', file);

        btnSave.disabled  = true;
        btnSave.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Mengimport...';
        errorArea.classList.add('d-none');

        try {
            const res  = await fetch('{{ route("kalibrasi.import") }}', {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon : 'success',
                    title: 'Import Berhasil',
                    text : data.message,
                    timer: 3000,
                    showConfirmButton: false,
                }).then(() => { $('#dynamicModal').modal('hide'); location.reload(); });
            } else {
                // Tampilkan error validasi per baris
                errorList.innerHTML = '';
                (data.errors || [data.message]).forEach(msg => {
                    const li = document.createElement('li');
                    li.textContent = msg;
                    errorList.appendChild(li);
                });
                errorArea.classList.remove('d-none');
                btnSave.disabled  = false;
                btnSave.innerHTML = '<i class="bi bi-upload me-1"></i>Import Sekarang';
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.' });
            btnSave.disabled  = false;
            btnSave.innerHTML = '<i class="bi bi-upload me-1"></i>Import Sekarang';
        }
    });
})();
</script>