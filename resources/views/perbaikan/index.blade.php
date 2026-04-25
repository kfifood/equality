@extends('layouts.app')
@section('title', 'Perbaikan Alat')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tools me-2"></i>Perbaikan Alat
                    </h5>
                    <button class="btn btn-sm" style="background-color:#4361EE; color:white;" onclick="showCreatePerbaikanModal()">
                        <i class="bi bi-plus-circle me-1"></i>Catat Perbaikan
                    </button>
                </div>
                <div class="card-body">
                    <!-- Session Messages dengan SweetAlert -->
                    @if(session('success'))
                        <div class="d-none" id="session-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="d-none" id="session-error">{{ session('error') }}</div>
                    @endif

                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('perbaikan.index') }}" method="GET" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Status Perbaikan</label>
                                        <select name="status" class="form-select">
                                            <option value="">Semua Status</option>
                                            <option value="Masuk Lab"          {{ request('status') == 'Masuk Lab'          ? 'selected' : '' }}>Masuk Lab</option>
                                            <option value="Perbaikan Internal" {{ request('status') == 'Perbaikan Internal' ? 'selected' : '' }}>Perbaikan Internal (Teknik)</option>
                                            <option value="Dikirim Eksternal"  {{ request('status') == 'Dikirim Eksternal'  ? 'selected' : '' }}>Dikirim Eksternal</option>
                                            <option value="Selesai"            {{ request('status') == 'Selesai'            ? 'selected' : '' }}>Selesai</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Tanggal Dari</label>
                                        <input type="date" name="tanggal_dari" class="form-control"
                                               value="{{ request('tanggal_dari') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Tanggal Sampai</label>
                                        <input type="date" name="tanggal_sampai" class="form-control"
                                               value="{{ request('tanggal_sampai') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Kode Asset</label>
                                        <input type="text" name="kode_asset" class="form-control"
                                               value="{{ request('kode_asset') }}" placeholder="Cari kode asset...">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" title="Filter">
                                                <i class="bi bi-funnel"></i>
                                            </button>
                                            <a href="{{ route('perbaikan.index') }}"
                                               class="btn btn-secondary d-flex align-items-center justify-content-center"
                                               title="Reset">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="perbaikanTable">
                            <thead class="table-tmb" style="color:#4361EE;">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th>Merk &amp; Tipe</th>
                                    <th>Line Sebelumnya</th>
                                    <th>Keluhan</th>
                                    <th>Status Perbaikan</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Tanggal Rilis</th>
                                    <th>Durasi</th>
                                    <th width="100" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perbaikan as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $perbaikan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-tools text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $item->timbangan->kode_asset }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px;"
                                              title="{{ $item->timbangan->merk_tipe_no_seri }}">
                                            {{ $item->timbangan->merk_tipe_no_seri }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $item->line_sebelumnya }}</span>
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px;"
                                              title="{{ $item->deskripsi_keluhan }}">
                                            {{ $item->deskripsi_keluhan }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $badgeColor = match($item->status_perbaikan) {
                                                'Masuk Lab'          => 'secondary',
                                                'Perbaikan Internal' => 'warning',
                                                'Dikirim Eksternal'  => 'info',
                                                'Selesai'            => 'success',
                                                default              => 'secondary'
                                            };
                                            $statusIcon = match($item->status_perbaikan) {
                                                'Masuk Lab'          => 'box-arrow-in-down',
                                                'Perbaikan Internal' => 'tools',
                                                'Dikirim Eksternal'  => 'arrow-right-circle',
                                                'Selesai'            => 'check-circle',
                                                default              => 'question-circle'
                                            };
                                            // Label tampilan untuk Perbaikan Internal
                                            $statusLabel = $item->status_perbaikan === 'Perbaikan Internal'
                                                ? 'Perbaikan Internal (Teknik)'
                                                : $item->status_perbaikan;
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar me-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_masuk_lab)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @if($item->tanggal_selesai_perbaikan)
                                            <i class="bi bi-calendar-check me-1 text-success"></i>
                                            {{ \Carbon\Carbon::parse($item->tanggal_selesai_perbaikan)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->durasi_perbaikan !== null)
                                            <span class="badge bg-light text-dark">
                                                {{ $item->durasi_perbaikan }} hari
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->status_perbaikan !== 'Selesai')
                                            <button class="btn btn-sm btn-info" title="Update Status"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStatusModal"
                                                    data-id="{{ $item->id }}"
                                                    data-status="{{ $item->status_perbaikan }}"
                                                    data-tindakan="{{ $item->tindakan_perbaikan }}"
                                                    data-eksternal="{{ $item->perbaikan_eksternal }}">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Selesai
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $perbaikan->firstItem() }} hingga {{ $perbaikan->lastItem() }}
                            dari {{ $perbaikan->total() }} perbaikan
                        </div>
                        <nav>
                            {{ $perbaikan->appends(request()->query())->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     UPDATE STATUS MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateStatusForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background-color:white; color:#4361EE;">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-clockwise me-2"></i>Update Status Perbaikan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- ── Status ── --}}
                    <div class="mb-3">
                        <label class="form-label">Status Perbaikan <span class="text-danger">*</span></label>
                        <select name="status_perbaikan" class="form-select" id="status_perbaikan" required>
                            <option value="Masuk Lab">Masuk Lab</option>
                            <option value="Perbaikan Internal">Perbaikan Internal (oleh Teknik)</option>
                            <option value="Dikirim Eksternal">Dikirim Eksternal (Vendor Luar)</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>

                    {{-- ── Tindakan Perbaikan ── --}}
                    <div class="mb-3">
                        <label class="form-label">Tindakan Perbaikan</label>
                        <textarea name="tindakan_perbaikan" id="tindakan_perbaikan" class="form-control" rows="3"
                                  placeholder="Deskripsi perbaikan yang dilakukan"></textarea>
                    </div>

                    {{-- ── Catatan Eksternal (muncul hanya saat Dikirim Eksternal) ── --}}
                    <div class="mb-3" id="catatanEksternalWrap" style="display:none;">
                        <label class="form-label">Catatan Perbaikan Eksternal</label>
                        <textarea name="perbaikan_eksternal" id="perbaikan_eksternal" class="form-control" rows="2"
                                  placeholder="Nama vendor / keterangan pengiriman eksternal"></textarea>
                    </div>

                    {{-- ── Field Selesai (muncul hanya saat Selesai) ── --}}
                    <div id="selesaiFields" style="display:none;">
                        <hr class="my-3">
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Pilih lokasi tujuan timbangan setelah selesai diperbaiki.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_selesai_perbaikan" id="tanggal_selesai_perbaikan"
                                           class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi Tujuan <span class="text-danger">*</span></label>
                                    <select name="line_tujuan" id="line_tujuan" class="form-select" required>
                                        <option value="">-- Pilih Lokasi --</option>
                                        {{-- Opsi "Lab" = timbangan dikembalikan ke Lab --}}
                                        <option value="Lab">Lab (Simpan di Lab)</option>
                                        @php
                                            $lines = \App\Models\MasterLine::where('status_aktif', true)
                                                        ->orderBy('nama_line')->get();
                                        @endphp
                                        @foreach($lines as $line)
                                            <option value="{{ $line->nama_line }}">{{ $line->nama_line }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        Pilih <strong>Lab</strong> jika timbangan disimpan di lab dulu,
                                        atau pilih Line tujuan langsung.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dynamic Modal Container -->
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-labelledby="dynamicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="dynamicModalContent">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 0.9rem;
}
.card {
    border: none;
    border-radius: 12px;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
}
.badge {
    font-size: 0.75em;
}
</style>

<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {

    // ── Session messages ──────────────────────────────────────
    const successMsg = $('#session-success').text();
    const errorMsg   = $('#session-error').text();
    if (successMsg) Swal.fire({ icon: 'success', title: 'Berhasil', text: successMsg, timer: 3000, showConfirmButton: false });
    if (errorMsg)   Swal.fire({ icon: 'error',   title: 'Error',    text: errorMsg,   timer: 4000 });

    // ── Auto-search with debounce ─────────────────────────────
    let searchTimer;
    $('input[name="kode_asset"]').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => $('#filterForm').submit(), 800);
    });

    // ── Tooltips ──────────────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // ── Update Status Modal: populate fields on open ──────────
    $('#updateStatusModal').on('show.bs.modal', function (event) {
        const btn = $(event.relatedTarget);

        // Set form action
        $('#updateStatusForm').attr('action', '{{ url("perbaikan") }}/' + btn.data('id') + '/status');

        // Pre-fill fields
        const status = btn.data('status');
        $('#status_perbaikan').val(status);
        $('#tindakan_perbaikan').val(btn.data('tindakan') || '');
        $('#perbaikan_eksternal').val(btn.data('eksternal') || '');

        // Reset selesai fields
        $('#line_tujuan').val('').prop('required', false);
        $('#tanggal_selesai_perbaikan').prop('required', false);

        toggleStatusFields(status);
    });

    // ── Toggle fields on status change ───────────────────────
    $('#status_perbaikan').on('change', function () {
        toggleStatusFields($(this).val());
    });

    function toggleStatusFields(status) {
        const isSelesai   = status === 'Selesai';
        const isEksternal = status === 'Dikirim Eksternal';

        // Selesai fields
        if (isSelesai) {
            $('#selesaiFields').show();
            $('#line_tujuan').prop('required', true);
            $('#tanggal_selesai_perbaikan').prop('required', true);
        } else {
            $('#selesaiFields').hide();
            $('#line_tujuan').val('').prop('required', false);
            $('#tanggal_selesai_perbaikan').prop('required', false);
        }

        // Catatan Eksternal
        $('#catatanEksternalWrap').toggle(isEksternal);
    }

    // ── Update Status Form Submit ─────────────────────────────
    $(document).on('submit', '#updateStatusForm', function (e) {
        e.preventDefault();

        const form        = $(this);
        const submitBtn   = form.find('button[type="submit"]');
        const originalTxt = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

        $.ajax({
            url:     form.attr('action'),
            type:    'POST',
            data:    form.serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalTxt);
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil', text: response.message,
                        timer: 2000, showConfirmButton: false
                    }).then(() => { $('#updateStatusModal').modal('hide'); location.reload(); });
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html(originalTxt);
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Terjadi kesalahan saat mengupdate status.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });

    // ── Create Perbaikan Form Submit ──────────────────────────
    $(document).on('submit', '#createPerbaikanForm', function (e) {
        e.preventDefault();

        const form        = $(this);
        const submitBtn   = form.find('button[type="submit"]');
        const originalTxt = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

        $.ajax({
            url:  form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalTxt);
                if (response.success) {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil', text: response.message,
                        timer: 2000, showConfirmButton: false
                    }).then(() => { $('#dynamicModal').modal('hide'); location.reload(); });
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html(originalTxt);
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    clearValidationErrors();
                    for (const field in xhr.responseJSON.errors) {
                        const input = $('[name="' + field + '"]');
                        input.addClass('is-invalid')
                             .after('<div class="invalid-feedback">' + xhr.responseJSON.errors[field][0] + '</div>');
                    }
                    $('.is-invalid').first().focus();
                } else {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Terjadi kesalahan saat menyimpan data perbaikan.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            }
        });
    });

    $('#dynamicModal').on('hidden.bs.modal', function () {
        $('#dynamicModalContent').html('');
    });
});

// ── Show Create Perbaikan Modal (global function) ─────────────
function showCreatePerbaikanModal(timbanganId = null) {
    let url = '{{ route("perbaikan.create") }}';
    if (timbanganId) url = '{{ url("perbaikan/create") }}/' + timbanganId;

    Swal.fire({ title: 'Memuat form...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: url, type: 'GET',
        success: function (response) {
            Swal.close();
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
                initFormValidation();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form perbaikan' });
            }
        },
        error: function () {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form perbaikan' });
        }
    });
}

function initFormValidation()    { clearValidationErrors(); }
function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}
</script>
@endsection