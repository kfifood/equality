<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-arrow-right-circle me-2"></i>Catat Penggunaan Alat
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="createPenggunaanForm" action="{{ route('penggunaan.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Pilih Alat <span class="text-danger">*</span></label>
                    <select name="peralatan_id" class="form-select select2-peralatan" id="peralatanSelect" required>
                        <option value="">Pilih Alat</option>
                        @foreach($peralatan as $item)
                            <option value="{{ $item->id }}"
                                {{ $selectedPeralatan && $selectedPeralatan->id == $item->id ? 'selected' : '' }}
                                data-lokasi="{{ $item->status_line ? $item->status_line : 'Lab' }}"
                                data-kode="{{ $item->kode_asset }}"
                                data-merk="{{ $item->merk_tipe_lengkap }}"
                                data-kondisi="{{ $item->kondisi_saat_ini }}">
                                {{ $item->kode_asset }} - {{ $item->merk_tipe_lengkap }}
                                @if($item->status_line)
                                    (Sedang di {{ $item->status_line }})
                                @else
                                    (Lab)
                                @endif
                                - {{ $item->kondisi_saat_ini }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text" id="lokasiInfo"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Line Tujuan <span class="text-danger">*</span></label>
                    <select name="line_tujuan" class="form-select select2-line" id="lineTujuanSelect" required>
                        <option value="">Pilih Line</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->nama_line }}">
                                {{ $line->nama_line }} ({{ $line->department }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tanggal Pemakaian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_pemakaian" class="form-control"
                           value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">PIC <span class="text-danger">*</span></label>

                    {{-- Hidden input yang dikirim ke server --}}
                    <input type="hidden" name="pic" id="picValue">

                    {{-- Dropdown PIC (difilter berdasarkan line yang dipilih) --}}
                    <select class="form-select select2-pic" id="picSelect">
                        <option value="">— Pilih Line terlebih dahulu —</option>
                    </select>

                    {{-- Data semua PIC aktif untuk JS --}}
                    <div id="allPicData" class="d-none"
                         data-pics="{{ json_encode(
                             $picList->map(fn($p) => [
                                 'id'       => $p->id,
                                 'nama_pic' => $p->nama_pic,
                                 'jabatan'  => $p->jabatan ?? '',
                                 'line_id'  => $p->line_id,
                                 'line_nama'=> $p->line->nama_line ?? '',
                             ])
                         ) }}">
                    </div>

                    <div class="form-text text-muted" id="picHint">
                        <i class="bi bi-info-circle me-1"></i>Pilih Line Tujuan dulu untuk memfilter PIC.
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="2"
                      placeholder="Keterangan penggunaan (opsional)"></textarea>
        </div>

        <div class="alert alert-info mb-2">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Informasi Penggunaan:</strong><br>
                • Hanya peralatan kondisi <strong>Baik</strong> yang dapat digunakan<br>
                • Peralatan di line lain <strong>bisa dipindahkan</strong><br>
                • Daftar PIC difilter otomatis sesuai Line Tujuan yang dipilih
            </small>
        </div>

        <div class="alert alert-warning d-none" id="warningAlert">
            <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Perhatian:</strong> Peralatan sedang digunakan di line lain.
                Status penggunaan sebelumnya otomatis menjadi "Selesai".
            </small>
        </div>

        <div class="alert alert-danger d-none" id="dangerAlert">
            <small>
                <i class="bi bi-x-circle me-1"></i>
                <strong>Peringatan:</strong> Kondisi peralatan <span id="kondisiPeralatan"></span> — tidak dapat digunakan.
            </small>
        </div>

        <div class="alert alert-warning d-none" id="noPicAlert">
            <small>
                <i class="bi bi-person-x me-1"></i>
                <strong>Tidak ada PIC aktif</strong> untuk line ini.
                Silakan tambahkan PIC di menu <strong>Master Data → Data PIC</strong>.
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="bi bi-save me-1"></i>Simpan Penggunaan
        </button>
    </div>
</form>

<style>
.select2-container--default .select2-selection--single {
    height: 38px; padding: 5px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
.select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #4361EE; }
.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da; border-radius: 0.375rem; padding: 6px;
}
.select2-dropdown { border: 1px solid #ced4da; border-radius: 0.375rem; }
</style>

<script>
$(document).ready(function () {

    // ── Data PIC dari server ──────────────────────────────────────────────────
    const allPics = JSON.parse($('#allPicData').attr('data-pics') || '[]');

    // ── Init Select2 ─────────────────────────────────────────────────────────
    $('.select2-peralatan').select2({
        placeholder: 'Cari alat berdasarkan kode asset atau merk...',
        allowClear: true, width: '100%',
        dropdownParent: $('#dynamicModal'),
        templateResult: formatPeralatan,
        templateSelection: formatPeralatanSelection
    });

    $('.select2-line').select2({
        placeholder: 'Pilih line tujuan...',
        allowClear: true, width: '100%',
        dropdownParent: $('#dynamicModal')
    });

    $('.select2-pic').select2({
        placeholder: '— Pilih PIC —',
        allowClear: true, width: '100%',
        dropdownParent: $('#dynamicModal')
    });

    // ── Format Select2 Peralatan ─────────────────────────────────────────────
    function formatPeralatan(item) {
        if (!item.id) return item.text;
        const el = item.element;
        return $(
            '<div class="d-flex justify-content-between align-items-center">' +
                '<div><strong>' + el.getAttribute('data-kode') + '</strong><br>' +
                '<small class="text-muted">' + el.getAttribute('data-merk') + '</small></div>' +
                '<div class="text-end">' +
                    '<span class="badge bg-' + kondisiColor(el.getAttribute('data-kondisi')) + '">' +
                        el.getAttribute('data-kondisi') + '</span><br>' +
                    '<small class="text-muted">' + el.getAttribute('data-lokasi') + '</small>' +
                '</div>' +
            '</div>'
        );
    }
    function formatPeralatanSelection(item) {
        if (!item.id) return item.text;
        return item.element.getAttribute('data-kode') + ' — ' + item.element.getAttribute('data-merk');
    }
    function kondisiColor(k) {
        return k === 'Baik' ? 'success' : k === 'Rusak' ? 'danger' : 'warning';
    }

    // ── Saat Peralatan dipilih ────────────────────────────────────────────────
    $('#peralatanSelect').on('change', function () {
        const opt    = $(this).find('option:selected');
        const lokasi = opt.data('lokasi');
        const kondisi= opt.data('kondisi');

        if (opt.val()) {
            $('#lokasiInfo').html(
                'Lokasi saat ini: <strong>' + lokasi + '</strong> | Kondisi: <strong>' + kondisi + '</strong>'
            );
            $('#warningAlert').toggleClass('d-none', lokasi === 'Lab');
            const notBaik = kondisi !== 'Baik';
            $('#dangerAlert').toggleClass('d-none', !notBaik);
            if (notBaik) $('#kondisiPeralatan').text(kondisi);
            $('#submitBtn').prop('disabled', notBaik)
                           .toggleClass('btn-secondary', notBaik)
                           .toggleClass('btn-primary', !notBaik);
        } else {
            $('#lokasiInfo').html('');
            $('#warningAlert, #dangerAlert').addClass('d-none');
            $('#submitBtn').prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
        }
    });

    // ── Saat Line Tujuan dipilih → filter PIC ────────────────────────────────
    $('#lineTujuanSelect').on('change', function () {
        const lineNama = $(this).find('option:selected').text().replace(/\s*\(.*\)/, '').trim();
        const filtered = allPics.filter(p => p.line_nama === lineNama);

        // Rebuild opsi dropdown PIC
        $('#picSelect').empty().append('<option value="">— Pilih PIC —</option>');
        $('#picValue').val('');

        if (filtered.length === 0) {
            $('#noPicAlert').removeClass('d-none');
            $('#picHint').addClass('d-none');
        } else {
            $('#noPicAlert').addClass('d-none');
            $('#picHint').addClass('d-none');
            filtered.forEach(function (p) {
                const label = p.nama_pic + (p.jabatan ? ' (' + p.jabatan + ')' : '');
                $('#picSelect').append(
                    $('<option>', { value: p.nama_pic, text: label })
                );
            });
        }

        // Refresh Select2
        $('#picSelect').trigger('change.select2');
    });

    // ── Saat PIC dipilih → simpan ke hidden input ─────────────────────────────
    $('#picSelect').on('change', function () {
        $('#picValue').val($(this).val());
    });

    // Trigger peralatan jika sudah ada pre-selected
    $('#peralatanSelect').trigger('change');
});
</script>