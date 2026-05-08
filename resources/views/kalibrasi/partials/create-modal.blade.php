<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-plus-circle me-2"></i>Tambah Data Kalibrasi
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

<form id="createForm" action="{{ route('kalibrasi.store') }}" method="POST">
    @csrf
    <div class="modal-body">

        {{-- Timbangan --}}
        <div class="mb-3">
            <label class="form-label">Timbangan <span class="text-danger">*</span></label>

            {{-- Select asli (hidden) — tetap dipakai untuk submit --}}
            <select id="timbangan_id" name="timbangan_id" required style="display:none;">
                <option value="">-- Pilih Timbangan --</option>
                @foreach($timbanganList as $t)
                    <option value="{{ $t->id }}"
                        data-certificate="{{ $t->certificate_number ?? '' }}">
                        {{ $t->kode_asset }} — {{ $t->merk_tipe_no_seri }}
                    </option>
                @endforeach
            </select>

            {{-- Custom dropdown --}}
            <div class="ts-wrap" id="c_wrap">
                <div class="ts-input" id="c_input" tabindex="0">
                    <span id="c_label" class="ts-placeholder">-- Pilih Timbangan --</span>
                    <span class="ts-arrow">▼</span>
                </div>
                <div class="ts-dropdown" id="c_dropdown">
                    <div class="ts-search-box">
                        <input type="text" id="c_search"
                               placeholder="🔍 Cari kode / merk timbangan..."
                               autocomplete="off">
                    </div>
                    <div class="ts-list" id="c_list"></div>
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
                           name="tanggal_pelaksanaan" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="hasil" class="form-label">Hasil Kalibrasi</label>
                    <select class="form-select" id="hasil" name="hasil">
                        <option value="">-- Pilih Hasil --</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Tidak Lulus">Tidak Lulus</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="dept_bagian" class="form-label">Dept / Bagian</label>
                    <input type="text" class="form-control" id="dept_bagian" name="dept_bagian"
                           placeholder="Contoh: QC, Produksi, Lab">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="pelaksana" class="form-label">Pelaksana</label>
                    <input type="text" class="form-control" id="pelaksana" name="pelaksana"
                           placeholder="Nama teknisi atau lembaga kalibrasi">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="certificate_number" class="form-label">
                        Certificate Number
                        <span class="badge bg-secondary bg-opacity-75 ms-1" style="font-size:0.65em;">
                            auto dari timbangan
                        </span>
                    </label>
                    <input type="text" class="form-control" id="certificate_number"
                           name="certificate_number"
                           placeholder="Otomatis terisi saat pilih timbangan">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="beda_maksimum" class="form-label">
                        Beda Maksimum
                        <span class="text-muted small">(opsional)</span>
                    </label>
                    <input type="text" class="form-control" id="beda_maksimum"
                           name="beda_maksimum" placeholder="Contoh: ±0.5 g">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="catatan" class="form-label">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3"
                      placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan
        </button>
    </div>
</form>

<script>
(function () {
    var selectEl  = document.getElementById('timbangan_id');
    var inputEl   = document.getElementById('c_input');
    var dropdown  = document.getElementById('c_dropdown');
    var searchEl  = document.getElementById('c_search');
    var listEl    = document.getElementById('c_list');
    var labelEl   = document.getElementById('c_label');
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

    var selectedValue = '';

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

    // Tutup jika klik di luar
    document.addEventListener('mousedown', function handler(e) {
        var wrap = document.getElementById('c_wrap');
        if (wrap && !wrap.contains(e.target)) closeDropdown();
    });

    renderList('');
})();
</script>