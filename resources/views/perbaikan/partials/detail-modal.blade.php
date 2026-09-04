{{--
    Partial: perbaikan/partials/detail-modal.blade.php
    Di-render via AJAX oleh PerbaikanController@detail
    Variabel: $laporan (LaporanKerusakan with peralatan, keluhanList, riwayatPenggunaan, riwayatPerbaikan.detailTindakan.masterTindakan)
--}}

@php
    $perbaikan = $laporan->riwayatPerbaikan->first();

    $statusLaporanColor = match($laporan->status) {
        'Menunggu' => 'warning',
        'Diproses' => 'info',
        'Selesai'  => 'success',
        default    => 'secondary',
    };
    $statusLaporanIcon = match($laporan->status) {
        'Menunggu' => 'clock',
        'Diproses' => 'gear',
        'Selesai'  => 'check-circle',
        default    => 'question-circle',
    };
@endphp

<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-eye me-2"></i>Detail Laporan &amp; Perbaikan
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    {{-- ── Info Alat ──────────────────────────────────────────────────────── --}}
    <div class="card border mb-3">
        <div class="card-header py-2" style="background:#f8f9fa;">
            <strong class="small text-muted">
                <i class="bi bi-speedometer me-1"></i>Informasi Alat
            </strong>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Kode Asset</div>
                    <div class="fw-bold text-primary fs-6">
                        {{ $laporan->peralatan->kode_asset }}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Merk / Tipe</div>
                    <div class="fw-semibold small">{{ $laporan->peralatan->merk_tipe_lengkap }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Kondisi Saat Ini</div>
                    @php
                        $kondisiColor = match($laporan->peralatan->kondisi_saat_ini) {
                            'Baik'            => 'success',
                            'Rusak'           => 'danger',
                            'Dalam Perbaikan' => 'warning',
                            default           => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $kondisiColor }}">
                        {{ $laporan->peralatan->kondisi_saat_ini }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Info Laporan Kerusakan ──────────────────────────────────────────── --}}
    <div class="card border mb-3">
        <div class="card-header py-2" style="background:#f8f9fa;">
            <strong class="small text-muted">
                <i class="bi bi-file-earmark-text me-1"></i>Laporan Kerusakan
            </strong>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Tanggal Laporan</div>
                    <div class="small fw-semibold">
                        {{ $laporan->tanggal_laporan->format('d/m/Y') }}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Line Asal</div>
                    <span class="badge bg-info">{{ $laporan->line_asal ?? '-' }}</span>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">PIC Pelapor</div>
                    <div class="small fw-semibold">{{ $laporan->pic_pelapor ?? '-' }}</div>
                </div>
                <div class="col-sm-3">
                    <div class="small text-muted mb-1">Status Laporan</div>
                    <span class="badge bg-{{ $statusLaporanColor }}">
                        <i class="bi bi-{{ $statusLaporanIcon }} me-1"></i>{{ $laporan->status }}
                    </span>
                </div>
            </div>

            {{-- Keluhan --}}
            <div class="mt-3 pt-3 border-top">
                <div class="small text-muted mb-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>Keluhan Dilaporkan
                </div>
                <div class="d-flex flex-wrap gap-1">
                    @forelse($laporan->keluhanList as $k)
                        <span class="badge bg-danger">{{ $k->nama_keluhan }}</span>
                    @empty
                        <span class="text-muted small">Tidak ada keluhan tercatat.</span>
                    @endforelse
                </div>
                @if($laporan->keterangan_tambahan)
                    <div class="small text-muted mt-2">
                        <i class="bi bi-chat-text me-1"></i>
                        {{ $laporan->keterangan_tambahan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Riwayat Perbaikan ───────────────────────────────────────────────── --}}
    <div class="card border mb-3">
        <div class="card-header py-2" style="background:#f8f9fa;">
            <strong class="small text-muted">
                <i class="bi bi-tools me-1"></i>Proses Perbaikan
            </strong>
        </div>
        <div class="card-body p-3">
            @if($perbaikan)
                <div class="row g-3 mb-3">
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Tanggal Masuk Lab</div>
                        <div class="small fw-semibold">
                            {{ $perbaikan->tanggal_masuk_lab?->format('d/m/Y') ?? '-' }}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Tanggal Selesai</div>
                        <div class="small fw-semibold">
                            {{ $perbaikan->tanggal_selesai_perbaikan?->format('d/m/Y') ?? '-' }}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Durasi Perbaikan</div>
                        <div class="small fw-semibold">
                            {{ $perbaikan->durasi_perbaikan !== null ? $perbaikan->durasi_perbaikan . ' hari' : '-' }}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small text-muted mb-1">Status Perbaikan</div>
                        <span class="badge bg-{{ $perbaikan->status_color }}">
                            <i class="bi bi-{{ $perbaikan->status_icon }} me-1"></i>
                            {{ $perbaikan->status_perbaikan }}
                        </span>
                    </div>
                </div>

                @if($perbaikan->line_tujuan)
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">Lokasi Tujuan Setelah Perbaikan</div>
                            <span class="badge bg-success">{{ $perbaikan->line_tujuan }}</span>
                        </div>
                        @if($perbaikan->catatan)
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">Catatan Proses</div>
                                <div class="small">{{ $perbaikan->catatan }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Tabel Tindakan --}}
                @if($perbaikan->detailTindakan->count() > 0)
                    <div class="border-top pt-3 mt-2">
                        <div class="small text-muted mb-2">
                            <i class="bi bi-list-check me-1"></i>Tindakan yang Dilakukan
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">Tindakan</th>
                                        <th class="small" width="110">Tanggal</th>
                                        <th class="small">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($perbaikan->detailTindakan as $dt)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-tools me-1"></i>
                                                {{ $dt->masterTindakan->nama_tindakan ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="small">
                                            {{ $dt->tanggal_tindakan
                                                ? \Carbon\Carbon::parse($dt->tanggal_tindakan)->format('d/m/Y')
                                                : '-' }}
                                        </td>
                                        <td class="small text-muted">{{ $dt->catatan ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-muted small border-top pt-3 mt-2">
                        <i class="bi bi-info-circle me-1"></i>Belum ada tindakan yang dicatat.
                    </div>
                @endif

            @else
                <div class="text-center text-muted py-3">
                    <i class="bi bi-hourglass fs-4 d-block mb-2"></i>
                    Belum ada proses perbaikan yang dicatat untuk laporan ini.
                </div>
            @endif
        </div>
    </div>

    {{-- ── Riwayat Penggunaan Terkait ──────────────────────────────────────── --}}
    @if($laporan->riwayatPenggunaan)
    <div class="card border">
        <div class="card-header py-2" style="background:#f8f9fa;">
            <strong class="small text-muted">
                <i class="bi bi-person-check me-1"></i>Penggunaan Terakhir Sebelum Kerusakan
            </strong>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Tanggal Pemakaian</div>
                    <div class="small fw-semibold">
                        {{ $laporan->riwayatPenggunaan->tanggal_pemakaian?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">Line Penggunaan</div>
                    <span class="badge bg-info">
                        {{ $laporan->riwayatPenggunaan->line_tujuan ?? '-' }}
                    </span>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted mb-1">PIC Penggunaan</div>
                    <div class="small fw-semibold">
                        {{ $laporan->riwayatPenggunaan->pic ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>{{-- end modal-body --}}

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="bi bi-x-circle me-1"></i>Tutup
    </button>
    @if(!$laporan->isSelesai())
        <button type="button" class="btn btn-primary"
            onclick="$('#dynamicModal').modal('hide'); setTimeout(() => showProsesModal({{ $laporan->id }}), 400);">
            <i class="bi bi-arrow-clockwise me-1"></i>Proses Perbaikan
        </button>
    @endif
</div>