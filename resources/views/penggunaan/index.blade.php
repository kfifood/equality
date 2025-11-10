@extends('layouts.app')
@section('title', 'Penggunaan Alat')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-arrow-right-circle me-2"></i>Riwayat Penggunaan Alat
                        </h5>
                        <small class="text-muted">Termasuk timbangan yang baru selesai perbaikan</small>
                    </div>
                    <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                        onclick="showCreatePenggunaanModal()">
                        <i class="bi bi-plus-circle me-1"></i>Catat Penggunaan
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
                            <form action="{{ route('penggunaan.index') }}" method="GET" id="filterForm">
                                <div class="row g-3">
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
                                        <label class="form-label">Line Tujuan</label>
                                        <select name="line_tujuan" class="form-select">
                                            <option value="">Semua Line</option>
                                            @foreach($lineList as $line)
                                            <option value="{{ $line->nama_line }}"
                                                {{ request('line_tujuan') == $line->nama_line ? 'selected' : '' }}>
                                                {{ $line->nama_line }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Kode Asset</label>
                                        <input type="text" name="kode_asset" class="form-control"
                                            value="{{ request('kode_asset') }}" placeholder="Cari kode asset...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Kondisi</label>
                                        <select name="kondisi" class="form-select">
                                            <option value="">Semua Kondisi</option>
                                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>
                                                Baik</option>
                                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>
                                                Rusak</option>
                                            <option value="Dalam Perbaikan"
                                                {{ request('kondisi') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam
                                                Perbaikan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit"
                                                class="btn btn-primary d-flex align-items-center justify-content-center"
                                                title="Filter">
                                                <i class="bi bi-funnel"></i>
                                            </button>
                                            <a href="{{ route('penggunaan.index') }}"
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
                        <table class="table table-bordered table-hover table-striped" id="penggunaanTable">
                            <thead class="table-tmb" style="color:#4361EE;">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th>Merk & Tipe</th>
                                    <th>Line Tujuan</th>
                                    <th>PIC</th>
                                    <th>Tanggal Pemakaian</th>
                                    <th>Status</th>
                                    <th>Kondisi Alat</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penggunaan as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $penggunaan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-speedometer text-white"></i>
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
                                        <span class="badge bg-info">{{ $item->line_tujuan }}</span>
                                    </td>
                                    <td>{{ $item->pic ?? '-' }}</td>
                                    <td>
                                        <i class="bi bi-calendar me-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_pemakaian)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @php
                                        $statusColor = match($item->status_penggunaan) {
                                        'Masih Digunakan' => 'success',
                                        'Dikembalikan' => 'warning',
                                        'Selesai' => 'secondary',
                                        default => 'secondary'
                                        };

                                        $statusIcon = match($item->status_penggunaan) {
                                        'Masih Digunakan' => 'check-circle',
                                        'Dikembalikan' => 'arrow-return-left',
                                        'Selesai' => 'check',
                                        default => 'question-circle'
                                        };

                                        // PERUBAHAN: Update tooltip untuk status Selesai
                                        $statusTooltip = match($item->status_penggunaan) {
                                        'Masih Digunakan' => 'Timbangan masih digunakan di ' . $item->line_tujuan,
                                        'Dikembalikan' => 'Timbangan dikembalikan karena ' .
                                        strtolower($item->timbangan->kondisi_saat_ini),
                                        'Selesai' => $item->isSelesaiDipindahkan() ?
                                        'Penggunaan selesai - timbangan dipindahkan ke ' . $item->timbangan->status_line
                                        :
                                        'Penggunaan selesai - timbangan dalam kondisi baik',
                                        default => 'Status penggunaan'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}" data-bs-toggle="tooltip"
                                            title="{{ $statusTooltip }}">
                                            <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $item->status_penggunaan }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                        // AMBIL LANGSUNG DARI DATA TIMBANGAN
                                        $kondisi = $item->timbangan->kondisi_saat_ini;
                                        $badgeColor = match($kondisi) {
                                        'Baik' => 'success',
                                        'Rusak' => 'danger',
                                        'Dalam Perbaikan' => 'warning',
                                        default => 'secondary'
                                        };

                                        $kondisiIcon = match($kondisi) {
                                        'Baik' => 'check-circle',
                                        'Rusak' => 'exclamation-triangle',
                                        'Dalam Perbaikan' => 'tools',
                                        default => 'question-circle'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            <i class="bi bi-{{ $kondisiIcon }} me-1"></i>{{ $kondisi }}
                                        </span>
                                    </td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $penggunaan->firstItem() }} hingga {{ $penggunaan->lastItem() }}
                            dari {{ $penggunaan->total() }} riwayat penggunaan
                        </div>
                        <nav>
                            {{ $penggunaan->appends(request()->query())->links() }}
                        </nav>
                    </div>
                </div>
            </div>
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
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
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
$('#dynamicModal').on('hidden.bs.modal', function() {
    $('#dynamicModalContent').html('');
});

// ==================== MODAL FUNCTIONS ====================

function showCreatePenggunaanModal(timbanganId = null) {
    let url = '{{ route("penggunaan.create") }}';
    if (timbanganId) {
        url = '{{ url("penggunaan/create") }}/' + timbanganId;
    }

    // Show loading
    Swal.fire({
        title: 'Memuat form...',
        text: 'Sedang memuat form penggunaan',
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
                    text: 'Gagal memuat form penggunaan'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            console.error('Error:', xhr);

            let errorMessage = 'Gagal memuat form penggunaan';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        }
    });
}

// Handle form submission dengan konfirmasi khusus
$(document).on('submit', '#createPenggunaanForm', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const timbanganId = form.find('#timbanganSelect').val();
    const selectedOption = form.find('#timbanganSelect option:selected');
    const lokasiSaatIni = selectedOption.data('lokasi');
    const lineTujuan = form.find('[name="line_tujuan"]').val();
    
    // Jika timbangan sedang digunakan di line lain, tampilkan konfirmasi
    if (lokasiSaatIni !== 'Lab' && lokasiSaatIni !== lineTujuan) {
        Swal.fire({
            title: 'Pindahkan Timbangan?',
            html: `Timbangan ini sedang digunakan di <strong>${lokasiSaatIni}</strong>.<br>
                   Apakah Anda yakin ingin memindahkan ke <strong>${lineTujuan}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Pindahkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitPenggunaanForm(form);
            }
        });
    } else {
        submitPenggunaanForm(form);
    }
});

// Function untuk submit form
function submitPenggunaanForm(form) {
    const formData = form.serialize();
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
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
                    timer: 3000,
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
                const errors = xhr.responseJSON.errors;
                clearValidationErrors();
                
                for (const field in errors) {
                    const input = $('[name="' + field + '"]');
                    const errorMsg = errors[field][0];
                    
                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback">' + errorMsg + '</div>');
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menyimpan data'
                });
            }
        }
    });
}
</script>
@endsection