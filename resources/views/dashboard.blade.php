@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="container-fluid py-4">
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card welcome-card gradient-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="card-title mb-2">Selamat Datang, {{ Auth::user()->full_name ?? 'User' }}! 👋</h3>
                            <p class="card-text mb-2">K-LAB - Sistem Pelacakan Timbangan Digital</p>
                            <small>
                                @if(Auth::user()->last_login_at && Auth::user()->last_login_at instanceof \Carbon\Carbon)
                                Terakhir login: {{ Auth::user()->last_login_at->format('d M Y H:i') }}
                                @else
                                Kamu login pertama kali
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="bi bi-speedometer welcome-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Timbangan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Total Timbangan</h6>
                            <h2 class="mb-0">{{ $stats['total_timbangan'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['timbangan_baik'] ?? 0 }} dalam kondisi baik</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-speedometer fa-2x text-primary opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timbangan Baik -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Timbangan Baik</h6>
                            <h2 class="mb-0">{{ $stats['timbangan_baik'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['total_timbangan'] > 0 ? round(($stats['timbangan_baik'] / $stats['total_timbangan']) * 100, 1) : 0 }}% dari total</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-success opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dalam Perbaikan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Dalam Perbaikan</h6>
                            <h2 class="mb-0">{{ $stats['timbangan_perbaikan'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['perbaikan_aktif'] ?? 0 }} proses perbaikan aktif</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tools fa-2x text-warning opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penggunaan Bulan Ini -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Penggunaan</h6>
                            <h2 class="mb-0">{{ $stats['penggunaan_bulan_ini'] ?? 0 }}</h2>
                            <small class="text-muted">{{ $stats['total_line'] ?? 0 }} line aktif</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-arrow-right-circle fa-2x text-info opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row">
        <!-- Recent Timbangan -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-speedometer me-2 text-primary"></i>Timbangan Terbaru
                    </h5>
                    <a href="{{ route('timbangan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if($recentTimbangan && $recentTimbangan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Kode Asset</th>
                                    <th>Merk & Seri</th>
                                    <th>Line</th>
                                    <th>Kondisi</th>
                                    <th class="pe-3">Tanggal Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTimbangan as $timbangan)
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $timbangan->kode_asset ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($timbangan->merk_tipe_no_seri, 20) ?? 'N/A' }}</td>
                                    <td>
                                        @if($timbangan->status_line)
                                            <span class="badge bg-info">{{ $timbangan->status_line }}</span>
                                        @else
                                            <span class="badge bg-secondary">Lab</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $kondisi = $timbangan->kondisi_saat_ini ?? 'Baik';
                                            $badgeColor = match($kondisi) {
                                                'Baik' => 'success',
                                                'Rusak' => 'danger',
                                                'Dalam Perbaikan' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            {{ $kondisi }}
                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        {{ $timbangan->updated_at ? $timbangan->updated_at->format('d/m/Y') : 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-speedometer fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada data timbangan</p>
                        <a href="{{ route('timbangan.create') }}" class="btn btn-primary btn-sm">Tambah Timbangan</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ganti section Quick Actions dengan ini: -->

<!-- Kondisi Timbangan & Alert -->
<div class="col-lg-4 mb-4">
    <!-- Ringkasan Kondisi -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-pie-chart me-2 text-info"></i>Ringkasan Kondisi
            </h5>
        </div>
        <div class="card-body">
            <!-- Chart Sederhana -->
            <div class="text-center mb-3">
                <div class="position-relative d-inline-block">
                    <canvas id="kondisiChart" width="120" height="120"></canvas>
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <span class="fw-bold fs-4">{{ $stats['persentase_baik'] }}%</span>
                        <br>
                        <small class="text-muted">Baik</small>
                    </div>
                </div>
            </div>
            
            <div class="row text-center">
                <div class="col-4">
                    <div class="border-end">
                        <div class="fw-bold text-success">{{ $stats['timbangan_baik'] }}</div>
                        <small class="text-muted">Baik</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-end">
                        <div class="fw-bold text-warning">{{ $stats['timbangan_perbaikan'] }}</div>
                        <small class="text-muted">Perbaikan</small>
                    </div>
                </div>
                <div class="col-4">
                    <div>
                        <div class="fw-bold text-danger">{{ $stats['timbangan_rusak'] }}</div>
                        <small class="text-muted">Rusak</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert & Notifikasi -->
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-bell me-2 text-warning"></i>Perhatian
            </h5>
        </div>
        <div class="card-body">
            @if($timbanganPerhatian->count() > 0)
                <div class="alert alert-warning mb-3">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <div>
                            <strong>{{ $timbanganPerhatian->count() }} timbangan</strong> perlu perhatian
                        </div>
                    </div>
                </div>

                @foreach($timbanganPerhatian as $timbangan)
                <div class="alert alert-light border mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="d-block">{{ $timbangan->kode_asset }}</strong>
                            <small class="text-muted">
                                {{ $timbangan->kondisi_saat_ini }}
                                @if($timbangan->status_line)
                                    • {{ $timbangan->status_line }}
                                @else
                                    • Lab
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('perbaikan.create.withId', $timbangan->id) }}" 
                           class="btn btn-sm btn-outline-warning" 
                           onclick="showCreatePerbaikanModal({{ $timbangan->id }})">
                            <i class="bi bi-tools"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success fa-2x mb-2"></i>
                    <p class="text-muted mb-0">Semua timbangan dalam kondisi baik</p>
                </div>
            @endif

            @if($perbaikanLama > 0)
                <div class="alert alert-danger mt-3">
                    <div class="d-flex">
                        <i class="bi bi-clock-history me-2"></i>
                        <div>
                            <strong>{{ $perbaikanLama }} perbaikan</strong> sudah lebih dari 7 hari
                            <br>
                            <small>Perlu follow up</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
    </div>

    <!-- Second Row: Perbaikan dan Penggunaan -->
    <div class="row">
        <!-- Recent Perbaikan -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-tools me-2 text-warning"></i>Perbaikan Terbaru
                    </h5>
                    <a href="{{ route('perbaikan.index') }}" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if($recentPerbaikan && $recentPerbaikan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Kode Asset</th>
                                    <th>Line</th>
                                    <th>Status</th>
                                    <th class="pe-3">Tanggal Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPerbaikan as $perbaikan)
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $perbaikan->timbangan->kode_asset ?? 'N/A' }}</td>
                                    <td>{{ $perbaikan->line_sebelumnya ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $status = $perbaikan->status_perbaikan ?? 'Masuk Lab';
                                            $badgeColor = match($status) {
                                                'Masuk Lab' => 'secondary',
                                                'Dalam Perbaikan' => 'warning',
                                                'Selesai' => 'success',
                                                'Dikirim Eksternal' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="pe-3">
                                        {{ $perbaikan->tanggal_masuk_lab ? \Carbon\Carbon::parse($perbaikan->tanggal_masuk_lab)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-tools fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada data perbaikan</p>
                        <a href="{{ route('perbaikan.create') }}" class="btn btn-warning btn-sm">Catat Perbaikan</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Penggunaan -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-arrow-right-circle me-2 text-success"></i>Penggunaan Terbaru
                    </h5>
                    <a href="{{ route('penggunaan.index') }}" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if($recentPenggunaan && $recentPenggunaan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Kode Asset</th>
                                    <th>Line Tujuan</th>
                                    <th>PIC</th>
                                    <th class="pe-3">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPenggunaan as $penggunaan)
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $penggunaan->timbangan->kode_asset ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $penggunaan->line_tujuan ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ $penggunaan->pic ?? 'N/A' }}</td>
                                    <td class="pe-3">
                                        {{ $penggunaan->tanggal_pemakaian ? \Carbon\Carbon::parse($penggunaan->tanggal_pemakaian)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-arrow-right-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada data penggunaan</p>
                        <a href="{{ route('penggunaan.create') }}" class="btn btn-success btn-sm">Catat Penggunaan</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.gradient-card {
    background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
    color: white;
    border: none;
    border-radius: 16px;
}

.stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.welcome-icon {
    font-size: 4rem;
    opacity: 0.3;
}

.avatar-sm {
    width: 30px;
    height: 30px;
}

.badge-sm {
    font-size: 0.7em;
    padding: 0.25em 0.5em;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    border-color: #f8f9fa;
}

.btn-lg {
    padding: 1rem 1.5rem;
    border-radius: 12px;
}

/* Loading state */
.loading {
    opacity: 0.7;
    pointer-events: none;
}
</style>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Pie Chart untuk Kondisi Timbangan
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('kondisiChart').getContext('2d');
    const kondisiChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Baik', 'Dalam Perbaikan', 'Rusak'],
            datasets: [{
                data: [
                    {{ $stats['timbangan_baik'] }},
                    {{ $stats['timbangan_perbaikan'] }},
                    {{ $stats['timbangan_rusak'] }}
                ],
                backgroundColor: [
                    '#28a745', // Hijau untuk Baik
                    '#ffc107', // Kuning untuk Perbaikan  
                    '#dc3545'  // Merah untuk Rusak
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            cutout: '65%',
            responsive: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = {{ $stats['total_timbangan'] }};
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});

// Function untuk modal perbaikan (jika belum ada)
function showCreatePerbaikanModal(timbanganId = null) {
    let url = '{{ route("perbaikan.create") }}';
    if (timbanganId) {
        url = '{{ url("perbaikan/create") }}/' + timbanganId;
    }

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
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat form perbaikan'
            });
        }
    });
}

// Auto-refresh dashboard setiap 5 menit
setTimeout(function() {
    window.location.reload();
}, 300000);
</script>

@endsection