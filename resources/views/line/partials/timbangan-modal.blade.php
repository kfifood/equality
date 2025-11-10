<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-speedometer me-2"></i>Data Timbangan - {{ $line->nama_line }}
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

    <!-- Timbangan yang Sedang Digunakan di Line Ini -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="card-title mb-0">
                <i class="bi bi-check-circle me-2"></i>Timbangan yang Sedang Digunakan
                <span class="badge bg-light text-dark ms-2">{{ $timbanganDiLine->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($timbanganDiLine->count() > 0)
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
                            @foreach($timbanganDiLine as $timbangan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="bi bi-speedometer text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <strong>{{ $timbangan->kode_asset }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                          title="{{ $timbangan->merk_tipe_no_seri }}">
                                        {{ $timbangan->merk_tipe_no_seri }}
                                    </span>
                                </td>
                                <td>
                                    @if($timbangan->lokasi_asli == $line->nama_line)
                                        <span class="badge bg-primary">Lokasi Asli</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                        <br><small class="text-muted">Asli: {{ $timbangan->lokasi_asli }}</small>
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
                    <p class="text-muted mt-2 mb-0">Tidak ada timbangan yang sedang digunakan di line ini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Timbangan dengan Lokasi Asli di Line Ini -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="card-title mb-0">
                <i class="bi bi-house me-2"></i>Timbangan dengan Lokasi Asli di Line Ini
                <span class="badge bg-light text-dark ms-2">{{ $timbanganLokasiAsli->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($timbanganLokasiAsli->count() > 0)
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
                            @foreach($timbanganLokasiAsli as $timbangan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="bi bi-speedometer text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <strong>{{ $timbangan->kode_asset }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                          title="{{ $timbangan->merk_tipe_no_seri }}">
                                        {{ $timbangan->merk_tipe_no_seri }}
                                    </span>
                                </td>
                                <td>
                                    @if($timbangan->status_line)
                                        @if($timbangan->status_line == $line->nama_line)
                                            <span class="badge bg-success">Di Line Ini</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Dipinjam ke {{ $timbangan->status_line }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Lab</span>
                                    @endif
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    @php
                                        $statusLokasi = $timbangan->status_lokasi;
                                        $statusColor = match(true) {
                                            $timbangan->isDiLokasiAsli() => 'success',
                                            $timbangan->isDipinjam() => 'warning',
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
                    <p class="text-muted mt-2 mb-0">Tidak ada timbangan dengan lokasi asli di line ini</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4 class="text-success">{{ $timbanganDiLine->count() }}</h4>
                    <small class="text-muted">Sedang Digunakan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4 class="text-primary">{{ $timbanganLokasiAsli->count() }}</h4>
                    <small class="text-muted">Total Lokasi Asli</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    @php
                        $timbanganDiLab = $timbanganLokasiAsli->where('status_line', null)->where('kondisi_saat_ini', 'Baik')->count();
                    @endphp
                    <h4 class="text-info">{{ $timbanganDiLab }}</h4>
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