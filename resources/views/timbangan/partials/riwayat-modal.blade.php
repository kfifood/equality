<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-clock-history me-2"></i>Riwayat Timbangan - {{ $timbangan->kode_asset }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <!-- Info Timbangan -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Kode Asset</h6>
                    <p class="card-text fw-bold">{{ $timbangan->kode_asset }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Merk & Seri</h6>
                    <p class="card-text">{{ $timbangan->merk_tipe_no_seri }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Saat Ini -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Lokasi Saat Ini</h6>
                    <p class="card-text">
                        @if($timbangan->status_line)
                            <span class="badge bg-info">{{ $timbangan->status_line }}</span>
                        @else
                            <span class="badge bg-secondary">Lab</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Kondisi</h6>
                    <p class="card-text">
                        @php
                            $badgeColor = match($timbangan->kondisi_saat_ini) {
                                'Baik' => 'success',
                                'Rusak' => 'danger',
                                'Dalam Perbaikan' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">
                            {{ $timbangan->kondisi_saat_ini }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penggunaan - SIMPLE VERSION -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-arrow-right-circle me-2"></i>Riwayat Penggunaan
                <span class="badge bg-primary ms-2">{{ $timbangan->riwayatPenggunaan->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($timbangan->riwayatPenggunaan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Line Tujuan</th>
                                <th>PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timbangan->riwayatPenggunaan->sortByDesc('created_at') as $riwayat)
                            <tr>
                                <td>{{ $riwayat->tanggal_pemakaian ? \Carbon\Carbon::parse($riwayat->tanggal_pemakaian)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $riwayat->line_tujuan }}</td>
                                <td>{{ $riwayat->pic ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">Belum ada riwayat penggunaan</p>
            @endif
        </div>
    </div>

    <!-- Riwayat Perbaikan - SIMPLE VERSION -->
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-tools me-2"></i>Riwayat Perbaikan
                <span class="badge bg-primary ms-2">{{ $timbangan->riwayatPerbaikan->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($timbangan->riwayatPerbaikan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal Masuk</th>
                                <th>Line Sebelum</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timbangan->riwayatPerbaikan->sortByDesc('created_at') as $riwayat)
                            <tr>
                                <td>{{ $riwayat->tanggal_masuk_lab ? \Carbon\Carbon::parse($riwayat->tanggal_masuk_lab)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $riwayat->line_sebelumnya }}</td>
                                <td>
                                    @php
                                        $status = $riwayat->status_perbaikan ?? 'Masuk Lab';
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">Belum ada riwayat perbaikan</p>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>