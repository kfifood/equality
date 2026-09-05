@extends('layouts.app')
@section('title', 'Laporan Alat')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="bi bi-file-earmark-text me-2"></i>Laporan Alat
                    </h5>
                    <div>
                        <a href="{{ route('laporan.statistik') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="bi bi-graph-up me-1"></i>Statistik
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter & Export Section -->
                    <div class="card mb-4 border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                {{-- Tahun --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Tahun</label>
                                    <select class="form-select" id="filterYear">
                                        @foreach($years as $y)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Bulan --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Bulan</label>
                                    <select class="form-select" id="filterMonth">
                                        @foreach($months as $key => $name)
                                            <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Filter Line --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Line / Lokasi</label>
                                    <select class="form-select" id="filterLine">
                                        <option value="" {{ $line === '' ? 'selected' : '' }}>Semua Line</option>
                                        @foreach($lineList as $l)
                                            <option value="{{ $l }}" {{ $line === $l ? 'selected' : '' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Format --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Format</label>
                                    <select class="form-select" id="exportType">
                                        <option value="excel">Excel</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>

                                {{-- Tipe Laporan (hanya untuk Excel).
                                     NOTE: value option di bawah ini (summary/riwayat/lengkap/timbangan/penggunaan/perbaikan)
                                     harus sama persis dengan $format yang dicek di App\Exports\PeralatanExport.
                                     File Export itu belum aku lihat isinya — kalau kamu sudah/belum rename
                                     case 'timbangan' di sana, samakan value option "timbangan" ini juga. --}}
                                <div class="col-md-2" id="exportFormatWrapper">
                                    <label class="form-label fw-semibold">Tipe Laporan</label>
                                    <select class="form-select" id="exportFormat">
                                        <option value="summary">Summary Lengkap</option>
                                        <option value="riwayat">Riwayat Pergerakan</option>
                                        <option value="lengkap">Laporan Lengkap</option>
                                        <option value="timbangan">Data Peralatan</option>
                                        <option value="penggunaan">Riwayat Penggunaan</option>
                                        <option value="perbaikan">Riwayat Perbaikan</option>
                                    </select>
                                </div>

                                {{-- Tombol --}}
                                <div class="col-md-2">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-secondary" id="btnFilter">
                                            <i class="bi bi-funnel me-1"></i>Terapkan Filter
                                        </button>
                                        <button type="button" class="btn btn-primary" id="btnExport">
                                            <i class="bi bi-download me-1"></i>Export Laporan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Label filter aktif --}}
                            @if($line !== '')
                                <div class="mt-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1">
                                        <i class="bi bi-geo-alt me-1"></i>Filter Line: <strong>{{ $line }}</strong>
                                        <a href="{{ route('laporan.index', ['year' => $year, 'month' => $month]) }}"
                                           class="ms-2 text-primary text-decoration-none" title="Hapus filter">×</a>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-5">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 bg-white shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Total Alat</h6>
                                            <h2 class="mb-0 text-primary">{{ $statistik['total'] }}</h2>
                                            <small class="text-muted">{{ $line ?: 'Semua Line' }}</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-speedometer fa-2x text-primary opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 bg-white shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Kondisi Baik</h6>
                                            <h2 class="mb-0 text-success">{{ $statistik['baik'] }}</h2>
                                            <small class="text-muted">{{ $statistik['persentase_baik'] }}% dari total</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-check-circle fa-2x text-success opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 bg-white shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Penggunaan</h6>
                                            <h2 class="mb-0 text-info">{{ $penggunaanPeriod }}</h2>
                                            <small class="text-muted">Bulan {{ $months[$month] }}</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-arrow-right-circle fa-2x text-info opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-0 bg-white shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Perbaikan</h6>
                                            <h2 class="mb-0 text-warning">{{ $perbaikanPeriod }}</h2>
                                            <small class="text-muted">Bulan {{ $months[$month] }}</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-tools fa-2x text-warning opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribution Charts -->
                    <div class="row mb-5">
                        <!-- Distribusi Line -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-diagram-3 me-2"></i>Distribusi per Line
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($distribusiLine->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Line</th>
                                                        <th class="text-end">Jumlah</th>
                                                        <th class="text-end">Persentase</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($distribusiLine as $dist)
                                                    <tr>
                                                        <td>
                                                            <i class="bi bi-geo-alt text-primary me-2"></i>
                                                            {{ $dist->status_line }}
                                                        </td>
                                                        <td class="text-end fw-bold">{{ $dist->total }}</td>
                                                        <td class="text-end">
                                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                                {{ $statistik['di_line'] > 0 ? round(($dist->total / $statistik['di_line']) * 100, 1) : 0 }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bi bi-diagram-3 fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada peralatan di line</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Summary -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-pie-chart me-2"></i>Ringkasan Status
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="border-end">
                                                <div class="fw-bold text-success fs-4">{{ $statistik['baik'] }}</div>
                                                <small class="text-muted">Baik</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border-end">
                                                <div class="fw-bold text-warning fs-4">{{ $statistik['perbaikan'] }}</div>
                                                <small class="text-muted">Perbaikan</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div>
                                                <div class="fw-bold text-danger fs-4">{{ $statistik['rusak'] }}</div>
                                                <small class="text-muted">Rusak</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $statistik['persentase_baik'] }}%">
                                                {{ $statistik['persentase_baik'] }}%
                                            </div>
                                            <div class="progress-bar bg-warning" style="width: {{ $statistik['total'] > 0 ? round(($statistik['perbaikan'] / $statistik['total']) * 100, 1) : 0 }}%">
                                                {{ $statistik['total'] > 0 ? round(($statistik['perbaikan'] / $statistik['total']) * 100, 1) : 0 }}%
                                            </div>
                                            <div class="progress-bar bg-danger" style="width: {{ $statistik['total'] > 0 ? round(($statistik['rusak'] / $statistik['total']) * 100, 1) : 0 }}%">
                                                {{ $statistik['total'] > 0 ? round(($statistik['rusak'] / $statistik['total']) * 100, 1) : 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <!-- Recent Penggunaan -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-arrow-right-circle me-2"></i>Penggunaan Terbaru
                                    </h6>
                                    <span class="badge bg-primary">{{ $recentPenggunaan->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    @if($recentPenggunaan->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-3">Kode Asset</th>
                                                        <th>Line Tujuan</th>
                                                        <th class="pe-3">Tanggal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentPenggunaan as $penggunaan)
                                                    <tr>
                                                        <td class="ps-3 fw-medium">{{ $penggunaan->peralatan->kode_asset ?? '-' }}</td>
                                                        <td><span class="badge bg-info">{{ $penggunaan->line_tujuan }}</span></td>
                                                        <td class="pe-3">{{ \Carbon\Carbon::parse($penggunaan->tanggal_pemakaian)->format('d/m/Y') }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bi bi-arrow-right-circle fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada data penggunaan</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Recent Perbaikan -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-tools me-2"></i>Perbaikan Terbaru
                                    </h6>
                                    <span class="badge bg-warning">{{ $recentPerbaikan->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    @if($recentPerbaikan->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-3">Kode Asset</th>
                                                        <th>Line Sebelum</th>
                                                        <th class="pe-3">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentPerbaikan as $perbaikan)
                                                    <tr>
                                                        <td class="ps-3 fw-medium">{{ $perbaikan->peralatan->kode_asset ?? '-' }}</td>
                                                        <td>{{ $perbaikan->line_sebelumnya }}</td>
                                                        <td class="pe-3">
                                                            @php
                                                                $badgeColor = match($perbaikan->status_perbaikan) {
                                                                    'Masuk Lab'        => 'secondary',
                                                                    'Dalam Perbaikan'  => 'warning',
                                                                    'Selesai'          => 'success',
                                                                    'Dikirim Eksternal'=> 'info',
                                                                    default            => 'secondary'
                                                                };
                                                            @endphp
                                                            <span class="badge bg-{{ $badgeColor }}">{{ $perbaikan->status_perbaikan }}</span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bi bi-tools fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada data perbaikan</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 12px; }
.table th { font-weight: 600; background-color: #f8f9fa !important; }
.progress-bar { font-size: 0.75rem; font-weight: 600; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const exportType          = document.getElementById('exportType');
    const exportFormatWrapper = document.getElementById('exportFormatWrapper');
    const exportFormat        = document.getElementById('exportFormat');
    const btnExport           = document.getElementById('btnExport');
    const btnFilter           = document.getElementById('btnFilter');

    // Sembunyikan tipe laporan jika PDF dipilih
    function toggleFormatWrapper() {
        const isPdf = exportType.value === 'pdf';
        exportFormatWrapper.style.display = isPdf ? 'none' : '';
    }
    toggleFormatWrapper();
    exportType.addEventListener('change', toggleFormatWrapper);

    // Tombol Terapkan Filter — reload halaman dengan parameter baru
    btnFilter.addEventListener('click', function () {
        const year  = document.getElementById('filterYear').value;
        const month = document.getElementById('filterMonth').value;
        const line  = document.getElementById('filterLine').value;

        const url = new URL('{{ route("laporan.index") }}');
        url.searchParams.set('year', year);
        url.searchParams.set('month', month);
        if (line) url.searchParams.set('line', line);

        window.location.href = url.toString();
    });

    // Tombol Export — kirim form ke laporan.export
    btnExport.addEventListener('click', function () {
        const year   = document.getElementById('filterYear').value;
        const month  = document.getElementById('filterMonth').value;
        const line   = document.getElementById('filterLine').value;
        const type   = exportType.value;
        const format = type === 'pdf' ? 'summary' : exportFormat.value;

        const originalText = btnExport.innerHTML;
        btnExport.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Memproses...';
        btnExport.disabled  = true;

        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route("laporan.export") }}';

        const params = { year, month, type, format, line };
        Object.entries(params).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = name;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            btnExport.innerHTML = originalText;
            btnExport.disabled  = false;
        }, 3000);
    });
});
</script>
@endsection