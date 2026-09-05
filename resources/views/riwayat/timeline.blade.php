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
                                        <label class="form-label">Peralatan</label>
                                        <select name="timbangan_id" class="form-select">
                                            <option value="">Semua Peralatan</option>
                                            @foreach($peralatanList as $peralatan)
                                                <option value="{{ $peralatan->id }}" 
                                                    {{ request('timbangan_id') == $peralatan->id ? 'selected' : '' }}>
                                                    {{ $peralatan->kode_asset }}
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
                                                <a href="{{ route('riwayat.peralatan', $item->peralatan_id) }}" 
                                                   class="text-decoration-none">
                                                    {{ $item->peralatan->kode_asset ?? '-' }}
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
                                        
                                        <!-- PERUBAHAN: Tampilkan info lokasi asli dan status -->
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <small class="text-muted">Lokasi Asli:</small>
                                                <span class="badge bg-primary ms-1">{{ $item->peralatan->lokasi_asli ?? 'Lab' }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Status Saat Ini:</small>
                                                @php
                                                    $statusColor = match($item->peralatan->kondisi_saat_ini ?? null) {
                                                        'Baik' => 'success',
                                                        'Rusak' => 'danger',
                                                        'Dalam Perbaikan' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }} ms-1">
                                                    {{ $item->peralatan->kondisi_saat_ini ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        @if($item->jenis == 'penggunaan')
                                            <p class="mb-1">
                                                <i class="bi bi-arrow-right text-success me-1"></i>
                                                Digunakan di <strong>{{ $item->lokasi }}</strong>
                                                @if($item->pic)
                                                    oleh <strong>{{ $item->pic }}</strong>
                                                @endif
                                            </p>
                                            <!-- PERUBAHAN: Tampilkan status penggunaan -->
                                            @php
                                                $penggunaan = $item->jenis == 'penggunaan' ? 
                                                    \App\Models\RiwayatPenggunaan::find($item->id) : null;
                                                $statusPenggunaan = $penggunaan ? $penggunaan->status_penggunaan : null;
                                                $statusColor = match($statusPenggunaan) {
                                                    'Masih Digunakan' => 'success',
                                                    'Dikembalikan' => 'warning',
                                                    'Selesai' => 'secondary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            @if($statusPenggunaan)
                                                <span class="badge bg-{{ $statusColor }} mb-2">
                                                    Status: {{ $statusPenggunaan }}
                                                </span>
                                            @endif
                                            @if($item->keterangan)
                                                <p class="text-muted mb-0"><small>{{ $item->keterangan }}</small></p>
                                            @endif
                                        @else
                                            <p class="mb-1">
                                                <i class="bi bi-arrow-left text-warning me-1"></i>
                                                Dikembalikan dari <strong>{{ $item->lokasi }}</strong> untuk perbaikan
                                            </p>
                                            <!-- PERUBAHAN: Tampilkan status perbaikan -->
                                            @php
                                                $perbaikan = $item->jenis == 'perbaikan' ? 
                                                    \App\Models\RiwayatPerbaikan::find($item->id) : null;
                                                $statusPerbaikan = $perbaikan ? $perbaikan->status_perbaikan : null;
                                                $statusColor = match($statusPerbaikan) {
                                                    'Masuk Lab' => 'secondary',
                                                    'Dalam Perbaikan' => 'warning',
                                                    'Selesai' => 'success',
                                                    'Dikirim Eksternal' => 'info',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            @if($statusPerbaikan)
                                                <span class="badge bg-{{ $statusColor }} mb-2">
                                                    <i class="bi bi-{{ match($statusPerbaikan) {
                                                        'Masuk Lab' => 'box-arrow-in-down',
                                                        'Dalam Perbaikan' => 'tools',
                                                        'Selesai' => 'check-circle',
                                                        'Dikirim Eksternal' => 'arrow-right-circle',
                                                        default => 'question-circle'
                                                    } }} me-1"></i>
                                                    {{ $statusPerbaikan }}
                                                </span>
                                            @endif
                                            <p class="text-muted mb-0"><small>{{ $item->keterangan }}</small></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($riwayat->hasPages())
                        <div class="mt-4">
                            <nav aria-label="Pagination Navigation">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                    <div class="mb-2 mb-md-0">
                                        <p class="small text-muted mb-0">
                                            Menampilkan
                                            <span class="fw-semibold">{{ $riwayat->firstItem() }}</span>
                                            sampai
                                            <span class="fw-semibold">{{ $riwayat->lastItem() }}</span>
                                            dari
                                            <span class="fw-semibold">{{ $riwayat->total() }}</span>
                                            data
                                        </p>
                                    </div>

                                    <div>
                                        <div class="btn-group" role="group" aria-label="Pagination">
                                            {{-- Previous Page --}}
                                            @if($riwayat->onFirstPage())
                                                <button type="button" class="btn btn-outline-primary btn-sm disabled">
                                                    <i class="bi bi-chevron-left"></i> Prev
                                                </button>
                                            @else
                                                <a href="{{ $riwayat->appends(request()->query())->previousPageUrl() }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-chevron-left"></i> Prev
                                                </a>
                                            @endif

                                            {{-- Current Page Info --}}
                                            <button type="button" class="btn btn-primary btn-sm disabled">
                                                Halaman {{ $riwayat->currentPage() }} / {{ $riwayat->lastPage() }}
                                            </button>

                                            {{-- Next Page --}}
                                            @if($riwayat->hasMorePages())
                                                <a href="{{ $riwayat->appends(request()->query())->nextPageUrl() }}"
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

// NOTE: showTimbanganRiwayat() (AJAX + modal) dihapus — Kode Asset di atas sekarang
// langsung berupa link <a> ke halaman riwayat.peralatan, karena RiwayatController::peralatan()
// mengembalikan full page view, bukan JSON {success, html} seperti pola modal di menu lain.


// Close modal handler
$('#dynamicModal').on('hidden.bs.modal', function () {
    $('#dynamicModalContent').html('');
});
</script>
@endsection