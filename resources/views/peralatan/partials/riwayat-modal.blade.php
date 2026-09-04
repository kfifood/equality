<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-clock-history me-2"></i>Riwayat Peralatan - {{ $peralatan->kode_asset }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <!-- Info Peralatan -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Lokasi Asli</h6>
                    <p class="card-text">
                        <span class="badge bg-primary">{{ $peralatan->lokasi_asli ?? 'Lab' }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Lokasi Saat Ini</h6>
                    <p class="card-text">
                        @if($peralatan->status_line)
                            <span class="badge bg-info">{{ $peralatan->status_line }}</span>
                        @else
                            <span class="badge bg-secondary">Lab</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Status Lokasi</h6>
                    <p class="card-text">
                        @php
                            $statusLokasi = $peralatan->status_lokasi;
                            $badgeColor = match(true) {
                                $peralatan->isDiLokasiAsli() => 'success',
                                $peralatan->isDipinjam() => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ $statusLokasi }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Kategori</h6>
                    <p class="card-text">{{ $peralatan->kategoriAlat->nama_kategori ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Merk & Seri</h6>
                    <p class="card-text">{{ $peralatan->merk_tipe_lengkap }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">Kondisi</h6>
                    <p class="card-text">
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
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($peralatan->spesifikasi))
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Spesifikasi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($peralatan->spesifikasi as $label => $value)
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">{{ $label }}</small>
                        <strong>{{ $value }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Riwayat Penggunaan -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-arrow-right-circle me-2"></i>Riwayat Penggunaan
                <span class="badge bg-primary ms-2">{{ $peralatan->riwayatPenggunaan->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($peralatan->riwayatPenggunaan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Line Tujuan</th>
                                <th>PIC</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peralatan->riwayatPenggunaan->sortByDesc('created_at') as $riwayat)
                            <tr>
                                <td>{{ $riwayat->tanggal_pemakaian ? \Carbon\Carbon::parse($riwayat->tanggal_pemakaian)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $riwayat->line_tujuan }}</td>
                                <td>{{ $riwayat->pic ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusColor = match($riwayat->status_penggunaan) {
                                            'Masih Digunakan' => 'success',
                                            'Dikembalikan' => 'warning',
                                            'Selesai' => 'secondary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $riwayat->status_penggunaan }}
                                    </span>
                                </td>
                                <td>{{ $riwayat->keterangan ?? '-' }}</td>
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

    <!-- Riwayat Perbaikan -->
    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="bi bi-tools me-2"></i>Riwayat Perbaikan
                <span class="badge bg-primary ms-2">{{ $peralatan->riwayatPerbaikan->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if($peralatan->riwayatPerbaikan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal Masuk</th>
                                <th>Line Sebelum</th>
                                <th>Status</th>
                                <th>Keluhan</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peralatan->riwayatPerbaikan->sortByDesc('created_at') as $riwayat)
                            <tr>
                                <td>{{ $riwayat->tanggal_masuk_lab ? \Carbon\Carbon::parse($riwayat->tanggal_masuk_lab)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $riwayat->line_sebelumnya }}</td>
                                <td>
                                    @php
                                        $status = $riwayat->status_perbaikan ?? 'Masuk Lab';
                                        $badgeColor = match($status) {
                                            'Masuk Lab' => 'secondary',
                                            'Perbaikan Internal' => 'primary',
                                            'Dikirim Eksternal' => 'info',
                                            'Menunggu Penanganan' => 'warning',
                                            'Selesai' => 'success',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                          title="{{ $riwayat->keluhan_ringkas }}">
                                        {{ $riwayat->keluhan_ringkas }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                          title="{{ $riwayat->tindakan_ringkas ?? '-' }}">
                                        {{ $riwayat->tindakan_ringkas ?? '-' }}
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