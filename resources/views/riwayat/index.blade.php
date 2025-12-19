@extends('layouts.app')
@section('title', 'Riwayat Lengkap')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Riwayat Lengkap 
                    </h5>
                    <a href="{{ route('riwayat.timeline') }}" class="btn btn-sm" style="background-color:#4361EE; color:white;">
                        <i class="bi bi-list-ul me-1"></i>Lihat Timeline
                    </a>
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
                            <form action="{{ route('riwayat.index') }}" method="GET" id="filterForm">
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
                                        <label class="form-label">Timbangan</label>
                                        <select name="timbangan_id" class="form-select">
                                            <option value="">Semua Timbangan</option>
                                            @foreach($timbanganList as $timbangan)
                                                <option value="{{ $timbangan->id }}" 
                                                    {{ request('timbangan_id') == $timbangan->id ? 'selected' : '' }}>
                                                    {{ $timbangan->kode_asset }}
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
                                        <label class="form-label">Line</label>
                                        <input type="text" name="line" class="form-control" 
                                               value="{{ request('line') }}" placeholder="Cari line...">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" title="Filter">
                                                <i class="bi bi-funnel"></i>
                                            </button>
                                            <a href="{{ route('riwayat.index') }}" 
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

                    <ul class="nav nav-tabs" id="riwayatTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="penggunaan-tab" data-bs-toggle="tab" 
                                    data-bs-target="#penggunaan" type="button" role="tab">
                                <i class="bi bi-arrow-right-circle me-1"></i>Riwayat Penggunaan
                                <span class="badge bg-primary ms-1">{{ $riwayatPenggunaan->total() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="perbaikan-tab" data-bs-toggle="tab" 
                                    data-bs-target="#perbaikan" type="button" role="tab">
                                <i class="bi bi-tools me-1"></i>Riwayat Perbaikan
                                <span class="badge bg-warning ms-1">{{ $riwayatPerbaikan->total() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="riwayatTabsContent">
                        <!-- Tab Penggunaan -->
                        <div class="tab-pane fade show active" id="penggunaan" role="tabpanel">
                            @if($riwayatPenggunaan->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped" id="penggunaanTable">
                                        <thead class="table-tmb" style="color:#4361EE;">
                                            <tr>
                                                <th width="50">No</th>
                                                <th>Kode Asset</th>
                                                <th>Line Tujuan</th>
                                                <th>PIC</th>
                                                <th>Tanggal Pemakaian</th>
                                                <th>Status</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($riwayatPenggunaan as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $riwayatPenggunaan->firstItem() + $index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="bi bi-speedometer text-white"></i>
                                                        </div>
                                                        <div>
                                                            <strong>
                                                                <a href="javascript:void(0)" onclick="showTimbanganRiwayat({{ $item->timbangan_id }})" 
                                                                   class="text-decoration-none">
                                                                    {{ $item->timbangan->kode_asset }}
                                                                </a>
                                                            </strong>
                                                        </div>
                                                    </div>
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
                                                    @endphp
                                                    <span class="badge bg-{{ $statusColor }}">
                                                        {{ $item->status_penggunaan }}
                                                    </span>
                                                </td>
                                                <td>{{ $item->keterangan ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- PAGINATION PENGEMBALIAN - SIMPLE VERSION -->
                                @if($riwayatPenggunaan->hasPages())
                                <div class="mt-4">
                                    <nav aria-label="Pagination Navigation">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                            <div class="mb-2 mb-md-0">
                                                <p class="small text-muted mb-0">
                                                    Menampilkan 
                                                    <span class="fw-semibold">{{ $riwayatPenggunaan->firstItem() }}</span>
                                                    sampai 
                                                    <span class="fw-semibold">{{ $riwayatPenggunaan->lastItem() }}</span>
                                                    dari 
                                                    <span class="fw-semibold">{{ $riwayatPenggunaan->total() }}</span>
                                                    data
                                                </p>
                                            </div>
                                            
                                            <div>
                                                <div class="btn-group" role="group" aria-label="Pagination">
                                                    {{-- Previous Page --}}
                                                    @if($riwayatPenggunaan->onFirstPage())
                                                        <button type="button" class="btn btn-outline-primary btn-sm disabled">
                                                            <i class="bi bi-chevron-left"></i> Prev
                                                        </button>
                                                    @else
                                                        <a href="{{ $riwayatPenggunaan->appends(request()->except('penggunaan_page'))->previousPageUrl() }}" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-chevron-left"></i> Prev
                                                        </a>
                                                    @endif
                                                    
                                                    {{-- Current Page Info --}}
                                                    <button type="button" class="btn btn-primary btn-sm disabled">
                                                        Halaman {{ $riwayatPenggunaan->currentPage() }} / {{ $riwayatPenggunaan->lastPage() }}
                                                    </button>
                                                    
                                                    {{-- Next Page --}}
                                                    @if($riwayatPenggunaan->hasMorePages())
                                                        <a href="{{ $riwayatPenggunaan->appends(request()->except('penggunaan_page'))->nextPageUrl() }}" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            Next <i class="bi bi-chevron-right"></i>
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-outline-primary btn-sm disabled">
                                                            Next <i class="bi bi-chevron-right"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </nav>
                                </div>
                                @endif
                                
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-arrow-right-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data riwayat penggunaan</p>
                                </div>
                            @endif
                        </div>

                        <!-- Tab Perbaikan -->
                        <div class="tab-pane fade" id="perbaikan" role="tabpanel">
                            @if($riwayatPerbaikan->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped" id="perbaikanTable">
                                        <thead class="table-tmb" style="color:#4361EE;">
                                            <tr>
                                                <th width="50">No</th>
                                                <th>Kode Asset</th>
                                                <th>Line Sebelumnya</th>
                                                <th>Keluhan</th>
                                                <th>Status Perbaikan</th>
                                                <th>Tanggal Masuk</th>
                                                <th>Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($riwayatPerbaikan as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $riwayatPerbaikan->firstItem() + $index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="bi bi-tools text-white"></i>
                                                        </div>
                                                        <div>
                                                            <strong>
                                                                <a href="javascript:void(0)" onclick="showTimbanganRiwayat({{ $item->timbangan_id }})" 
                                                                   class="text-decoration-none">
                                                                    {{ $item->timbangan->kode_asset }}
                                                                </a>
                                                            </strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $item->line_sebelumnya }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
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
                                                    @if($item->durasi_perbaikan)
                                                        <span class="badge bg-light text-dark">
                                                            {{ $item->durasi_perbaikan }} hari
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- PAGINATION PERBAIKAN - SIMPLE VERSION -->
                                @if($riwayatPerbaikan->hasPages())
                                <div class="mt-4">
                                    <nav aria-label="Pagination Navigation">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                            <div class="mb-2 mb-md-0">
                                                <p class="small text-muted mb-0">
                                                    Menampilkan 
                                                    <span class="fw-semibold">{{ $riwayatPerbaikan->firstItem() }}</span>
                                                    sampai 
                                                    <span class="fw-semibold">{{ $riwayatPerbaikan->lastItem() }}</span>
                                                    dari 
                                                    <span class="fw-semibold">{{ $riwayatPerbaikan->total() }}</span>
                                                    data
                                                </p>
                                            </div>
                                            
                                            <div>
                                                <div class="btn-group" role="group" aria-label="Pagination">
                                                    {{-- Previous Page --}}
                                                    @if($riwayatPerbaikan->onFirstPage())
                                                        <button type="button" class="btn btn-outline-warning btn-sm disabled">
                                                            <i class="bi bi-chevron-left"></i> Prev
                                                        </button>
                                                    @else
                                                        <a href="{{ $riwayatPerbaikan->appends(request()->except('perbaikan_page'))->previousPageUrl() }}" 
                                                           class="btn btn-outline-warning btn-sm">
                                                            <i class="bi bi-chevron-left"></i> Prev
                                                        </a>
                                                    @endif
                                                    
                                                    {{-- Current Page Info --}}
                                                    <button type="button" class="btn btn-warning btn-sm disabled">
                                                        Halaman {{ $riwayatPerbaikan->currentPage() }} / {{ $riwayatPerbaikan->lastPage() }}
                                                    </button>
                                                    
                                                    {{-- Next Page --}}
                                                    @if($riwayatPerbaikan->hasMorePages())
                                                        <a href="{{ $riwayatPerbaikan->appends(request()->except('perbaikan_page'))->nextPageUrl() }}" 
                                                           class="btn btn-outline-warning btn-sm">
                                                            Next <i class="bi bi-chevron-right"></i>
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-outline-warning btn-sm disabled">
                                                            Next <i class="bi bi-chevron-right"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </nav>
                                </div>
                                @endif
                                
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-tools fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data riwayat perbaikan</p>
                                </div>
                            @endif
                        </div>
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

/* PAGINATION BUTTON STYLES */
.btn-group .btn {
    border-radius: 0;
    min-width: 80px;
}
.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}
.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
.btn-group .btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<!-- Loading JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS Bundle (popper + bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

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

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Simpan tab state ke localStorage
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        try {
            localStorage.setItem('activeRiwayatTab', $(e.target).attr('id'));
        } catch (e) {
            console.log('LocalStorage not available');
        }
    });

    // Load tab state dari localStorage
    try {
        var activeTab = localStorage.getItem('activeRiwayatTab');
        if (activeTab) {
            $('#' + activeTab).tab('show');
        }
    } catch (e) {
        console.log('LocalStorage not available');
    }
});

// Function untuk show timbangan riwayat
function showTimbanganRiwayat(timbanganId) {
    Swal.fire({
        title: 'Memuat data...',
        text: 'Sedang mengambil data riwayat',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '{{ url("timbangan") }}/' + timbanganId + '/riwayat',
        type: 'GET',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                var modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data riwayat'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            console.error('Error:', xhr);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat data riwayat'
            });
        }
    });
}

// Close modal handler
$('#dynamicModal').on('hidden.bs.modal', function () {
    $('#dynamicModalContent').html('');
});
</script>
@endsection