<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-pencil me-2"></i>Edit Data Kalibrasi
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<style>
.ts-wrap { position: relative; }
.ts-input {
    width: 100%;
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}
.ts-input.open,
.ts-input:focus { border-color: #86b7fe; outline: 0; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
.ts-placeholder { color: #6c757d; }
.ts-arrow { font-size: .75rem; color: #6c757d; margin-left: 8px; flex-shrink: 0; }
.ts-dropdown {
    position: absolute;
    top: calc(100% + 2px);
    left: 0; right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    z-index: 9999;
    display: none;
    max-height: 260px;
    flex-direction: column;
}
.ts-dropdown.open { display: flex; }
.ts-search-box {
    padding: 8px;
    border-bottom: 1px solid #e9ecef;
    flex-shrink: 0;
}
.ts-search-box input {
    width: 100%;
    padding: .3rem .6rem;
    font-size: .9rem;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    outline: none;
}
.ts-search-box input:focus { border-color: #86b7fe; box-shadow: 0 0 0 .2rem rgba(13,110,253,.2); }
.ts-list { overflow-y: auto; flex: 1; }
.ts-option {
    padding: .45rem .75rem;
    cursor: pointer;
    font-size: .95rem;
    color: #212529;
}
.ts-option:hover  { background: #e8f0fe; color: #1a3fcc; }
.ts-option.selected { background: #4361EE; color: #fff; }
.ts-no-result { padding: .6rem .75rem; color: #6c757d; font-size: .9rem; font-style: italic; }
</style>

<form id="editForm" action="{{ route('kalibrasi.update', $kalibrasi->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">

        {{-- Peralatan --}}
        <div class="mb-3">
            <label class="form-label">Peralatan <span class="text-danger">*</span></label>

            {{-- Select asli (hidden) — tetap dipakai untuk submit --}}
            <select id="peralatan_id" name="peralatan_id" required style="display:none;">
                <option value="">-- Pilih Peralatan --</option>
                @foreach($peralatanList as $p)
                    <option value="{{ $p->id }}"
                        data-certificate="{{ $p->certificate_number ?? '' }}"
                        {{ old('peralatan_id', $kalibrasi->peralatan_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->kode_asset }} — {{ $p->merk_tipe_lengkap }}
                    </option>
                @endforeach
            </select>

            {{-- Custom dropdown --}}
            <div class="ts-wrap" id="e_wrap">
                <div class="ts-input" id="e_input" tabindex="0">
                    <span id="e_label" class="ts-placeholder">-- Pilih Peralatan --</span>
                    <span class="ts-arrow">▼</span>
                </div>
                <div class="ts-dropdown" id="e_dropdown">
                    <div class="ts-search-box">
                        <input type="text" id="e_search"
                               placeholder="🔍 Cari kode / merk peralatan..."
                               autocomplete="off">
                    </div>
                    <div class="ts-list" id="e_list"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal_pelaksanaan" class="form-label">
                        Tanggal Pelaksanaan <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="tanggal_pelaksanaan"
                           name="tanggal_pelaksanaan"
                           value="{{ old('tanggal_pelaksanaan', $kalibrasi->tanggal_pelaksanaan?->format('Y-m-d')) }}"
                           required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="hasil" class="form-label">Hasil Kalibrasi</label>
                    <select class="form-select" id="hasil" name="hasil">
                        <option value="">-- Pilih Hasil --</option>
                        <option value="Lulus"
                            {{ old('hasil', $kalibrasi->hasil) == 'Lulus' ? 'selected' : '' }}>
                            Lulus
                        </option>
                        <option value="Tidak Lulus"
                            {{ old('hasil', $kalibrasi->hasil) == 'Tidak Lulus' ? 'selected' : '' }}>
                            Tidak Lulus
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="dept_bagian" class="form-label">Dept / Bagian</label>
                    <input type="text" class="form-control" id="dept_bagian" name="dept_bagian"
                           value="{{ old('dept_bagian', $kalibrasi->dept_bagian) }}"
                           placeholder="Contoh: QC, Produksi, Lab">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="pelaksana" class="form-label">Pelaksana</label>
                    <input type="text" class="form-control" id="pelaksana" name="pelaksana"
                           value="{{ old('pelaksana', $kalibrasi->pelaksana) }}"
                           placeholder="Nama teknisi atau lembaga kalibrasi">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="certificate_number" class="form-label">Certificate Number</label>
                    <input type="text" class="form-control" id="certificate_number"
                           name="certificate_number"
                           value="{{ old('certificate_number', $kalibrasi->certificate_number) }}"
                           placeholder="Contoh: CAL-2024-001">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="beda_maksimum" class="form-label">
                        Beda Maksimum
                        <span class="text-muted small">(opsional)</span>
                    </label>
                    <input type="text" class="form-control" id="beda_maksimum"
                           name="beda_maksimum"
                           value="{{ old('beda_maksimum', $kalibrasi->beda_maksimum) }}"
                           placeholder="Contoh: ±0.5 g">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="catatan" class="form-label">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3"
                      placeholder="Catatan tambahan (opsional)">{{ old('catatan', $kalibrasi->catatan) }}</textarea>
        </div>

        <div class="alert alert-light border small text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Dibuat: {{ $kalibrasi->created_at?->format('d/m/Y H:i') ?? '-' }}
            &nbsp;|&nbsp;
            Terakhir diubah: {{ $kalibrasi->updated_at?->format('d/m/Y H:i') ?? '-' }}
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Update
        </button>
    </div>
</form>

<script>
(function () {
    var selectEl  = document.getElementById('peralatan_id');
    var inputEl   = document.getElementById('e_input');
    var dropdown  = document.getElementById('e_dropdown');
    var searchEl  = document.getElementById('e_search');
    var listEl    = document.getElementById('e_list');
    var labelEl   = document.getElementById('e_label');
    var certInput = document.getElementById('certificate_number');

    // Ambil semua option dari select asli
    var options = [];
    Array.prototype.forEach.call(selectEl.options, function (opt) {
        if (!opt.value) return;
        options.push({
            value      : opt.value,
            text       : opt.text.trim(),
            certificate: opt.getAttribute('data-certificate') || ''
        });
    });

    // Set nilai awal jika sudah ada selected (mode edit)
    var selectedValue = selectEl.value || '';
    if (selectedValue) {
        var current = options.find(function (o) { return o.value === selectedValue; });
        if (current) {
            labelEl.textContent = current.text;
            labelEl.classList.remove('ts-placeholder');
        }
    }

    function renderList(kw) {
        var keyword = (kw || '').toLowerCase().trim();
        var filtered = keyword
            ? options.filter(function (o) { return o.text.toLowerCase().indexOf(keyword) !== -1; })
            : options;

        listEl.innerHTML = '';

        if (!filtered.length) {
            listEl.innerHTML = '<div class="ts-no-result">Tidak ditemukan hasil untuk "' + kw + '"</div>';
            return;
        }

        filtered.forEach(function (o) {
            var div = document.createElement('div');
            div.className = 'ts-option' + (o.value === selectedValue ? ' selected' : '');
            div.textContent = o.text;
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectedValue       = o.value;
                selectEl.value      = o.value;
                labelEl.textContent = o.text;
                labelEl.classList.remove('ts-placeholder');
                certInput.value     = o.certificate;
                closeDropdown();
            });
            listEl.appendChild(div);
        });
    }

    function openDropdown() {
        searchEl.value = '';
        renderList('');
        dropdown.classList.add('open');
        inputEl.classList.add('open');
        setTimeout(function () { searchEl.focus(); }, 50);
    }

    function closeDropdown() {
        dropdown.classList.remove('open');
        inputEl.classList.remove('open');
    }

    inputEl.addEventListener('click', function () {
        dropdown.classList.contains('open') ? closeDropdown() : openDropdown();
    });

    inputEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDropdown(); }
    });

    searchEl.addEventListener('input', function () {
        renderList(this.value);
    });

    document.addEventListener('mousedown', function (e) {
        var wrap = document.getElementById('e_wrap');
        if (wrap && !wrap.contains(e.target)) closeDropdown();
    });

    renderList('');
})();
</script>