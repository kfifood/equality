@extends('layouts.app')
@section('title', 'Perbaikan Alat')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-tools me-2"></i>Perbaikan Alat
                        </h5>
                        <small class="text-muted">
                            Daftar laporan kerusakan beserta proses penanganannya
                        </small>
                    </div>
                    <a href="{{ route('penggunaan.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>Ke Penggunaan Alat
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="d-none" id="session-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="d-none" id="session-error">{{ session('error') }}</div>
                    @endif

                    {{-- ── Filter ───────────────────────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('perbaikan.index') }}" method="GET" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status Laporan</label>
                                        <select name="status" class="form-select">
                                            <option value="">Semua Status</option>
                                            @foreach($statusOptions as $s)
                                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                                    {{ $s }}
                                                </option>
                                            @endforeach
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
                                    <div class="col-md-3 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit"
                                                class="btn btn-primary d-flex align-items-center justify-content-center"
                                                title="Filter">
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

                    {{-- ── Tabel Data ──────────────────────────────────────────── --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="perbaikanTable">
                            <thead style="color:#4361EE; background-color:#f8f9fa;">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th>Merk &amp; Tipe</th>
                                    <th>Line Asal</th>
                                    <th>Keluhan</th>
                                    <th>Tgl Laporan</th>
                                    <th>Status Laporan</th>
                                    <th>Status Perbaikan</th>
                                    <th>Durasi</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laporanList as $index => $laporan)
                                @php
                                    $perbaikanTerkini   = $laporan->riwayatPerbaikan->first();
                                    $statusLaporanColor = match($laporan->status) {
                                        'Menunggu' => 'warning',
                                        'Diproses' => 'info',
                                        'Selesai'  => 'success',
                                        default    => 'secondary',
                                    };
                                    $statusLaporanIcon  = match($laporan->status) {
                                        'Menunggu' => 'clock',
                                        'Diproses' => 'gear',
                                        'Selesai'  => 'check-circle',
                                        default    => 'question-circle',
                                    };
                                    $durasi = $perbaikanTerkini?->durasi_perbaikan;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $laporanList->firstItem() + $index }}</td>

                                    {{-- Kode Asset --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-tools text-white"></i>
                                            </div>
                                            <strong>{{ $laporan->timbangan->kode_asset }}</strong>
                                        </div>
                                    </td>

                                    {{-- Merk & Tipe --}}
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width:180px;"
                                            title="{{ $laporan->timbangan->merk_tipe_no_seri }}">
                                            {{ $laporan->timbangan->merk_tipe_no_seri }}
                                        </span>
                                    </td>

                                    {{-- Line Asal --}}
                                    <td>
                                        <span class="badge bg-info">{{ $laporan->line_asal ?? '-' }}</span>
                                    </td>

                                    {{-- Keluhan --}}
                                    <td>
                                        @php
                                            $keluhanItems  = $laporan->keluhanList;
                                            $keluhanTampil = $keluhanItems->take(2)->pluck('nama_keluhan');
                                            $sisanya       = $keluhanItems->count() - 2;
                                        @endphp
                                        <span class="text-truncate d-inline-block" style="max-width:180px;"
                                            title="{{ $laporan->keluhan_ringkas }}">
                                            {{ $keluhanTampil->implode(', ') }}
                                            @if($sisanya > 0)
                                                <span class="text-muted">+{{ $sisanya }} lagi</span>
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Tgl Laporan --}}
                                    <td>
                                        <i class="bi bi-calendar me-1 text-primary"></i>
                                        {{ $laporan->tanggal_laporan->format('d/m/Y') }}
                                    </td>

                                    {{-- Status Laporan --}}
                                    <td>
                                        <span class="badge bg-{{ $statusLaporanColor }}">
                                            <i class="bi bi-{{ $statusLaporanIcon }} me-1"></i>{{ $laporan->status }}
                                        </span>
                                    </td>

                                    {{-- Status Perbaikan --}}
                                    <td>
                                        @if($perbaikanTerkini)
                                            <span class="badge bg-secondary">
                                                {{ $perbaikanTerkini->status_perbaikan }}
                                            </span>
                                        @else
                                            <span class="text-muted small">Belum ada</span>
                                        @endif
                                    </td>

                                    {{-- Durasi --}}
                                    <td>
                                        @if($durasi)
                                            <span class="badge bg-light text-dark">{{ $durasi }} hari</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                onclick="showDetailModal({{ $laporan->id }})"
                                                data-bs-toggle="tooltip" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            @if(!$laporan->isSelesai())
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="showProsesModal({{ $laporan->id }})"
                                                    data-bs-toggle="tooltip" title="Proses Perbaikan">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            @else
                                                <span class="badge bg-success py-2 px-2">
                                                    <i class="bi bi-check-circle me-1"></i>Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada laporan kerusakan.
                                        Laporan masuk otomatis dari menu
                                        <a href="{{ route('penggunaan.index') }}">Penggunaan Alat</a>.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Pagination ───────────────────────────────────────── --}}
                    @if($laporanList->hasPages())
                    @php
                        $cur   = $laporanList->currentPage();
                        $last  = $laporanList->lastPage();
                        $pages = collect();
                        for ($i = 1; $i <= min(2, $last); $i++) $pages->push($i);
                        for ($i = max(1, $cur - 2); $i <= min($last, $cur + 2); $i++) $pages->push($i);
                        for ($i = max(1, $last - 1); $i <= $last; $i++) $pages->push($i);
                        $pages = $pages->unique()->sort()->values();
                        $q     = request()->except('page');
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $laporanList->firstItem() }}</strong>
                            hingga <strong>{{ $laporanList->lastItem() }}</strong>
                            dari <strong>{{ $laporanList->total() }}</strong> laporan
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                {{-- << First --}}
                                <li class="page-item {{ $cur == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $laporanList->url(1) }}&{{ http_build_query($q) }}">&laquo;</a>
                                </li>
                                {{-- < Prev --}}
                                <li class="page-item {{ $cur == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $laporanList->previousPageUrl() ?? '#' }}&{{ http_build_query($q) }}">&lsaquo;</a>
                                </li>

                                @php $prev = null; @endphp
                                @foreach($pages as $page)
                                    @if($prev !== null && $page - $prev > 1)
                                        <li class="page-item disabled"><span class="page-link px-2">…</span></li>
                                    @endif
                                    <li class="page-item {{ $page == $cur ? 'active' : '' }}">
                                        @if($page == $cur)
                                            <span class="page-link">{{ $page }}</span>
                                        @else
                                            <a class="page-link" href="{{ $laporanList->url($page) }}&{{ http_build_query($q) }}">{{ $page }}</a>
                                        @endif
                                    </li>
                                    @php $prev = $page; @endphp
                                @endforeach

                                {{-- > Next --}}
                                <li class="page-item {{ !$laporanList->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $laporanList->nextPageUrl() ?? '#' }}&{{ http_build_query($q) }}">&rsaquo;</a>
                                </li>
                                {{-- >> Last --}}
                                <li class="page-item {{ !$laporanList->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $laporanList->url($last) }}&{{ http_build_query($q) }}">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Dynamic Modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="dynamicModalContent"></div>
    </div>
</div>

<style>
.avatar-sm { width: 36px; height: 36px; font-size: 0.9rem; }
.card { border: none; border-radius: 12px; }
.table th { font-weight: 600; background-color: #f8f9fa !important; }
.badge { font-size: 0.75em; }
.pagination .page-item.active .page-link {
    background-color: #4361EE;
    border-color: #4361EE;
    color: #fff;
    font-weight: 600;
}
.pagination .page-link {
    color: #4361EE;
    border-radius: 6px !important;
    margin: 0 2px;
    min-width: 34px;
    text-align: center;
}
.pagination .page-link:hover { background-color: #eef0fd; color: #4361EE; }
.pagination .page-item.disabled .page-link { color: #adb5bd; }
</style>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const successMsg = $('#session-success').text();
    const errorMsg   = $('#session-error').text();
    if (successMsg) Swal.fire({ icon:'success', title:'Berhasil', text:successMsg, timer:3000, showConfirmButton:false });
    if (errorMsg)   Swal.fire({ icon:'error',   title:'Error',    text:errorMsg,   timer:4000 });

    let searchTimer;
    $('input[name="kode_asset"]').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => $('#filterForm').submit(), 800);
    });

    $('[data-bs-toggle="tooltip"]').each(function () { new bootstrap.Tooltip(this); });

    $('#dynamicModal').on('hidden.bs.modal', function () {
        $('#dynamicModalContent').html('');
    });
});

function showProsesModal(laporanId) {
    Swal.fire({ title: 'Memuat form...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '{{ url("perbaikan") }}/' + laporanId + '/proses',
        type: 'GET',
        success: function (res) {
            Swal.close();
            if (res.success) {
                $('#dynamicModalContent').html(res.html);
                $('#dynamicModal').modal('show');
            } else {
                Swal.fire({ icon: 'warning', title: 'Tidak Bisa Diproses', text: res.message });
            }
        },
        error: function (xhr) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal memuat form proses.' });
        }
    });
}

function showDetailModal(laporanId) {
    Swal.fire({ title: 'Memuat detail...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '{{ url("perbaikan") }}/' + laporanId + '/detail',
        type: 'GET',
        success: function (res) {
            Swal.close();
            if (res.success) {
                $('#dynamicModalContent').html(res.html);
                $('#dynamicModal').modal('show');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message });
            }
        },
        error: function (xhr) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal memuat detail.' });
        }
    });
}
</script>
@endpush