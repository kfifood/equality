@extends('layouts.app')
@section('title', 'Penggunaan Alat')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-arrow-right-circle me-2"></i>Riwayat Penggunaan Alat
                        </h5>
                        <small class="text-muted">Termasuk timbangan yang baru selesai perbaikan</small>
                    </div>
                    <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                        onclick="showCreatePenggunaanModal()">
                        <i class="bi bi-plus-circle me-1"></i>Catat Penggunaan
                    </button>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="d-none" id="session-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="d-none" id="session-error">{{ session('error') }}</div>
                    @endif

                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('penggunaan.index') }}" method="GET" id="filterForm">
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
                                        <label class="form-label">Line Tujuan</label>
                                        <select name="line_tujuan" class="form-select">
                                            <option value="">Semua Line</option>
                                            @foreach($lineList as $line)
                                                <option value="{{ $line->nama_line }}"
                                                    {{ request('line_tujuan') == $line->nama_line ? 'selected' : '' }}>
                                                    {{ $line->nama_line }}
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
                                        <label class="form-label">Kondisi</label>
                                        <select name="kondisi" class="form-select">
                                            <option value="">Semua Kondisi</option>
                                            <option value="Baik" {{ request('kondisi') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                            <option value="Rusak" {{ request('kondisi') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                            <option value="Dalam Perbaikan" {{ request('kondisi') == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" title="Filter">
                                                <i class="bi bi-funnel"></i>
                                            </button>
                                            <a href="{{ route('penggunaan.index') }}" class="btn btn-secondary d-flex align-items-center justify-content-center" title="Reset">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="penggunaanTable">
                            <thead class="table-tmb" style="color:#4361EE;">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th>Merk & Tipe</th>
                                    <th>Line Tujuan</th>
                                    <th>PIC</th>
                                    <th>Tanggal Pemakaian</th>
                                    <th>Status</th>
                                    <th>Kondisi Alat</th>
                                    <th>Keterangan</th>
                                    <th width="130" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penggunaan as $index => $item)
                                @php
                                    $kondisi       = $item->timbangan->kondisi_saat_ini;
                                    $badgeColor    = match($kondisi) {
                                        'Baik'            => 'success',
                                        'Rusak'           => 'danger',
                                        'Dalam Perbaikan' => 'warning',
                                        default           => 'secondary'
                                    };
                                    $kondisiIcon   = match($kondisi) {
                                        'Baik'            => 'check-circle',
                                        'Rusak'           => 'exclamation-triangle',
                                        'Dalam Perbaikan' => 'tools',
                                        default           => 'question-circle'
                                    };
                                    $statusColor   = match($item->status_penggunaan) {
                                        'Masih Digunakan' => 'success',
                                        'Dikembalikan'    => 'warning',
                                        'Selesai'         => 'secondary',
                                        default           => 'secondary'
                                    };
                                    $statusIcon    = match($item->status_penggunaan) {
                                        'Masih Digunakan' => 'check-circle',
                                        'Dikembalikan'    => 'arrow-return-left',
                                        'Selesai'         => 'check',
                                        default           => 'question-circle'
                                    };
                                    $statusTooltip = match($item->status_penggunaan) {
                                        'Masih Digunakan' => 'Timbangan masih digunakan di ' . $item->line_tujuan,
                                        'Dikembalikan'    => 'Timbangan dikembalikan karena ' . strtolower($kondisi),
                                        'Selesai'         => $item->isSelesaiDipindahkan()
                                            ? 'Penggunaan selesai - timbangan dipindahkan ke ' . $item->timbangan->status_line
                                            : 'Penggunaan selesai - timbangan dalam kondisi baik',
                                        default => 'Status penggunaan'
                                    };

                                    $bisaLaporkan    = $item->timbangan->bisaDilaporkanRusak();
                                    $sudahDilaporkan = $item->sudahDilaporkanRusak();
                                    $adaLaporan      = $item->laporanKerusakan !== null;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $penggunaan->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-speedometer text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $item->timbangan->kode_asset }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width:200px;"
                                            title="{{ $item->timbangan->merk_tipe_no_seri }}">
                                            {{ $item->timbangan->merk_tipe_no_seri }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $item->line_tujuan }}</span>
                                    </td>
                                    <td>{{ $item->pic ?? '-' }}</td>
                                    <td>
                                        <i class="bi bi-calendar me-1 text-primary"></i>
                                        {{ \Carbon\Carbon::parse($item->tanggal_pemakaian)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }}"
                                            data-bs-toggle="tooltip" title="{{ $statusTooltip }}">
                                            <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $item->status_penggunaan }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $badgeColor }}">
                                            <i class="bi bi-{{ $kondisiIcon }} me-1"></i>{{ $kondisi }}
                                        </span>
                                    </td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>

                                    <td class="text-center">
                                        @if($sudahDilaporkan || $adaLaporan)
                                            <span class="badge bg-danger py-2 px-2"
                                                data-bs-toggle="tooltip"
                                                title="Sudah ada laporan kerusakan aktif untuk alat ini">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Dilaporkan
                                            </span>
                                        @elseif($bisaLaporkan)
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="showLaporkanRusakModal({{ $item->id }})"
                                                data-bs-toggle="tooltip"
                                                title="Laporkan timbangan ini rusak">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Laporkan Rusak
                                            </button>
                                        @elseif($kondisi === 'Dalam Perbaikan')
                                            <span class="badge bg-warning text-dark py-2 px-2"
                                                data-bs-toggle="tooltip"
                                                title="Alat sedang dalam proses perbaikan">
                                                <i class="bi bi-tools me-1"></i>Diperbaiki
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Pagination ───────────────────────────────────────── --}}
                    @if($penggunaan->hasPages())
                    @php
                        $cur   = $penggunaan->currentPage();
                        $last  = $penggunaan->lastPage();
                        $pages = collect();
                        for ($i = 1; $i <= min(2, $last); $i++) $pages->push($i);
                        for ($i = max(1, $cur - 2); $i <= min($last, $cur + 2); $i++) $pages->push($i);
                        for ($i = max(1, $last - 1); $i <= $last; $i++) $pages->push($i);
                        $pages = $pages->unique()->sort()->values();
                        $q     = request()->except('page');
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $penggunaan->firstItem() }}</strong>
                            hingga <strong>{{ $penggunaan->lastItem() }}</strong>
                            dari <strong>{{ $penggunaan->total() }}</strong> riwayat penggunaan
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                {{-- << First --}}
                                <li class="page-item {{ $cur == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $penggunaan->url(1) }}&{{ http_build_query($q) }}">&laquo;</a>
                                </li>
                                {{-- < Prev --}}
                                <li class="page-item {{ $cur == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $penggunaan->previousPageUrl() ?? '#' }}&{{ http_build_query($q) }}">&lsaquo;</a>
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
                                            <a class="page-link" href="{{ $penggunaan->url($page) }}&{{ http_build_query($q) }}">{{ $page }}</a>
                                        @endif
                                    </li>
                                    @php $prev = $page; @endphp
                                @endforeach

                                {{-- > Next --}}
                                <li class="page-item {{ !$penggunaan->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $penggunaan->nextPageUrl() ?? '#' }}&{{ http_build_query($q) }}">&rsaquo;</a>
                                </li>
                                {{-- >> Last --}}
                                <li class="page-item {{ !$penggunaan->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $penggunaan->url($last) }}&{{ http_build_query($q) }}">&raquo;</a>
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

<!-- ── Dynamic Modal Container ─── -->
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
    if (successMsg) Swal.fire({ icon: 'success', title: 'Berhasil', text: successMsg, timer: 3000, showConfirmButton: false });
    if (errorMsg)   Swal.fire({ icon: 'error',   title: 'Error',    text: errorMsg,   timer: 4000 });

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

function showCreatePenggunaanModal(timbanganId = null) {
    let url = '{{ route("penggunaan.create") }}';
    if (timbanganId) url = '{{ url("penggunaan/create") }}/' + timbanganId;

    Swal.fire({ title: 'Memuat form...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: url, type: 'GET',
        success: function (response) {
            Swal.close();
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form penggunaan.' });
            }
        },
        error: function (xhr) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal memuat form penggunaan.' });
        }
    });
}

function showLaporkanRusakModal(penggunaanId) {
    Swal.fire({ title: 'Memuat form...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '{{ url("laporan-kerusakan") }}/' + penggunaanId + '/create',
        type: 'GET',
        success: function (response) {
            Swal.close();
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
            } else {
                Swal.fire({ icon: 'warning', title: 'Tidak Bisa Dilaporkan', text: response.message });
            }
        },
        error: function (xhr) {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Gagal memuat form laporan.' });
        }
    });
}

$(document).on('submit', '#createPenggunaanForm', function (e) {
    e.preventDefault();
    const form          = $(this);
    const lokasiSaatIni = form.find('#timbanganSelect option:selected').data('lokasi');
    const lineTujuan    = form.find('[name="line_tujuan"]').val();

    if (lokasiSaatIni !== 'Lab' && lokasiSaatIni !== lineTujuan) {
        Swal.fire({
            title: 'Pindahkan Timbangan?',
            html: `Timbangan ini sedang digunakan di <strong>${lokasiSaatIni}</strong>.<br>Apakah Anda yakin ingin memindahkan ke <strong>${lineTujuan}</strong>?`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Pindahkan!', cancelButtonText: 'Batal'
        }).then(r => { if (r.isConfirmed) submitPenggunaanForm(form); });
    } else {
        submitPenggunaanForm(form);
    }
});

function submitPenggunaanForm(form) {
    const btn = form.find('button[type="submit"]');
    const ori = btn.html();
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

    $.ajax({
        url: form.attr('action'), type: 'POST', data: form.serialize(),
        success: function (res) {
            btn.prop('disabled', false).html(ori);
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 3000, showConfirmButton: false })
                    .then(() => { $('#dynamicModal').modal('hide'); location.reload(); });
            }
        },
        error: function (xhr) {
            btn.prop('disabled', false).html(ori);
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                for (const field in errors) {
                    const input = $('[name="' + field + '"]');
                    input.addClass('is-invalid').after('<div class="invalid-feedback">' + errors[field][0] + '</div>');
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
            }
        }
    });
}
</script>
@endpush