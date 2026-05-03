{{--
    Partial: perbaikan/partials/proses-modal.blade.php
    Di-render via AJAX oleh PerbaikanController@prosesModal
    Variabel: $laporan, $tindakanList, $lineList, $perbaikanTerakhir
--}}

<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-arrow-clockwise me-2"></i>Proses Perbaikan Alat
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="formProsesModal"
    action="{{ route('perbaikan.proses-store', $laporan->id) }}"
    method="POST">
    @csrf

    <div class="modal-body">

        {{-- ── Info Alat & Laporan (read-only) ─────────────────────────── --}}
        <div class="alert alert-light border mb-3 p-3">
            <div class="row g-2 align-items-center">
                <div class="col-sm-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer fs-3 text-primary"></i>
                        <div>
                            <div class="fw-bold text-primary" style="font-size:1rem;">
                                {{ $laporan->timbangan->kode_asset }}
                            </div>
                            <div class="text-muted small">{{ $laporan->timbangan->merk_tipe_no_seri }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="small text-muted">Line Asal</div>
                    <span class="badge bg-info">{{ $laporan->line_asal ?? '-' }}</span>
                </div>
                <div class="col-sm-2">
                    <div class="small text-muted">PIC Pelapor</div>
                    <div class="fw-semibold small">{{ $laporan->pic_pelapor ?? '-' }}</div>
                </div>
                <div class="col-sm-2">
                    <div class="small text-muted">Tgl Laporan</div>
                    <div class="small">{{ $laporan->tanggal_laporan->format('d/m/Y') }}</div>
                </div>
                <div class="col-sm-2">
                    <div class="small text-muted">Status Laporan</div>
                    <span class="badge bg-{{ $laporan->status_color }}">{{ $laporan->status }}</span>
                </div>
            </div>
            {{-- Keluhan --}}
            <div class="mt-2 pt-2 border-top">
                <span class="small text-muted me-2">Keluhan:</span>
                @foreach($laporan->keluhanList as $k)
                    <span class="badge bg-danger me-1">{{ $k->nama_keluhan }}</span>
                @endforeach
                @if($laporan->keterangan_tambahan)
                    <div class="small text-muted mt-1">
                        <i class="bi bi-chat-text me-1"></i>{{ $laporan->keterangan_tambahan }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Histori Tindakan yang sudah dicatat ─────────────────────── --}}
        @if($perbaikanTerakhir && $perbaikanTerakhir->detailTindakan->count() > 0)
        <div class="mb-3">
            <label class="form-label fw-semibold text-muted small">
                <i class="bi bi-clock-history me-1"></i>Tindakan yang Sudah Dicatat
            </label>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tindakan</th>
                            <th width="110">Tanggal</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perbaikanTerakhir->detailTindakan as $dt)
                        <tr>
                            <td>
                                <span class="badge bg-primary me-1">
                                    <i class="bi bi-tools me-1"></i>{{ $dt->masterTindakan->nama_tindakan ?? '-' }}
                                </span>
                            </td>
                            <td class="small">{{ $dt->tanggal_formatted }}</td>
                            <td class="small text-muted">{{ $dt->catatan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <hr class="my-3">

        {{-- ── Form Input Proses Baru ───────────────────────────────────── --}}
        <div class="row">
            {{-- Status Perbaikan --}}
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Status Perbaikan <span class="text-danger">*</span>
                    </label>
                    <select name="status_perbaikan" id="selectStatusPerbaikan" class="form-select" required>
                        <option value="Menunggu Penanganan"
                            {{ ($perbaikanTerakhir?->status_perbaikan ?? '') == 'Menunggu Penanganan' ? 'selected' : '' }}>
                            ⏳ Menunggu Penanganan
                        </option>
                        <option value="Masuk Lab"
                            {{ ($perbaikanTerakhir?->status_perbaikan ?? '') == 'Masuk Lab' ? 'selected' : '' }}>
                            📥 Masuk Lab
                        </option>
                        <option value="Perbaikan Internal"
                            {{ ($perbaikanTerakhir?->status_perbaikan ?? '') == 'Perbaikan Internal' ? 'selected' : '' }}>
                            🔧 Perbaikan Internal (Teknik)
                        </option>
                        <option value="Dikirim Eksternal"
                            {{ ($perbaikanTerakhir?->status_perbaikan ?? '') == 'Dikirim Eksternal' ? 'selected' : '' }}>
                            📦 Dikirim Eksternal (Vendor)
                        </option>
                        <option value="Selesai"
                            {{ ($perbaikanTerakhir?->status_perbaikan ?? '') == 'Selesai' ? 'selected' : '' }}>
                            ✅ Selesai
                        </option>
                    </select>
                </div>
            </div>

            {{-- Tanggal Masuk Lab --}}
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tanggal Masuk Lab <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_masuk_lab" class="form-control"
                        value="{{ $perbaikanTerakhir?->tanggal_masuk_lab?->format('Y-m-d') ?? date('Y-m-d') }}"
                        required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            {{-- Tanggal Tindakan --}}
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Tindakan</label>
                    <input type="date" name="tanggal_tindakan" class="form-control"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        {{-- Pilih Tindakan (multi-select) --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">
                Tindakan yang Dilakukan
                <small class="text-muted fw-normal">(bisa pilih lebih dari satu)</small>
            </label>
            <select name="tindakan_ids[]" id="selectTindakan"
                class="form-select select2-tindakan" multiple>
                @foreach($tindakanList as $t)
                    <option value="{{ $t->id }}">{{ $t->nama_tindakan }}</option>
                @endforeach
            </select>
            @if($tindakanList->isEmpty())
                <div class="form-text text-danger">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Belum ada tindakan. Tambahkan di
                    <a href="{{ route('master-tindakan.index') }}" target="_blank">Master Tindakan</a>.
                </div>
            @else
                <div class="form-text">Pilih tindakan yang dilakukan pada sesi ini.</div>
            @endif
        </div>

        {{-- Catatan Tindakan --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Tindakan</label>
            <input type="text" name="catatan_tindakan" class="form-control"
                placeholder="Catatan singkat untuk tindakan di atas (opsional)"
                maxlength="500">
        </div>

        {{-- Catatan Proses Umum --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Proses Perbaikan</label>
            <textarea name="catatan" class="form-control" rows="2"
                placeholder="Catatan umum proses perbaikan (opsional)"
                maxlength="500">{{ $perbaikanTerakhir?->catatan }}</textarea>
        </div>

        {{-- ── Field Selesai (muncul hanya jika status = Selesai) ─────── --}}
        <div id="selesaiFields" style="display:none;">
            <hr class="my-3">
            <div class="alert alert-success border-success py-2 mb-3">
                <small>
                    <i class="bi bi-check-circle me-1"></i>
                    <strong>Status Selesai:</strong> Timbangan akan dikembalikan ke lokasi tujuan
                    dan kondisi berubah kembali menjadi <strong>Baik</strong>.
                </small>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tanggal Selesai <span class="text-danger" id="reqTanggalSelesai">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai_perbaikan" id="inputTanggalSelesai"
                            class="form-control" value="{{ date('Y-m-d') }}">
                        <div class="invalid-feedback" id="errorTanggalSelesai"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Lokasi Tujuan <span class="text-danger" id="reqLineTujuan">*</span>
                        </label>
                        <select name="line_tujuan" id="selectLineTujuan" class="form-select">
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            <option value="Lab">🏭 Lab (Simpan di Lab)</option>
                            @foreach($lineList as $line)
                                <option value="{{ $line->nama_line }}">
                                    {{ $line->nama_line }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="errorLineTujuan"></div>
                        <div class="form-text">
                            Pilih <strong>Lab</strong> jika disimpan di lab, atau pilih line tujuan langsung.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end modal-body --}}

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Tutup
        </button>
        <button type="submit" class="btn btn-primary" id="btnSimpanProses">
            <i class="bi bi-save me-1"></i>Simpan Update Perbaikan
        </button>
    </div>
</form>

<style>
/* Select2 multiple dalam modal */
.select2-container--default .select2-selection--multiple {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 40px;
    padding: 3px 6px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #4361EE;
    border-color: #3a56d4;
    color: white;
    border-radius: 4px;
    padding: 2px 8px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: rgba(255,255,255,0.75);
    margin-right: 4px;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #4361EE;
}
</style>

<script>
$(document).ready(function () {

    // ── Init Select2 tindakan ──────────────────────────────────────────────
    $('#selectTindakan').select2({
        placeholder: 'Pilih tindakan yang dilakukan...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#dynamicModal'),
        closeOnSelect: false,
    });

    // ── Toggle field Selesai ───────────────────────────────────────────────
    function toggleSelesaiFields(status) {
        const isSelesai = (status === 'Selesai');
        $('#selesaiFields').toggle(isSelesai);
        $('#inputTanggalSelesai').prop('required', isSelesai);
        $('#selectLineTujuan').prop('required', isSelesai);
    }

    // Init state berdasarkan nilai awal
    toggleSelesaiFields($('#selectStatusPerbaikan').val());

    $('#selectStatusPerbaikan').on('change', function () {
        toggleSelesaiFields($(this).val());
    });

    // ── Submit form proses ─────────────────────────────────────────────────
    $('#formProsesModal').on('submit', function (e) {
        e.preventDefault();
        const form   = $(this);
        const status = $('#selectStatusPerbaikan').val();

        // Validasi manual untuk field Selesai
        if (status === 'Selesai') {
            let valid = true;

            if (!$('#inputTanggalSelesai').val()) {
                $('#inputTanggalSelesai').addClass('is-invalid');
                $('#errorTanggalSelesai').text('Tanggal selesai harus diisi.');
                valid = false;
            } else {
                $('#inputTanggalSelesai').removeClass('is-invalid');
            }

            if (!$('#selectLineTujuan').val()) {
                $('#selectLineTujuan').addClass('is-invalid');
                $('#errorLineTujuan').text('Lokasi tujuan harus dipilih.');
                valid = false;
            } else {
                $('#selectLineTujuan').removeClass('is-invalid');
            }

            if (!valid) return;
        }

        // Konfirmasi jika status Selesai
        if (status === 'Selesai') {
            const lineTujuan = $('#selectLineTujuan option:selected').text().trim();
            Swal.fire({
                title: 'Tandai Perbaikan Selesai?',
                html: 'Kondisi alat akan kembali menjadi <strong>Baik</strong> dan dikirim ke <strong>' + lineTujuan + '</strong>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4361EE',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Ya, Selesai!',
                cancelButtonText: 'Batal'
            }).then(r => { if (r.isConfirmed) submitProsesForm(form); });
        } else {
            submitProsesForm(form);
        }
    });

    function submitProsesForm(form) {
        const btn = $('#btnSimpanProses');
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

        $.ajax({
            url:  form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function (res) {
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Update Perbaikan');
                if (res.success) {
                    $('#dynamicModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        confirmButtonColor: '#4361EE',
                    }).then(() => location.reload());
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Update Perbaikan');
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    // Tampilkan error validasi server
                    const errors = xhr.responseJSON.errors;
                    if (errors.line_tujuan) {
                        $('#selectLineTujuan').addClass('is-invalid');
                        $('#errorLineTujuan').text(errors.line_tujuan[0]);
                    }
                    if (errors.tanggal_selesai_perbaikan) {
                        $('#inputTanggalSelesai').addClass('is-invalid');
                        $('#errorTanggalSelesai').text(errors.tanggal_selesai_perbaikan[0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.',
                    });
                }
            }
        });
    }
});
</script>