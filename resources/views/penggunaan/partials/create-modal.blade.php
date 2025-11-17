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
                    <select name="timbangan_id" class="form-select select2-timbangan" id="timbanganSelect" required>
                        <option value="">Pilih Alat</option>
                        @foreach($timbangan as $item)
                            <option value="{{ $item->id }}" 
                                {{ $selectedTimbangan && $selectedTimbangan->id == $item->id ? 'selected' : '' }}
                                data-lokasi="{{ $item->status_line ? $item->status_line : 'Lab' }}"
                                data-kode="{{ $item->kode_asset }}"
                                data-merk="{{ $item->merk_tipe_no_seri }}"
                                data-kondisi="{{ $item->kondisi_saat_ini }}">
                                {{ $item->kode_asset }} - {{ $item->merk_tipe_no_seri }} 
                                @if($item->status_line)
                                    (Sedang di {{ $item->status_line }})
                                @else
                                    (Lab)
                                @endif
                                - {{ $item->kondisi_saat_ini }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text" id="lokasiInfo">
                        <!-- Info lokasi akan di-update via JavaScript -->
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Line Tujuan <span class="text-danger">*</span></label>
                    <select name="line_tujuan" class="form-select select2-line" required>
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
                    <label class="form-label">PIC</label>
                    <input type="text" name="pic" class="form-control" 
                           placeholder="Nama penanggung jawab">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" 
                      placeholder="Keterangan penggunaan (opsional)"></textarea>
        </div>

        <!-- Update alert info -->
        <div class="alert alert-info">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Informasi Penggunaan:</strong><br>
                • Hanya timbangan dengan kondisi <strong>Baik</strong> yang bisa digunakan<br>
                • Timbangan yang sedang digunakan di line lain <strong>bisa dipindahkan</strong><br>
                • Status penggunaan sebelumnya otomatis berubah menjadi "Selesai"<br>
                • Timbangan akan berpindah ke line tujuan yang baru
            </small>
        </div>

        <!-- Alert warning untuk timbangan yang sedang digunakan -->
        <div class="alert alert-warning d-none" id="warningAlert">
            <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Perhatian:</strong> Timbangan ini sedang digunakan di line lain. 
                Status penggunaan sebelumnya akan otomatis berubah menjadi "Selesai".
            </small>
        </div>

        <!-- Alert untuk timbangan tidak baik -->
        <div class="alert alert-danger d-none" id="dangerAlert">
            <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Peringatan:</strong> Timbangan ini dalam kondisi <span id="kondisiTimbangan"></span> dan tidak dapat digunakan.
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

<!-- Tambahkan CSS Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #4361EE;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 6px;
}

.select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 untuk timbangan dengan search
    $('.select2-timbangan').select2({
        placeholder: "Cari alat berdasarkan kode asset atau merk...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#dynamicModal'),
        templateResult: formatTimbangan,
        templateSelection: formatTimbanganSelection
    });

    // Initialize Select2 untuk line
    $('.select2-line').select2({
        placeholder: "Pilih line tujuan...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#dynamicModal')
    });

    // Format tampilan hasil pencarian
    function formatTimbangan(timbangan) {
        if (!timbangan.id) {
            return timbangan.text;
        }

        var $timbangan = $(
            '<div class="d-flex justify-content-between align-items-center">' +
                '<div>' +
                    '<strong>' + timbangan.element.getAttribute('data-kode') + '</strong><br>' +
                    '<small class="text-muted">' + timbangan.element.getAttribute('data-merk') + '</small>' +
                '</div>' +
                '<div class="text-end">' +
                    '<span class="badge bg-' + getKondisiColor(timbangan.element.getAttribute('data-kondisi')) + '">' + 
                        timbangan.element.getAttribute('data-kondisi') + 
                    '</span><br>' +
                    '<small class="text-muted">' + timbangan.element.getAttribute('data-lokasi') + '</small>' +
                '</div>' +
            '</div>'
        );
        return $timbangan;
    }

    // Format tampilan saat dipilih
    function formatTimbanganSelection(timbangan) {
        if (!timbangan.id) {
            return timbangan.text;
        }
        return timbangan.element.getAttribute('data-kode') + ' - ' + timbangan.element.text;
    }

    // Helper function untuk warna kondisi
    function getKondisiColor(kondisi) {
        switch(kondisi) {
            case 'Baik': return 'success';
            case 'Rusak': return 'danger';
            case 'Dalam Perbaikan': return 'warning';
            default: return 'secondary';
        }
    }

    // Update info lokasi saat timbangan dipilih
    $('#timbanganSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const lokasi = selectedOption.data('lokasi');
        const kondisi = selectedOption.data('kondisi');
        
        if (selectedOption.val()) {
            $('#lokasiInfo').html('Lokasi saat ini: <strong>' + lokasi + '</strong> | Kondisi: <strong>' + kondisi + '</strong>');
            
            // Tampilkan warning jika timbangan sedang digunakan di line
            if (lokasi !== 'Lab') {
                $('#warningAlert').removeClass('d-none');
            } else {
                $('#warningAlert').addClass('d-none');
            }

            // Tampilkan danger alert jika kondisi tidak baik
            if (kondisi !== 'Baik') {
                $('#dangerAlert').removeClass('d-none');
                $('#kondisiTimbangan').text(kondisi);
                $('#submitBtn').prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');
            } else {
                $('#dangerAlert').addClass('d-none');
                $('#submitBtn').prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
            }
        } else {
            $('#lokasiInfo').html('Pilih timbangan untuk melihat info lokasi');
            $('#warningAlert').addClass('d-none');
            $('#dangerAlert').addClass('d-none');
            $('#submitBtn').prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
        }
    });

    // Trigger change event saat modal terbuka
    $('#timbanganSelect').trigger('change');

    // Handle modal show event untuk reset Select2
    $('#dynamicModal').on('shown.bs.modal', function () {
        $('.select2-timbangan').select2({
            placeholder: "Cari alat berdasarkan kode asset atau merk...",
            allowClear: true,
            width: '100%',
            dropdownParent: $(this),
            templateResult: formatTimbangan,
            templateSelection: formatTimbanganSelection
        });

        $('.select2-line').select2({
            placeholder: "Pilih line tujuan...",
            allowClear: true,
            width: '100%',
            dropdownParent: $(this)
        });
    });

    // Handle modal hide event untuk destroy Select2
    $('#dynamicModal').on('hidden.bs.modal', function () {
        $('.select2-timbangan').select2('destroy');
        $('.select2-line').select2('destroy');
    });
});
</script>