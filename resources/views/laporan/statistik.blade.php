@extends('layouts.app')
@section('title', 'Statistik & Analytics')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="bi bi-graph-up me-2"></i>Statistik & Analytics
                    </h5>
                    <div>
                        <a href="{{ route('laporan.index') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="bi bi-table me-1"></i>Data Lengkap
                        </a>
                        <a href="{{ route('laporan.export') }}?type=excel&format=summary" class="btn btn-primary btn-sm">
                            <i class="bi bi-download me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- MTBF & Reliability -->
                    <div class="row mb-5">
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history fa-2x text-primary mb-3"></i>
                                    <h4 class="text-primary">{{ $mtbfData['total_perbaikan'] }}</h4>
                                    <p class="text-muted mb-0">Total Perbaikan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-speedometer fa-2x text-success mb-3"></i>
                                    <h4 class="text-success">{{ $mtbfData['avg_downtime'] }} hari</h4>
                                    <p class="text-muted mb-0">Rata-rata Downtime</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <i class="bi bi-shield-check fa-2x text-info mb-3"></i>
                                    <h4 class="text-info">{{ $mtbfData['reliability'] }}%</h4>
                                    <p class="text-muted mb-0">Reliability Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row 1 -->
                    <div class="row mb-4">
                        <!-- Distribusi Kondisi -->
                        <div class="col-xl-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-pie-chart me-2"></i>Distribusi Kondisi Timbangan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="kondisiChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Distribusi Line -->
                        <div class="col-xl-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-bar-chart me-2"></i>Distribusi per Line
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="lineChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row 2 -->
                    <div class="row mb-4">
                        <!-- Perbaikan 30 Hari -->
                        <div class="col-xl-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-activity me-2"></i>Perbaikan 30 Hari Terakhir
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="perbaikanChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Penggunaan Bulanan -->
                        <div class="col-xl-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-calendar-month me-2"></i>Penggunaan Tahunan {{ date('Y') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="penggunaanChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tables -->
                    <div class="row">
                        <!-- Top Line -->
                        <div class="col-xl-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-trophy me-2"></i>Top 5 Line
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="border-0 ps-4">Line</th>
                                                    <th class="border-0 text-end">Jumlah</th>
                                                    <th class="border-0 pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($distribusiLine->take(5) as $index => $line)
                                                <tr class="border-bottom">
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-diagram-3 text-primary me-2"></i>
                                                            <span class="text-dark">{{ $line->status_line }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-primary">{{ $line->total }}</strong>
                                                    </td>
                                                    <td class="pe-4 text-end">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">
    @php
        $totalLines = $distribusiLine->sum('total');
        $percentage = $totalLines > 0 ? round(($line->total / $totalLines) * 100, 1) : 0;
    @endphp
    {{ $percentage }}%
</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Kondisi -->
                        <div class="col-xl-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="card-title mb-0 text-primary">
                                        <i class="bi bi-clipboard-data me-2"></i>Ringkasan Kondisi
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="border-0 ps-4">Kondisi</th>
                                                    <th class="border-0 text-end">Jumlah</th>
                                                    <th class="border-0 pe-4 text-end">Persentase</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($distribusiKondisi as $kondisi)
                                                <tr class="border-bottom">
                                                    <td class="ps-4">
                                                        @php
                                                            $badgeColor = match($kondisi->kondisi_saat_ini) {
                                                                'Baik' => 'success',
                                                                'Rusak' => 'danger',
                                                                'Dalam Perbaikan' => 'warning',
                                                                default => 'secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25 rounded-pill px-3 py-1">
                                                            {{ $kondisi->kondisi_saat_ini }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="text-primary">{{ $kondisi->total }}</strong>
                                                    </td>
                                                    <td class="pe-4 text-end">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">
    @php
        $totalKondisi = $distribusiKondisi->sum('total');
        $percentage = $totalKondisi > 0 ? round(($kondisi->total / $totalKondisi) * 100, 1) : 0;
    @endphp
    {{ $percentage }}%
</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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
.card {
    border-radius: 12px;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart colors
    const colors = {
        primary: '#4361ee',
        success: '#4cc9f0',
        danger: '#f72585',
        warning: '#f8961e',
        info: '#4895ef',
        secondary: '#6c757d'
    };

    // 1. Distribusi Kondisi Chart (Doughnut)
    const kondisiCtx = document.getElementById('kondisiChart').getContext('2d');
    new Chart(kondisiCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($distribusiKondisi->pluck('kondisi_saat_ini')) !!},
            datasets: [{
                data: {!! json_encode($distribusiKondisi->pluck('total')) !!},
                backgroundColor: [
                    colors.success,
                    colors.warning,
                    colors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 2. Distribusi Line Chart (Bar)
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($distribusiLine->pluck('status_line')) !!},
            datasets: [{
                label: 'Jumlah Timbangan',
                data: {!! json_encode($distribusiLine->pluck('total')) !!},
                backgroundColor: colors.primary,
                borderColor: colors.primary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 3. Perbaikan 30 Hari Chart (Line)
    const perbaikanCtx = document.getElementById('perbaikanChart').getContext('2d');
    new Chart(perbaikanCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($perbaikanHarian->pluck('tanggal')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m');
            })) !!},
            datasets: [{
                label: 'Perbaikan per Hari',
                data: {!! json_encode($perbaikanHarian->pluck('total')) !!},
                backgroundColor: colors.primary + '20',
                borderColor: colors.primary,
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // 4. Penggunaan Bulanan Chart (Bar)
    const penggunaanCtx = document.getElementById('penggunaanChart').getContext('2d');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    // Prepare data for all months
    const monthlyData = Array(12).fill(0);
    @foreach($penggunaanBulanan as $usage)
        monthlyData[{{ $usage->bulan - 1 }}] = {{ $usage->total }};
    @endforeach

    new Chart(penggunaanCtx, {
        type: 'bar',
        data: {
            labels: monthNames,
            datasets: [{
                label: 'Penggunaan per Bulan',
                data: monthlyData,
                backgroundColor: colors.info,
                borderColor: colors.info,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection