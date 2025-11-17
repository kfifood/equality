<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-tools me-2"></i>Catat Perbaikan Alat
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="createPerbaikanForm" action="{{ route('perbaikan.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Pilih Timbangan <span class="text-danger">*</span></label>
                    <select name="timbangan_id" class="form-select select2-timbangan" id="timbanganSelect" required>
    <option value="">Pilih Timbangan</option>
    @foreach($timbangan as $item)
        @php
            // Coba ambil PIC dari penggunaan terakhir jika ada
            $penggunaanTerakhir = $item->riwayatPenggunaan->sortByDesc('tanggal_pemakaian')->first();
            $pic = $penggunaanTerakhir ? $penggunaanTerakhir->pic : '-';
        @endphp
        <option value="{{ $item->id }}" 
            {{ $selectedTimbangan && $selectedTimbangan->id == $item->id ? 'selected' : '' }}
            data-kode="{{ $item->kode_asset }}"
            data-merk="{{ $item->merk_tipe_no_seri }}"
            data-kondisi="{{ $item->kondisi_saat_ini }}"
            data-lokasi="{{ $item->status_line ? $item->status_line : 'Lab' }}"
            data-pic="{{ $pic }}">
            {{ $item->kode_asset }} - {{ $item->merk_tipe_no_seri }} 
            ({{ $item->status_line ? $item->status_line : 'Lab' }}) - {{ $item->kondisi_saat_ini }}
        </option>
    @endforeach
</select>
                    <div class="form-text" id="timbanganInfo">
                        Pilih timbangan untuk melihat informasi lengkap
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Line Sebelumnya <span class="text-danger">*</span></label>
                    <input type="text" name="line_sebelumnya" class="form-control" id="lineSebelumnya" 
                           placeholder="Line sebelumnya akan terisi otomatis" readonly required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tanggal Masuk Lab <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_masuk_lab" class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Penggunaan Terakhir</label>
                    <input type="text" name="penggunaan_terakhir" class="form-control" id="penggunaanTerakhir"
                           placeholder="PIC akan terisi otomatis" readonly>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi Keluhan <span class="text-danger">*</span></label>
            <textarea name="deskripsi_keluhan" class="form-control" rows="4" required
                      placeholder="Jelaskan keluhan/kerusakan yang dialami (fluktuasi, tidak akurat, dll)"></textarea>
        </div>

        <!-- Alert untuk info auto-fill -->
        <div class="alert alert-info">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Informasi Auto-Fill:</strong><br>
                • <strong>Line Sebelumnya</strong> dan <strong>Penggunaan Terakhir</strong> akan terisi otomatis saat memilih timbangan<br>
                • Data diambil dari lokasi dan PIC penggunaan terakhir timbangan
            </small>
        </div>

        <div class="alert alert-warning">
            <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Timbangan akan dikembalikan ke <strong>Lab</strong> dan status berubah menjadi <strong>Dalam Perbaikan</strong>.
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">
            <i class="bi bi-save me-1"></i>Simpan Perbaikan
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

.form-control[readonly] {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

/* Loading spinner */
.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #4361EE;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 untuk timbangan dengan search
    $('.select2-timbangan').select2({
        placeholder: "Cari timbangan berdasarkan kode asset atau merk...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#dynamicModal'),
        templateResult: formatTimbangan,
        templateSelection: formatTimbanganSelection
    });

    // Format tampilan hasil pencarian di dropdown
    function formatTimbangan(timbangan) {
        if (!timbangan.id) {
            return timbangan.text;
        }

        var kode = timbangan.element.getAttribute('data-kode');
        var merk = timbangan.element.getAttribute('data-merk');
        var kondisi = timbangan.element.getAttribute('data-kondisi');
        var lokasi = timbangan.element.getAttribute('data-lokasi');

        var $timbangan = $(
            '<div class="d-flex justify-content-between align-items-center p-1">' +
                '<div class="flex-grow-1">' +
                    '<div class="fw-bold">' + kode + '</div>' +
                    '<small class="text-muted">' + merk + '</small>' +
                '</div>' +
                '<div class="text-end ms-2">' +
                    '<span class="badge bg-' + getKondisiColor(kondisi) + ' mb-1">' + kondisi + '</span><br>' +
                    '<small class="text-muted">' + lokasi + '</small>' +
                '</div>' +
            '</div>'
        );
        return $timbangan;
    }

    // PERBAIKAN: Format tampilan saat dipilih - TAMPILKAN LENGKAP
    function formatTimbanganSelection(timbangan) {
        if (!timbangan.id) {
            return timbangan.text;
        }
        
        var kode = timbangan.element.getAttribute('data-kode');
        var merk = timbangan.element.getAttribute('data-merk');
        var lokasi = timbangan.element.getAttribute('data-lokasi');
        
        // Tampilkan lengkap: kode + merk + lokasi
        return kode + ' - ' + merk + ' (' + lokasi + ')';
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

    // Update info dan auto-fill saat timbangan dipilih - TANPA AJAX
    $('#timbanganSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const timbanganId = selectedOption.val();
        
        if (timbanganId) {
            const lokasi = selectedOption.data('lokasi');
            const kondisi = selectedOption.data('kondisi');
            const kode = selectedOption.data('kode');
            const merk = selectedOption.data('merk');
            
            console.log('Data selected:', { lokasi, kondisi, kode, merk }); // Debug
            
            // Auto-fill line sebelumnya dari data lokasi
            $('#lineSebelumnya').val(lokasi);
            
            // PERBAIKAN: Untuk PIC, kita coba ambil dari data attribute jika ada
            // Jika tidak ada, beri petunjuk untuk diisi manual
            const picData = selectedOption.data('pic') || '-';
            $('#penggunaanTerakhir').val(picData);
            
            // Jika PIC masih "-", beri placeholder yang informatif
            if (picData === '-') {
                $('#penggunaanTerakhir').attr('placeholder', 'Isikan nama PIC yang terakhir menggunakan');
                $('#penggunaanTerakhir').prop('readonly', false); // Bisa diedit
                $('#penggunaanTerakhir').removeClass('bg-light').addClass('bg-warning bg-opacity-10');
            } else {
                $('#penggunaanTerakhir').attr('placeholder', '');
                $('#penggunaanTerakhir').prop('readonly', true);
                $('#penggunaanTerakhir').removeClass('bg-warning bg-opacity-10').addClass('bg-light');
            }
            
            // Update info timbangan
            $('#timbanganInfo').html(
                '<small>Kode: <strong>' + kode + '</strong> | ' +
                'Merk: <strong>' + merk + '</strong> | ' +
                'Kondisi: <strong>' + kondisi + '</strong> | ' +
                'Lokasi: <strong>' + lokasi + '</strong></small>'
            );
            
        } else {
            resetForm();
        }
    });

    // Function untuk reset form
    function resetForm() {
        $('#timbanganInfo').html('Pilih timbangan untuk melihat informasi lengkap');
        $('#lineSebelumnya').val('');
        $('#penggunaanTerakhir').val('');
        $('#penggunaanTerakhir').attr('placeholder', 'PIC akan terisi otomatis');
        $('#penggunaanTerakhir').prop('readonly', true);
        $('#penggunaanTerakhir').removeClass('bg-warning bg-opacity-10').addClass('bg-light');
    }

    // Trigger change event saat modal terbuka
    setTimeout(() => {
        $('#timbanganSelect').trigger('change');
    }, 500);

    // Handle modal events
    $('#dynamicModal').on('shown.bs.modal', function () {
        $('.select2-timbangan').select2({
            placeholder: "Cari timbangan berdasarkan kode asset atau merk...",
            allowClear: true,
            width: '100%',
            dropdownParent: $(this),
            templateResult: formatTimbangan,
            templateSelection: formatTimbanganSelection
        });
    });

    $('#dynamicModal').on('hidden.bs.modal', function () {
        $('.select2-timbangan').select2('destroy');
        resetForm();
    });
});
</script>