@extends('layouts.app')
@section('title', 'Timeline Riwayat')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul me-2"></i>Timeline Riwayat
                    </h5>
                    <a href="{{ route('riwayat.index') }}" class="btn btn-sm" style="background-color:#4361EE; color:white;">
                        <i class="bi bi-table me-1"></i>Lihat Tabel
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('riwayat.timeline') }}" method="GET" id="filterForm">
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
                                    <div class="col-md-2 d-flex align-items-end">
    <div class="d-flex gap-2 w-100">
        <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" title="Filter">
            <i class="bi bi-funnel"></i>
        </button>
        <a href="{{ route('riwayat.timeline') }}" 
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

                    <!-- Timeline -->
                    @if($riwayat->count() > 0)
                        <div class="timeline">
                            @foreach($riwayat as $item)
                            <div class="timeline-item mb-4">
                                <div class="timeline-marker">
                                    @if($item->jenis == 'penggunaan')
                                        <i class="bi bi-arrow-right-circle text-success"></i>
                                    @else
                                        <i class="bi bi-tools text-warning"></i>
                                    @endif
                                </div>
                                <div class="timeline-content card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                <a href="javascript:void(0)" onclick="showTimbanganRiwayat({{ $item->timbangan_id }})" 
                                                   class="text-decoration-none">
                                                    {{ $item->timbangan->kode_asset }}
                                                </a>
                                                <span class="badge bg-{{ $item->jenis == 'penggunaan' ? 'success' : 'warning' }} ms-2">
                                                    <i class="bi bi-{{ $item->jenis == 'penggunaan' ? 'arrow-right-circle' : 'tools' }} me-1"></i>
                                                    {{ $item->jenis == 'penggunaan' ? 'Penggunaan' : 'Perbaikan' }}
                                                </span>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                            </small>
                                        </div>
                                        
                                        @if($item->jenis == 'penggunaan')
                                            <p class="mb-1">
                                                <i class="bi bi-arrow-right text-success me-1"></i>
                                                Digunakan di <strong>{{ $item->lokasi }}</strong>
                                                @if($item->pic)
                                                    oleh <strong>{{ $item->pic }}</strong>
                                                @endif
                                            </p>
                                            @if($item->keterangan)
                                                <p class="text-muted mb-0"><small>{{ $item->keterangan }}</small></p>
                                            @endif
                                        @else
                                            <p class="mb-1">
                                                <i class="bi bi-arrow-left text-warning me-1"></i>
                                                Dikembalikan dari <strong>{{ $item->lokasi }}</strong> untuk perbaikan
                                            </p>
                                            <p class="text-muted mb-0"><small>{{ $item->keterangan }}</small></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $riwayat->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada data riwayat</p>
                        </div>
                    @endif
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
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #dee2e6;
}

.timeline-content {
    margin-left: 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -18px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Auto-submit search with delay
    let searchTimer;
    $('input[name="kode_asset"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 800);
    });
});

// Function untuk show timbangan riwayat
function showTimbanganRiwayat(timbanganId) {
    // Show loading
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
                $('#dynamicModal').modal('show');
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