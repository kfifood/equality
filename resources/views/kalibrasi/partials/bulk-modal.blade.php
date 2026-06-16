<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-table me-2"></i>Input Massal Kalibrasi
        <span class="badge bg-primary ms-2" id="rowCountBadge">1 baris</span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<style>
/* ── Header bersama ── */
.bulk-header-section {
    background: #f8f9fc;
    border: 1px solid #e2e5f1;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 16px;
}
.bulk-header-section label { font-weight: 600; font-size: .85rem; color: #495057; }

/* ── Tabel baris ── */
.bulk-table-wrap { overflow-x: auto; }
#bulkTable {
    min-width: 960px;
    font-size: .875rem;
    border-collapse: separate;
    border-spacing: 0;
}
#bulkTable thead th {
    background: #eef0fd;
    color: #4361EE;
    font-weight: 600;
    padding: 8px 10px;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 2;
}
#bulkTable tbody tr:hover { background: #f5f7ff; }
#bulkTable td { padding: 6px 8px; vertical-align: middle; }
#bulkTable td input,
#bulkTable td select { font-size: .85rem; padding: 4px 8px; }

/* ── Custom searchable select dalam table ── */
.bts-wrap { position: relative; min-width: 200px; }
.bts-trigger {
    width: 100%;
    padding: 4px 8px;
    font-size: .85rem;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    cursor: pointer;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
    white-space: nowrap;
    overflow: hidden;
}
.bts-trigger:focus,
.bts-trigger.open { border-color: #86b7fe; box-shadow: 0 0 0 .15rem rgba(13,110,253,.2); outline:none; }
.bts-placeholder { color: #999; overflow: hidden; text-overflow: ellipsis; }
.bts-label { overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
.bts-arrow { font-size: .65rem; color: #888; flex-shrink: 0; margin-left: 4px; }
.bts-dropdown {
    position: fixed;   /* fixed supaya tidak terpotong tabel */
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    z-index: 9999;
    display: none;
    width: 280px;
    max-height: 240px;
    flex-direction: column;
}
.bts-dropdown.open { display: flex; }
.bts-search-box { padding: 6px 8px; border-bottom: 1px solid #eee; flex-shrink: 0; }
.bts-search-box input {
    width: 100%;
    padding: 4px 8px;
    font-size: .85rem;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    outline: none;
}
.bts-search-box input:focus { border-color: #86b7fe; }
.bts-list { overflow-y: auto; flex: 1; }
.bts-option {
    padding: 6px 10px;
    cursor: pointer;
    font-size: .85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.bts-option:hover { background: #e8f0fe; }
.bts-option.active { background: #4361EE; color: #fff; }
.bts-no-result { padding: 8px 10px; color: #888; font-size: .8rem; font-style: italic; }

/* ── Tombol hapus baris ── */
.btn-del-row { padding: 3px 8px; font-size: .8rem; }

/* ── Validasi error per baris ── */
.row-error td { background: #fff5f5 !important; }
.row-error-msg { color: #dc3545; font-size: .75rem; display: none; }
</style>

<form id="bulkKalibrasiForm">
@csrf

    {{-- ── SECTION ATAS: Field bersama semua baris ── --}}
    <div class="modal-body pb-2">
        <div class="bulk-header-section">
            <div class="row g-3">
                <div class="col-md-3">
                    <label><i class="bi bi-calendar3 me-1"></i>Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-sm" id="shared_tanggal"
                           value="{{ date('Y-m-d') }}" required>
                    <div class="form-text">Berlaku untuk semua baris</div>
                </div>
                <div class="col-md-3">
                    <label><i class="bi bi-building me-1"></i>Dept / Bagian</label>
                    <input type="text" class="form-control form-control-sm" id="shared_dept"
                           placeholder="QC, Produksi, Lab...">
                    <div class="form-text">Berlaku untuk semua baris (bisa dioverride per baris)</div>
                </div>
                <div class="col-md-3">
                    <label><i class="bi bi-person me-1"></i>Pelaksana</label>
                    <input type="text" class="form-control form-control-sm" id="shared_pelaksana"
                           placeholder="Nama teknisi / lembaga">
                    <div class="form-text">Berlaku untuk semua baris (bisa dioverride)</div>
                </div>
                <div class="col-md-3">
                    <label><i class="bi bi-check2-circle me-1"></i>Hasil Default</label>
                    <select class="form-select form-select-sm" id="shared_hasil">
                        <option value="Lulus" selected>Lulus</option>
                        <option value="Tidak Lulus">Tidak Lulus</option>
                        <option value="">— tidak diset —</option>
                    </select>
                    <div class="form-text">Bisa diubah per baris</div>
                </div>
            </div>
        </div>

        {{-- ── TABEL BARIS INPUT ── --}}
        <div class="bulk-table-wrap" style="max-height:380px; overflow-y:auto;">
            <table class="table table-bordered mb-0" id="bulkTable">
                <thead>
                    <tr>
                        <th width="34" class="text-center">#</th>
                        <th style="min-width:210px;">Timbangan <span class="text-danger">*</span></th>
                        <th style="min-width:140px;">Certificate No.</th>
                        <th style="min-width:100px;">Beda Maks.</th>
                        <th style="min-width:90px;">Hasil</th>
                        <th style="min-width:110px;">Dept / Bagian <span class="text-muted" style="font-size:.75em;">(override)</span></th>
                        <th style="min-width:120px;">Pelaksana <span class="text-muted" style="font-size:.75em;">(override)</span></th>
                        <th style="min-width:140px;">Catatan</th>
                        <th width="38"></th>
                    </tr>
                </thead>
                <tbody id="bulkTableBody">
                    {{-- baris diisi JS --}}
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center gap-2 mt-3">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBulkRow()">
                <i class="bi bi-plus-circle me-1"></i>Tambah Baris
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addBulkRows(5)">
                <i class="bi bi-plus-square me-1"></i>+5 Baris
            </button>
            <span class="text-muted small ms-2" id="bulkRowInfo">1 baris</span>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnBulkSave">
            <i class="bi bi-save me-1"></i>Simpan Semua
        </button>
    </div>
</form>

<script>
(function () {
    // ── Data timbangan dari server ──────────────────────────────────────────
    const TIMBANGAN = @json($timbanganJson);
    let rowIdx = 0;
    let openDropdown = null; // track dropdown yang sedang terbuka

    // ── Render satu opsi di dropdown ──────────────────────────────────────
    function renderDropdownOptions(listEl, kw, selectedId, rowId) {
        const keyword  = (kw || '').toLowerCase();
        const filtered = keyword
            ? TIMBANGAN.filter(t => t.label.toLowerCase().includes(keyword))
            : TIMBANGAN;

        listEl.innerHTML = '';
        if (!filtered.length) {
            listEl.innerHTML = '<div class="bts-no-result">Tidak ditemukan</div>';
            return;
        }
        filtered.forEach(t => {
            const div  = document.createElement('div');
            div.className = 'bts-option' + (String(t.id) === String(selectedId) ? ' active' : '');
            div.textContent = t.label;
            div.addEventListener('mousedown', e => {
                e.preventDefault();
                pickTimbangan(rowId, t);
                closeOpenDropdown();
            });
            listEl.appendChild(div);
        });
    }

    // ── Pilih timbangan untuk baris tertentu ───────────────────────────────
    function pickTimbangan(rowId, t) {
        const row    = document.getElementById('row-' + rowId);
        if (!row) return;
        row.querySelector('.bts-hidden-id').value   = t.id;
        const lbl = row.querySelector('.bts-label-text');
        lbl.textContent = t.label;
        lbl.classList.remove('bts-placeholder');

        // Auto-fill certificate_number jika kolom masih kosong
        const certInput = row.querySelector('.col-cert');
        if (certInput && !certInput.value) certInput.value = t.certificate;
    }

    // ── Tutup dropdown yang terbuka ───────────────────────────────────────
    function closeOpenDropdown() {
        if (openDropdown) {
            openDropdown.classList.remove('open');
            openDropdown = null;
        }
    }

    // ── Posisikan dropdown tepat di bawah trigger ─────────────────────────
    function positionDropdown(trigger, dropEl) {
        const rect   = trigger.getBoundingClientRect();
        dropEl.style.top    = (rect.bottom + window.scrollY + 2) + 'px';
        dropEl.style.left   = (rect.left + window.scrollX)       + 'px';
        dropEl.style.width  = Math.max(280, rect.width) + 'px';
    }

    // ── Buat satu baris input ──────────────────────────────────────────────
    function createRow() {
        const rid = rowIdx++;
        const sharedHasil = document.getElementById('shared_hasil')?.value || 'Lulus';

        const tr  = document.createElement('tr');
        tr.id     = 'row-' + rid;
        tr.dataset.rid = rid;

        tr.innerHTML = `
            <td class="text-center text-muted" style="font-size:.8rem;">${rowIdx}</td>

            {{-- Searchable select timbangan --}}
            <td>
                <input type="hidden" class="bts-hidden-id" name="rows[${rid}][timbangan_id]">
                <div class="bts-wrap">
                    <div class="bts-trigger" tabindex="0" data-rid="${rid}">
                        <span class="bts-label-text bts-placeholder">-- Pilih --</span>
                        <span class="bts-arrow">▼</span>
                    </div>
                    <div class="bts-dropdown" id="bts-drop-${rid}">
                        <div class="bts-search-box">
                            <input type="text" placeholder="🔍 Cari kode / merk..." data-rid="${rid}" class="bts-search-input">
                        </div>
                        <div class="bts-list" id="bts-list-${rid}"></div>
                    </div>
                </div>
            </td>

            {{-- Certificate No --}}
            <td><input type="text" class="form-control col-cert" name="rows[${rid}][certificate_number]" placeholder="Opsional"></td>

            {{-- Beda Maksimum --}}
            <td><input type="text" class="form-control" name="rows[${rid}][beda_maksimum]" placeholder="mis. ±0.5 g"></td>

            {{-- Hasil --}}
            <td>
                <select class="form-select" name="rows[${rid}][hasil]">
                    <option value="Lulus"       ${sharedHasil === 'Lulus'       ? 'selected' : ''}>Lulus</option>
                    <option value="Tidak Lulus" ${sharedHasil === 'Tidak Lulus' ? 'selected' : ''}>Tidak Lulus</option>
                    <option value=""            ${sharedHasil === ''            ? 'selected' : ''}>—</option>
                </select>
            </td>

            {{-- Dept override --}}
            <td><input type="text" class="form-control col-dept" name="rows[${rid}][dept_bagian]" placeholder="(gunakan default)"></td>

            {{-- Pelaksana override --}}
            <td><input type="text" class="form-control col-pelaksana" name="rows[${rid}][pelaksana]" placeholder="(gunakan default)"></td>

            {{-- Catatan --}}
            <td><input type="text" class="form-control" name="rows[${rid}][catatan]" placeholder="Opsional"></td>

            {{-- Hapus --}}
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-del-row" onclick="deleteRow(${rid})" title="Hapus baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        // Render daftar timbangan
        const listEl = tr.querySelector('#bts-list-' + rid);
        renderDropdownOptions(listEl, '', null, rid);

        // Event: trigger click → buka dropdown
        const trigger = tr.querySelector('.bts-trigger');
        const dropEl  = tr.querySelector('#bts-drop-' + rid);
        const searchI = tr.querySelector('.bts-search-input');

        trigger.addEventListener('click', e => {
            e.stopPropagation();
            const isOpen = dropEl.classList.contains('open');
            closeOpenDropdown();
            if (!isOpen) {
                document.body.appendChild(dropEl); // pindah ke body supaya tidak terpotong
                positionDropdown(trigger, dropEl);
                dropEl.classList.add('open');
                openDropdown = dropEl;
                setTimeout(() => searchI.focus(), 30);
            }
        });

        trigger.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); trigger.click(); }
        });

        searchI.addEventListener('input', function () {
            const selId = tr.querySelector('.bts-hidden-id').value;
            renderDropdownOptions(listEl, this.value, selId, rid);
        });

        return tr;
    }

    // ── Tutup dropdown jika klik di luar ──────────────────────────────────
    document.addEventListener('click', () => closeOpenDropdown());

    // Reposisi dropdown saat scroll
    document.querySelector('.bulk-table-wrap')?.addEventListener('scroll', () => {
        if (openDropdown) {
            // cari trigger dari rid di openDropdown id
            const rid = openDropdown.id.replace('bts-drop-', '');
            const trigger = document.querySelector(`[data-rid="${rid}"]`);
            if (trigger) positionDropdown(trigger, openDropdown);
        }
    });

    // ── Hapus baris ───────────────────────────────────────────────────────
    window.deleteRow = function (rid) {
        const row = document.getElementById('row-' + rid);
        if (row) row.remove();
        updateRowCount();
    };

    // ── Tambah baris ──────────────────────────────────────────────────────
    window.addBulkRow = function () {
        document.getElementById('bulkTableBody').appendChild(createRow());
        updateRowCount();
    };

    window.addBulkRows = function (n) {
        const tbody = document.getElementById('bulkTableBody');
        for (let i = 0; i < n; i++) tbody.appendChild(createRow());
        updateRowCount();
    };

    // ── Update counter ────────────────────────────────────────────────────
    function updateRowCount() {
        const n    = document.getElementById('bulkTableBody').children.length;
        const txt  = n + ' baris';
        document.getElementById('rowCountBadge').textContent = txt;
        document.getElementById('bulkRowInfo').textContent   = txt;
        // update nomor urut
        document.querySelectorAll('#bulkTableBody tr').forEach((tr, i) => {
            tr.querySelector('td:first-child').textContent = i + 1;
        });
    }

    // ── Baris awal ───────────────────────────────────────────────────────
    window.addBulkRow();
    window.addBulkRow();
    window.addBulkRow();

    // ── Submit form ───────────────────────────────────────────────────────
    document.getElementById('bulkKalibrasiForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const sharedTgl   = document.getElementById('shared_tanggal').value;
        const sharedDept  = document.getElementById('shared_dept').value.trim();
        const sharedPel   = document.getElementById('shared_pelaksana').value.trim();

        if (!sharedTgl) {
            Swal.fire({ icon: 'warning', title: 'Tanggal wajib diisi', text: 'Isi tanggal pelaksanaan terlebih dahulu.' });
            return;
        }

        // Kumpulkan data baris
        const rows = [];
        let hasError = false;

        document.querySelectorAll('#bulkTableBody tr').forEach((tr, i) => {
            const tid = tr.querySelector('.bts-hidden-id')?.value;
            if (!tid) {
                tr.classList.add('row-error');
                hasError = true;
                return;
            }
            tr.classList.remove('row-error');

            rows.push({
                timbangan_id:        tid,
                tanggal_pelaksanaan: sharedTgl,
                dept_bagian:         tr.querySelector('[name$="[dept_bagian]"]')?.value.trim() || sharedDept,
                pelaksana:           tr.querySelector('[name$="[pelaksana]"]')?.value.trim()   || sharedPel,
                certificate_number:  tr.querySelector('[name$="[certificate_number]"]')?.value.trim() || '',
                beda_maksimum:       tr.querySelector('[name$="[beda_maksimum]"]')?.value.trim()      || '',
                hasil:               tr.querySelector('[name$="[hasil]"]')?.value || '',
                catatan:             tr.querySelector('[name$="[catatan]"]')?.value.trim()            || '',
            });
        });

        if (hasError) {
            Swal.fire({ icon: 'warning', title: 'Ada baris kosong', text: 'Pilih timbangan untuk setiap baris yang diisi (baris merah).' });
            return;
        }

        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Tidak ada data', text: 'Tambahkan minimal satu baris.' });
            return;
        }

        const btn = document.getElementById('btnBulkSave');
        btn.disabled  = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menyimpan ' + rows.length + ' data...';

        const csrf = document.querySelector('[name="_token"]').value;

        try {
            const res = await fetch('{{ route("kalibrasi.storeBulk") }}', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body   : JSON.stringify({ rows }),
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon : 'success',
                    title: 'Berhasil',
                    text : data.message,
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => { $('#dynamicModal').modal('hide'); location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Gagal menyimpan data.' });
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Semua';
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan koneksi.' });
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Semua';
        }
    });

})();
</script>