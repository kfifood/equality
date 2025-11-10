@extends('layouts.app')
@section('title', 'Data Peralatan')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-speedometer me-2"></i>Data Peralatan
                    </h5>
                    <div>
                        <!--<button class="btn btn-sm me-2" data-bs-toggle="modal" data-bs-target="#importModal"
                            style="background-color:#4361EE; color:white;">
                            <i class="bi bi-upload me-1"></i>Import
                        </button>
                        <a href="{{ route('timbangan.export') }}" class="btn btn-sm me-2"
                            style="background-color:#4361EE; color:white;">
                            <i class="bi bi-download me-1"></i>Export
                        </a>-->
                        <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                            onclick="showCreateModal()">
                            <i class="bi bi-plus-circle me-1"></i>Tambah
                        </button>
                    </div>
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
                            <form action="{{ route('timbangan.index') }}" method="GET" id="filterForm">
                                <!-- Di bagian Filter Section -->
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Kondisi</label>
                                        <select name="kondisi" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Kondisi</option>
                                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>
                                                Baik</option>
                                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>
                                                Rusak</option>
                                            <option value="Dalam Perbaikan"
                                                {{ request('kondisi') == 'Dalam Perbaikan' ? 'selected' : '' }}>
                                                Dalam Perbaikan
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Lokasi Asli</label>
                                        <select name="lokasi_asli" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Lokasi Asli</option>
                                            <option value="Lab" {{ request('lokasi_asli') == 'Lab' ? 'selected' : '' }}>
                                                Lab</option>
                                            @foreach($lineList as $line)
                                            <option value="{{ $line }}"
                                                {{ request('lokasi_asli') == $line ? 'selected' : '' }}>
                                                {{ $line }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Lokasi Saat Ini</label>
                                        <select name="status_line" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Lokasi</option>
                                            <option value="Lab" {{ request('status_line') == 'Lab' ? 'selected' : '' }}>
                                                Lab</option>
                                            @foreach($lineList as $line)
                                            <option value="{{ $line }}"
                                                {{ request('status_line') == $line ? 'selected' : '' }}>
                                                {{ $line }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pencarian</label>
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Cari kode asset, merk, atau lokasi..."
                                                value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            @if(request()->anyFilled(['kondisi', 'lokasi_asli', 'status_line',
                                            'search']))
                                            <a href="{{ route('timbangan.index') }}" class="btn btn-outline-danger">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="timbanganTable">
                            <!-- Di bagian thead -->
                            <thead class="table-tmb" style="color:#4361EE;">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th>Merk & Seri</th>
                                    <th>Tanggal Datang</th>
                                    <th>Lokasi Asli</th>
                                    <th>Lokasi Saat Ini</th>
                                    <th>Status Lokasi</th>
                                    <th>Kondisi</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timbangan as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $timbangan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-speedometer text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $item->kode_asset }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px;"
                                            title="{{ $item->merk_tipe_no_seri }}">
                                            {{ $item->merk_tipe_no_seri }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $item->tanggal_datang ? \Carbon\Carbon::parse($item->tanggal_datang)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $item->lokasi_asli ?? 'Lab' }}</span>
                                    </td>
                                    <td>
                                        @if($item->status_line)
                                        <span class="badge bg-info">{{ $item->status_line }}</span>
                                        @else
                                        <span class="badge bg-secondary">Lab</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $statusLokasi = $item->status_lokasi;
                                        $badgeColor = match(true) {
                                        $item->isDiLokasiAsli() => 'success',
                                        $item->isDipinjam() => 'warning',
                                        default => 'secondary'
                                        };
                                        $icon = match(true) {
                                        $item->isDiLokasiAsli() => 'check-circle',
                                        $item->isDipinjam() => 'arrow-left-right',
                                        default => 'house'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}" data-bs-toggle="tooltip"
                                            title="{{ $statusLokasi }}">
                                            <i class="bi bi-{{ $icon }} me-1"></i>{{ $statusLokasi }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                        $badgeColor = match($item->kondisi_saat_ini) {
                                        'Baik' => 'success',
                                        'Rusak' => 'danger',
                                        'Dalam Perbaikan' => 'warning',
                                        default => 'secondary'
                                        };
                                        $icon = match($item->kondisi_saat_ini) {
                                        'Baik' => 'check-circle',
                                        'Rusak' => 'exclamation-triangle',
                                        'Dalam Perbaikan' => 'tools',
                                        default => 'question-circle'
                                        };

                                        $tooltip = match($item->kondisi_saat_ini) {
                                        'Baik' => $item->status_line ? 'Sedang digunakan di ' . $item->status_line :
                                        'Siap digunakan (di Lab)',
                                        'Rusak' => 'Timbangan rusak - perlu perbaikan',
                                        'Dalam Perbaikan' => 'Sedang dalam proses perbaikan',
                                        default => 'Status tidak diketahui'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }} rounded-pill" data-bs-toggle="tooltip"
                                            title="{{ $tooltip }}">
                                            <i class="bi bi-{{ $icon }} me-1"></i>{{ $item->kondisi_saat_ini }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info" title="Riwayat"
                                                onclick="showRiwayatModal({{ $item->id }})">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning" title="Edit"
                                                onclick="showEditModal({{ $item->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <!-- TOMBOL BARU: Tandai Rusak untuk timbangan yang Baik dan di Line -->
                                            @if($item->kondisi_saat_ini === 'Baik' && $item->status_line)
                                            <button type="button" class="btn btn-secondary" title="Tandai Rusak"
                                                onclick="tandaiRusak({{ $item->id }})">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </button>
                                            @endif

                                            <button type="button" class="btn btn-danger" title="Hapus"
                                                onclick="deleteTimbangan({{ $item->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Menampilkan {{ $timbangan->firstItem() ?? 0 }} hingga {{ $timbangan->lastItem() ?? 0 }}
        dari {{ $timbangan->total() ?? 0 }} timbangan
    </div>
    
    @if($timbangan->hasPages())
    <nav>
        <ul class="pagination mb-0">
            {{-- Previous Page Link --}}
            @if($timbangan->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">&laquo;</span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link" href="{{ $timbangan->previousPageUrl() }}" rel="prev">&laquo;</a>
            </li>
            @endif

            {{-- Pagination Elements --}}
            @php
                // Custom pagination logic untuk tampilan yang konsisten
                $current = $timbangan->currentPage();
                $last = $timbangan->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
                
                if ($end - $start < 4) {
                    if ($start == 1) {
                        $end = min($last, $start + 4);
                    } else {
                        $start = max(1, $end - 4);
                    }
                }
            @endphp

            {{-- First Page Link --}}
            @if($start > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $timbangan->url(1) }}">1</a>
            </li>
            @if($start > 2)
            <li class="page-item disabled">
                <span class="page-link">...</span>
            </li>
            @endif
            @endif

            {{-- Array Of Links --}}
            @for($i = $start; $i <= $end; $i++)
            <li class="page-item {{ ($i == $current) ? 'active' : '' }}">
                @if($i == $current)
                <span class="page-link">{{ $i }}</span>
                @else
                <a class="page-link" href="{{ $timbangan->url($i) }}">{{ $i }}</a>
                @endif
            </li>
            @endfor

            {{-- Last Page Link --}}
            @if($end < $last)
            @if($end < $last - 1)
            <li class="page-item disabled">
                <span class="page-link">...</span>
            </li>
            @endif
            <li class="page-item">
                <a class="page-link" href="{{ $timbangan->url($last) }}">{{ $last }}</a>
            </li>
            @endif

            {{-- Next Page Link --}}
            @if($timbangan->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $timbangan->nextPageUrl() }}" rel="next">&raquo;</a>
            </li>
            @else
            <li class="page-item disabled">
                <span class="page-link">&raquo;</span>
            </li>
            @endif
        </ul>
    </nav>
    @endif
</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('timbangan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background-color:white; color:#4361EE;">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2"></i>Import Data Timbangan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            Format file: .xlsx, .xls, atau .csv.
                            <a href="{{ route('timbangan.download-template') }}" class="text-decoration-none">
                                <i class="bi bi-download me-1"></i>Download template
                            </a>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Petunjuk Import:</h6>
                        <ul class="mb-0 small">
                            <li>Kolom wajib: kode_asset, merk_tipe_no_seri, tanggal_datang, lokasi_saat_ini</li>
                            <li>Kolom opsional: tanggal_pemakaian, tanggal_kerusakan, keluhan, perbaikan,
                                perbaikan_eksternal, tanggal_rilis, status_line</li>
                            <li>Kondisi: Baik, Rusak, atau Dalam Perbaikan</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Import
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

/* Pagination Styles */
.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #4361EE;
    border: 1px solid #dee2e6;
    padding: 0.375rem 0.75rem;
}

.page-link:hover {
    color: #4361EE;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #4361EE;
    border-color: #4361EE;
    color: white;
}

.page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Pagination Styles */
.pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
    justify-content: center;
}

.page-link {
    color: #4361EE;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    min-width: 42px;
    text-align: center;
}

.page-link:hover {
    color: #4361EE;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #4361EE;
    border-color: #4361EE;
    color: white;
}

.page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Responsive pagination */
@media (max-width: 768px) {
    .pagination {
        font-size: 0.8rem;
    }
    
    .page-link {
        padding: 0.375rem 0.5rem;
        min-width: 36px;
    }
    
    .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between.align-items-center > div:first-child {
        text-align: center;
    }
}
</style>

<!-- SweetAlert2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

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
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 800);
    });
});

// FIXED MODAL FUNCTIONS dengan SweetAlert
function showCreateModal() {
    $.ajax({
        url: '{{ route("timbangan.create") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
                initFormValidation();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat form tambah data'
                });
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat form tambah data'
            });
        }
    });
}

function showEditModal(id) {
    $.ajax({
        url: '{{ url("timbangan") }}/' + id + '/edit',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
                initFormValidation();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat form edit'
                });
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat form edit'
            });
        }
    });
}

function showRiwayatModal(id) {
    console.log('Loading riwayat for ID:', id);

    // Show loading
    Swal.fire({
        title: 'Memuat data...',
        text: 'Sedang mengambil data riwayat',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Clear previous modal content
    $('#dynamicModalContent').html('');

    $.ajax({
        url: '{{ url("timbangan") }}/' + id + '/riwayat',
        type: 'GET',
        dataType: 'json', // Expect JSON
        timeout: 10000, // 10 second timeout
        success: function(response) {
            console.log('Full response:', response);
            Swal.close();

            if (response && response.success) {
                console.log('Success, loading HTML into modal');
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
            } else {
                console.error('Server returned error:', response);
                let errorMsg = (response && response.message) ? response.message :
                    'Gagal memuat data riwayat';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });

            let errorMessage = 'Gagal memuat data riwayat';

            // Check if response is HTML (Laravel error page)
            if (xhr.responseText && xhr.responseText.trim().startsWith('<!DOCTYPE html>')) {
                errorMessage = 'Terjadi error di server. Cek log Laravel untuk detailnya.';

                // Try to extract error message from HTML response
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = xhr.responseText;
                const errorElement = tempDiv.querySelector('.error-message, .exception-message');
                if (errorElement) {
                    errorMessage += '\n' + errorElement.textContent.substring(0, 200);
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 500) {
                errorMessage = 'Internal Server Error - Cek log server';
            } else if (xhr.status === 404) {
                errorMessage = 'Data tidak ditemukan';
            } else if (xhr.status === 0) {
                errorMessage = 'Koneksi terputus atau timeout';
            }

            Swal.fire({
                icon: 'error',
                title: 'Error ' + xhr.status,
                text: errorMessage,
                footer: '<small>Periksa console browser untuk detail lebih lanjut</small>'
            });
        }
    });
}

// Function untuk menandai timbangan rusak
function tandaiRusak(id) {
    Swal.fire({
        title: 'Tandai Timbangan Rusak?',
        text: "Timbangan akan ditandai rusak dan bisa dicatat di menu Perbaikan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Tandai Rusak!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menandai timbangan rusak',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ url("timbangan") }}/' + id + '/tandai-rusak',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Gagal menandai timbangan rusak'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    console.error('Error:', xhr);

                    let errorMessage = 'Gagal menandai timbangan rusak';
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
    });
}

function deleteTimbangan(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data timbangan akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                text: 'Sedang menghapus data',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ url("timbangan") }}/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Gagal menghapus data'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    console.error('Error:', xhr);

                    let errorMessage = 'Gagal menghapus data';
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
    });
}

// Initialize form validation
function initFormValidation() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

// Handle form submission for create and edit dengan SweetAlert
$(document).on('submit', '#createForm, #editForm', function(e) {
    e.preventDefault();

    const form = $(this);
    const url = form.attr('action');
    const method = form.attr('method');
    const formData = form.serialize();

    // Show loading state
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

    $.ajax({
        url: url,
        type: method,
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
                const errors = xhr.responseJSON.errors;
                clearValidationErrors();

                for (const field in errors) {
                    const input = $('[name="' + field + '"]');
                    const errorMsg = errors[field][0];

                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback">' + errorMsg + '</div>');
                }

                // Scroll ke error pertama
                $('.is-invalid').first().focus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menyimpan data'
                });
            }
        }
    });
});

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

// Close modal handler
$('#dynamicModal').on('hidden.bs.modal', function() {
    $('#dynamicModalContent').html('');
});
</script>
@endsection