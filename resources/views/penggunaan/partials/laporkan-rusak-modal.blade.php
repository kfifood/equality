{{--
    Partial: penggunaan/partials/laporkan-rusak-modal.blade.php
    Di-render via AJAX oleh LaporanKerusakanController@create
    Variabel yang tersedia: $penggunaan, $peralatan, $keluhanList
--}}

<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Laporkan Kerusakan Alat
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form id="formLaporkanRusak" action="{{ route('laporan-kerusakan.store') }}" method="POST">
    @csrf
    <input type="hidden" name="penggunaan_id" value="{{ $penggunaan->id }}">
    <input type="hidden" name="peralatan_id" value="{{ $peralatan->id }}">

    <div class="modal-body">

        {{-- ── Info Alat (read-only) ────────────────────────────────────── --}}
        <div class="alert alert-light border mb-4 p-3">
            <div class="row g-2">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer fs-4 text-primary"></i>
                        <div>
                            <div class="fw-bold text-primary" style="font-size:1.05rem;">
                                {{ $peralatan->kode_asset }}
                            </div>
                            <div class="text-muted small">{{ $peralatan->merk_tipe_lengkap }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted">Lokasi / Line</div>
                    <span class="badge bg-info fs-6">{{ $penggunaan->line_tujuan }}</span>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted">PIC Pelapor</div>
                    <div class="fw-semibold">{{ $penggunaan->pic ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- ── Tanggal Laporan ──────────────────────────────────────── --}}
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tanggal Laporan <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_laporan" id="inputTanggalLaporan"
                        class="form-control"
                        value="{{ date('Y-m-d') }}"
                        max="{{ date('Y-m-d') }}"
                        required>
                    <div class="invalid-feedback" id="errorTanggalLaporan"></div>
                </div>
            </div>

            {{-- ── Pilih Keluhan (multi) ────────────────────────────────── --}}
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Keluhan <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(pilih satu atau lebih)</small>
                    </label>
                    <select name="keluhan_ids[]" id="selectKeluhan" class="form-control" multiple="multiple" style="width: 100%" required>
                        @foreach($keluhanList as $keluhan)
                            <option value="{{ $keluhan->id }}">{{ $keluhan->nama_keluhan }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="errorKeluhan"></div>
                    @if($keluhanList->isEmpty())
                        <div class="form-text text-danger">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Belum ada keluhan. Tambahkan di menu
                            <a href="{{ route('master-keluhan.index') }}" target="_blank">Master Keluhan</a>.
                        </div>
                    @else
                        <div class="form-text">Klik untuk memilih, bisa lebih dari satu keluhan.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Keterangan Tambahan ──────────────────────────────────────── --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Keterangan Tambahan</label>
            <textarea name="keterangan_tambahan" class="form-control" rows="3"
                placeholder="Deskripsikan lebih detail kondisi kerusakan (opsional)..."
                maxlength="500" id="inputKeterangan"></textarea>
            <div class="form-text">
                <span id="charCount">0</span>/500 karakter
            </div>
        </div>

        {{-- ── Peringatan ───────────────────────────────────────────────── --}}
        <div class="alert alert-warning border-warning mb-0">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning mt-1 flex-shrink-0"></i>
                <div class="small">
                    <strong>Perhatian:</strong> Setelah laporan disimpan, kondisi alat akan berubah
                    menjadi <strong>Rusak</strong> dan alat tidak dapat digunakan sampai perbaikan selesai.
                    Proses perbaikan selanjutnya dilakukan di menu <strong>Perbaikan Alat</strong>.
                </div>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Batal
        </button>
        <button type="submit" class="btn btn-danger" id="btnSimpanLaporan"
            {{ $keluhanList->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-send me-1"></i>Kirim Laporan Kerusakan
        </button>
    </div>
</form>

<style>
/* Select2 Custom Styling */
.select2-container--default .select2-selection--multiple {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
    padding: 5px;
}

.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #4361EE;
    border: 1px solid #3a56d4;
    border-radius: 4px;
    color: white;
    padding: 3px 8px;
    margin: 2px;
    font-size: 13px;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-right: 5px;
    opacity: 0.8;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: white;
    opacity: 1;
}

.select2-dropdown {
    border-color: #ced4da;
    z-index: 1056 !important;
}

.select2-container {
    z-index: 1056 !important;
}
</style>

<script>
// Fungsi inisialisasi Select2 yang dijeda
function initSelect2InModal() {
    if ($('#selectKeluhan').length && !$('#selectKeluhan').hasClass('select2-hidden-accessible')) {
        setTimeout(function() {
            $('#selectKeluhan').select2({
                placeholder: "Pilih keluhan...",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#dynamicModal'),
                closeOnSelect: false,
                theme: 'default'
            });
            
            // Trigger change untuk styling
            $('#selectKeluhan').on('change', function() {
                console.log('Selected values:', $(this).val());
            });
        }, 100);
    }
}

// Jalankan inisialisasi saat DOM siap
$(document).ready(function() {
    initSelect2InModal();
});

// Jika modal menggunakan event show.bs.modal, inisialisasi ulang
$('#dynamicModal').on('shown.bs.modal', function() {
    initSelect2InModal();
});

// ── Counter karakter keterangan ────────────────────────────────────────
$('#inputKeterangan').on('input', function() {
    var length = $(this).val().length;
    $('#charCount').text(length);
    if (length > 500) {
        $(this).val($(this).val().substring(0, 500));
        $('#charCount').text(500);
    }
});

// ── Submit form laporan ────────────────────────────────────────────────
$('#formLaporkanRusak').on('submit', function(e) {
    e.preventDefault();

    // Validasi keluhan wajib dipilih
    const keluhanIds = $('#selectKeluhan').val();
    if (!keluhanIds || keluhanIds.length === 0) {
        $('#errorKeluhan').text('Pilih minimal satu keluhan.');
        $('#errorKeluhan').show();
        $('#selectKeluhan').next('.select2-container').find('.select2-selection').css('border-color', '#dc3545');
        return;
    }
    $('#errorKeluhan').hide();
    $('#selectKeluhan').next('.select2-container').find('.select2-selection').css('border-color', '#ced4da');

    // Validasi tanggal
    if (!$('#inputTanggalLaporan').val()) {
        $('#inputTanggalLaporan').addClass('is-invalid');
        $('#errorTanggalLaporan').text('Tanggal laporan wajib diisi.');
        return;
    } else {
        $('#inputTanggalLaporan').removeClass('is-invalid');
    }

    // Konfirmasi sebelum kirim
    Swal.fire({
        title: 'Kirim Laporan Kerusakan?',
        html: 'Kondisi alat <strong>{{ $peralatan->kode_asset }}</strong> akan berubah menjadi <strong class="text-danger">Rusak</strong>.<br>Lanjutkan?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-send me-1"></i>Ya, Kirim!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            kirimLaporan($(this));
        }
    });
});

function kirimLaporan(form) {
    const btn = $('#btnSimpanLaporan');
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Mengirim...');

    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Kirim Laporan Kerusakan');
            if (res.success) {
                $('#dynamicModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Laporan Terkirim!',
                    text: res.message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4361EE',
                }).then(() => location.reload());
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Kirim Laporan Kerusakan');
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengirim laporan.';
            
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;
                if (errors.keluhan_ids) {
                    $('#errorKeluhan').text(errors.keluhan_ids[0]);
                    $('#errorKeluhan').show();
                }
                if (errors.tanggal_laporan) {
                    $('#inputTanggalLaporan').addClass('is-invalid');
                    $('#errorTanggalLaporan').text(errors.tanggal_laporan[0]);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        }
    });
}

// Reset form saat modal ditutup
$('#dynamicModal').on('hidden.bs.modal', function() {
    $('#formLaporkanRusak')[0].reset();
    $('#selectKeluhan').val(null).trigger('change');
    $('#charCount').text('0');
    $('#inputTanggalLaporan').removeClass('is-invalid');
    $('#errorKeluhan').hide();
});
</script>