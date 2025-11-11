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
                                            <option value="Masuk Lab" {{ request('status') == 'Masuk Lab' ? 'selected' : '' }}>Masuk Lab</option>
                                            <option value="Dalam Perbaikan" {{ request('status') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="Dikirim Eksternal" {{ request('status') == 'Dikirim Eksternal' ? 'selected' : '' }}>Dikirim Eksternal</option>
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
        <th>Merk & Tipe</th>
        <th>Line Sebelumnya</th>
        <th>Keluhan</th>
        <th>Status Perbaikan</th>
        <th>Tanggal Masuk</th>
        <th>Tanggal Rilis</th> <!-- TAMBAHAN: Tanggal Rilis -->
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
                                    <!-- Hapus baris untuk nomor_seri_unik -->
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
                                                'Masuk Lab' => 'secondary',
                                                'Dalam Perbaikan' => 'warning',
                                                'Selesai' => 'success',
                                                'Dikirim Eksternal' => 'info',
                                                default => 'secondary'
                                            };
                                            
                                            $statusIcon = match($item->status_perbaikan) {
                                                'Masuk Lab' => 'box-arrow-in-down',
                                                'Dalam Perbaikan' => 'tools',
                                                'Selesai' => 'check-circle',
                                                'Dikirim Eksternal' => 'arrow-right-circle',
                                                default => 'question-circle'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $item->status_perbaikan }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar me-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_masuk_lab)->format('d/m/Y') }}
                                    </td>
                                    <td>
        <!-- TAMBAHAN: Tanggal Rilis -->
        @if($item->tanggal_selesai_perbaikan)
            <i class="bi bi-calendar-check me-1 text-success"></i>
            {{ \Carbon\Carbon::parse($item->tanggal_selesai_perbaikan)->format('d/m/Y') }}
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
                                    <td>
                                        @if($item->durasi_perbaikan)
                                            <span class="badge bg-light text-dark">
                                                {{ $item->durasi_perbaikan }} hari
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <!-- Di bagian tabel perbaikan, kolom aksi -->
<td class="text-center">
    @if($item->status_perbaikan !== 'Selesai')
        <button class="btn btn-sm btn-info" title="Update Status" 
                data-bs-toggle="modal" 
                data-bs-target="#updateStatusModal"
                data-id="{{ $item->id }}"
                data-status="{{ $item->status_perbaikan }}">
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

<!-- Update Status Modal -->
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
                    <div class="mb-3">
                        <label class="form-label">Status Perbaikan <span class="text-danger">*</span></label>
                        <select name="status_perbaikan" class="form-select" id="status_perbaikan" required>
                            <option value="Masuk Lab">Masuk Lab</option>
                            <option value="Dalam Perbaikan">Dalam Perbaikan</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Dikirim Eksternal">Dikirim Eksternal</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tindakan Perbaikan</label>
                        <textarea name="tindakan_perbaikan" class="form-control" rows="3" 
                                  placeholder="Deskripsi perbaikan yang dilakukan"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perbaikan Eksternal</label>
                        <textarea name="perbaikan_eksternal" class="form-control" rows="2" 
                                  placeholder="Jika ada perbaikan eksternal"></textarea>
                    </div>
                    <div class="mb-3" id="selesaiFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai_perbaikan" class="form-control" 
                                       value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Line Tujuan</label>
                                <select name="line_tujuan" class="form-select">
                                    <option value="">Pilih Line (Opsional)</option>
                                    @php
                                        $lines = \App\Models\MasterLine::where('status_aktif', true)->get();
                                    @endphp
                                    @foreach($lines as $line)
                                        <option value="{{ $line->nama_line }}">
                                            {{ $line->nama_line }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Kosongkan jika tetap di Lab</div>
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

<!-- SweetAlert2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show session messages dengan SweetAlert
    const successMessage = $('#session-success').text();
    const errorMessage = $('#session-error').text();
    
    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: successMessage,
            timer: 3000,
            showConfirmButton: false
        });
    }
    
    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
            timer: 4000
        });
    }

    // Auto-submit search with delay
    let searchTimer;
    $('input[name="kode_asset"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 800);
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Update Status Modal Handler
    $('#updateStatusModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var status = button.data('status');
        
        $('#updateStatusForm').attr('action', '{{ url("perbaikan") }}/' + id + '/status');
        $('#status_perbaikan').val(status);
        
        // Toggle fields untuk status Selesai
        toggleSelesaiFields(status);
    });

    $('#status_perbaikan').change(function() {
        toggleSelesaiFields($(this).val());
    });

    function toggleSelesaiFields(status) {
        if (status === 'Selesai') {
            $('#selesaiFields').show();
        } else {
            $('#selesaiFields').hide();
        }
    }
});

// ==================== MODAL FUNCTIONS ====================

// Show Create Perbaikan Modal
function showCreatePerbaikanModal(timbanganId = null) {
    let url = '{{ route("perbaikan.create") }}';
    if (timbanganId) {
        url = '{{ url("perbaikan/create") }}/' + timbanganId;
    }

    // Show loading
    Swal.fire({
        title: 'Memuat form...',
        text: 'Sedang memuat form perbaikan',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
                initFormValidation();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat form perbaikan'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            console.error('Error:', xhr);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat form perbaikan'
            });
        }
    });
}

// Handle Create Perbaikan Form Submission
$(document).on('submit', '#createPerbaikanForm', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const formData = form.serialize();
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#dynamicModal').modal('hide');
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (xhr.status === 422) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                clearValidationErrors();
                
                for (const field in errors) {
                    const input = $('[name="' + field + '"]');
                    const errorMsg = errors[field][0];
                    
                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback">' + errorMsg + '</div>');
                }
                
                // Scroll to first error
                $('.is-invalid').first().focus();
                
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                // Custom error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message
                });
            } else {
                // Generic error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menyimpan data perbaikan'
                });
            }
        }
    });
});

// Handle Update Status Form Submission
$(document).on('submit', '#updateStatusForm', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const formData = form.serialize();
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#updateStatusModal').modal('hide');
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = '';
                
                for (const field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Error',
                    text: errorMessage
                });
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                // Custom error message (misal: sudah selesai)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat mengupdate status'
                });
            }
        }
    });
});

// ==================== HELPER FUNCTIONS ====================

// Initialize form validation
function initFormValidation() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

// Clear validation errors
function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

// Close modal handler
$('#dynamicModal').on('hidden.bs.modal', function () {
    $('#dynamicModalContent').html('');
});
</script>
@endsection