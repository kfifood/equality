<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-speedometer me-2"></i>Data Peralatan - {{ $line->nama_line }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <!-- Info Line -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Kode Line</h6>
                    <p class="card-text fw-bold">{{ $line->kode_line }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Department</h6>
                    <p class="card-text">{{ $line->department }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Peralatan yang Sedang Digunakan di Line Ini -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="card-title mb-0">
                <i class="bi bi-check-circle me-2"></i>Peralatan yang Sedang Digunakan
                <span class="badge bg-light text-dark ms-2">{{ $peralatanDiLine->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($peralatanDiLine->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>Kode Asset</th>
                                <th>Merk & Seri</th>
                                <th>Lokasi Asli</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peralatanDiLine as $peralatan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="bi bi-speedometer text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <strong>{{ $peralatan->kode_asset }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                          title="{{ $peralatan->merk_tipe_lengkap }}">
                                        {{ $peralatan->merk_tipe_lengkap }}
                                    </span>
                                </td>
                                <td>
                                    @if($peralatan->lokasi_asli == $line->nama_line)
                                        <span class="badge bg-primary">Lokasi Asli</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                        <br><small class="text-muted">Asli: {{ $peralatan->lokasi_asli }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Digunakan
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-3">
                    <i class="bi bi-speedometer2 text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">Tidak ada peralatan yang sedang digunakan di line ini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Peralatan dengan Lokasi Asli di Line Ini -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="card-title mb-0">
                <i class="bi bi-house me-2"></i>Peralatan dengan Lokasi Asli di Line Ini
                <span class="badge bg-light text-dark ms-2">{{ $peralatanLokasiAsli->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($peralatanLokasiAsli->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Kode Asset</th>
                                <th>Merk & Seri</th>
                                <th>Lokasi Saat Ini</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peralatanLokasiAsli as $peralatan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="bi bi-speedometer text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <strong>{{ $peralatan->kode_asset }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                          title="{{ $peralatan->merk_tipe_lengkap }}">
                                        {{ $peralatan->merk_tipe_lengkap }}
                                    </span>
                                </td>
                                <td>
                                    @if($peralatan->status_line)
                                        @if($peralatan->status_line == $line->nama_line)
                                            <span class="badge bg-success">Di Line Ini</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Dipinjam ke {{ $peralatan->status_line }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Lab</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match($peralatan->kondisi_saat_ini) {
                                            'Baik' => 'success',
                                            'Rusak' => 'danger',
                                            'Dalam Perbaikan' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ $peralatan->kondisi_saat_ini }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusLokasi = $peralatan->status_lokasi;
                                        $statusColor = match(true) {
                                            $peralatan->isDiLokasiAsli() => 'success',
                                            $peralatan->isDipinjam() => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $statusLokasi }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-3">
                    <i class="bi bi-house text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">Tidak ada peralatan dengan lokasi asli di line ini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4 class="text-success">{{ $peralatanDiLine->count() }}</h4>
                    <small class="text-muted">Sedang Digunakan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4 class="text-primary">{{ $peralatanLokasiAsli->count() }}</h4>
                    <small class="text-muted">Total Lokasi Asli</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    @php
                        $peralatanDiLab = $peralatanLokasiAsli->where('status_line', null)->where('kondisi_saat_ini', 'Baik')->count();
                    @endphp
                    <h4 class="text-info">{{ $peralatanDiLab }}</h4>
                    <small class="text-muted">Siap Digunakan (Lab)</small>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>

<style>
.avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 0.7rem;
}
</style>