@extends('layouts.app')
@section('title', 'Data Kalibrasi')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-patch-check me-2"></i>Data Kalibrasi
                    </h5>
                    <div class="d-flex gap-2 align-items-center">
                        {{-- Tombol Cetak Sticker Batch (muncul jika ada yg dicentang) --}}
                        <button class="btn btn-sm btn-outline-secondary" id="btnStickerBatch"
                            onclick="cetakStickerBatch()" disabled style="display:none!important;">
                            <i class="bi bi-printer me-1"></i>Cetak Sticker
                            (<span id="jumlahDipilih">0</span>)
                        </button>
                        <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                            onclick="showCreateModal()">
                            <i class="bi bi-plus-circle me-1"></i>Tambah
                        </button>
                    </div>
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
                            <form action="{{ route('kalibrasi.index') }}" method="GET" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Timbangan</label>
                                        <select name="timbangan_id" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Timbangan</option>
                                            @foreach($timbanganList as $t)
                                                <option value="{{ $t->id }}"
                                                    {{ request('timbangan_id') == $t->id ? 'selected' : '' }}>
                                                    {{ $t->kode_asset }} — {{ $t->merk_tipe_no_seri }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Hasil</label>
                                        <select name="hasil" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Hasil</option>
                                            <option value="Lulus" {{ request('hasil') == 'Lulus' ? 'selected' : '' }}>Lulus</option>
                                            <option value="Tidak Lulus" {{ request('hasil') == 'Tidak Lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Dept / Bagian</label>
                                        <select name="dept_bagian" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Dept</option>
                                            @foreach($deptList as $dept)
                                                <option value="{{ $dept }}"
                                                    {{ request('dept_bagian') == $dept ? 'selected' : '' }}>
                                                    {{ $dept }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Pencarian</label>
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Cari kode asset, certificate, pelaksana..."
                                                value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            @if(request()->anyFilled(['timbangan_id', 'hasil', 'dept_bagian', 'search']))
                                                <a href="{{ route('kalibrasi.index') }}" class="btn btn-outline-danger">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead style="color:#4361EE;">
                                <tr>
                                    {{-- Kolom checkbox --}}
                                    <th width="36" class="text-center">
                                        <input type="checkbox" id="checkAll" class="form-check-input"
                                            title="Pilih Semua" onchange="toggleCheckAll(this)">
                                    </th>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th class="d-none d-md-table-cell">Merk & Seri</th>
                                    <th>Tgl. Pelaksanaan</th>
                                    <th class="d-none d-md-table-cell">Dept / Bagian</th>
                                    <th class="d-none d-lg-table-cell">Certificate No.</th>
                                    <th class="d-none d-lg-table-cell">Beda Maksimum</th>
                                    <th class="d-none d-md-table-cell">Pelaksana</th>
                                    <th>Hasil</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kalibrasi as $index => $item)
                                <tr>
                                    {{-- Checkbox --}}
                                    <td class="text-center align-middle">
                                        <input type="checkbox" class="form-check-input sticker-check"
                                            value="{{ $item->id }}" onchange="updateBatchCount()">
                                    </td>

                                    <td class="text-center align-middle">{{ $kalibrasi->firstItem() + $index }}</td>

                                    {{-- Kode Asset --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-patch-check text-white" style="font-size:0.85rem;"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $item->timbangan->kode_asset ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Merk & Seri --}}
                                    <td class="d-none d-md-table-cell">
                                        <span class="text-truncate d-inline-block" style="max-width:180px;"
                                            title="{{ $item->timbangan->merk_tipe_no_seri ?? '-' }}">
                                            {{ $item->timbangan->merk_tipe_no_seri ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Tanggal Pelaksanaan --}}
                                    <td>
                                        {{ $item->tanggal_pelaksanaan
                                            ? $item->tanggal_pelaksanaan->format('d/m/Y')
                                            : '-' }}
                                    </td>

                                    {{-- Dept / Bagian --}}
                                    <td class="d-none d-md-table-cell">
                                        @if($item->dept_bagian)
                                            <span class="badge bg-info text-dark">
                                                <i class="bi bi-building me-1"></i>{{ $item->dept_bagian }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- Certificate No --}}
                                    <td class="d-none d-lg-table-cell">
                                        @if($item->certificate_number)
                                            <span class="font-monospace small">
                                                <i class="bi bi-file-earmark-text text-primary me-1"></i>{{ $item->certificate_number }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- Beda Maksimum --}}
                                    <td class="d-none d-lg-table-cell">
                                        @if($item->beda_maksimum)
                                            <span class="small">{{ $item->beda_maksimum }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- Pelaksana --}}
                                    <td class="d-none d-md-table-cell">
                                        {{ $item->pelaksana ?? '-' }}
                                    </td>

                                    {{-- Hasil --}}
                                    <td>
                                        @if($item->hasil)
                                            @php
                                                $color = $item->hasil === 'Lulus' ? 'success' : 'danger';
                                                $icon  = $item->hasil === 'Lulus' ? 'check-circle' : 'x-circle';
                                            @endphp
                                            <span class="badge bg-{{ $color }} rounded-pill">
                                                <i class="bi bi-{{ $icon }} me-1"></i>{{ $item->hasil }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">
                                                <i class="bi bi-dash-circle me-1"></i>Belum diisi
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Tombol Sticker --}}
                                            <button type="button" class="btn btn-info" title="Cetak Sticker"
                                                onclick="cetakStickerSatu({{ $item->id }})"
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cetak Sticker">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            {{-- Edit --}}
                                            <button type="button" class="btn btn-warning" title="Edit"
                                                onclick="showEditModal({{ $item->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            {{-- Hapus --}}
                                            <button type="button" class="btn btn-danger" title="Hapus"
                                                onclick="deleteKalibrasi({{ $item->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada data kalibrasi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $kalibrasi->firstItem() ?? 0 }} hingga {{ $kalibrasi->lastItem() ?? 0 }}
                            dari {{ $kalibrasi->total() ?? 0 }} data kalibrasi
                        </div>

                        @if($kalibrasi->hasPages())
                        <nav>
                            <ul class="pagination mb-0">
                                @if($kalibrasi->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $kalibrasi->previousPageUrl() }}" rel="prev">&laquo;</a>
                                    </li>
                                @endif

                                @php
                                    $current = $kalibrasi->currentPage();
                                    $last    = $kalibrasi->lastPage();
                                    $start   = max(1, $current - 2);
                                    $end     = min($last, $current + 2);
                                    if ($end - $start < 4) {
                                        $start = $start == 1 ? $start : max(1, $end - 4);
                                        $end   = min($last, $start + 4);
                                    }
                                @endphp

                                @if($start > 1)
                                    <li class="page-item"><a class="page-link" href="{{ $kalibrasi->url(1) }}">1</a></li>
                                    @if($start > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                @endif

                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        @if($i == $current)
                                            <span class="page-link">{{ $i }}</span>
                                        @else
                                            <a class="page-link" href="{{ $kalibrasi->url($i) }}">{{ $i }}</a>
                                        @endif
                                    </li>
                                @endfor

                                @if($end < $last)
                                    @if($end < $last - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                    <li class="page-item"><a class="page-link" href="{{ $kalibrasi->url($last) }}">{{ $last }}</a></li>
                                @endif

                                @if($kalibrasi->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $kalibrasi->nextPageUrl() }}" rel="next">&raquo;</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                @endif
                            </ul>
                        </nav>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Modal Container -->
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="dynamicModalContent"></div>
    </div>
</div>

{{-- Form tersembunyi untuk submit batch sticker --}}
<form id="stickerBatchForm"
      action="{{ route('kalibrasi.sticker.batch') }}"
      method="POST"
      target="_blank">
    @csrf
    <div id="batchIdsContainer"></div>
</form>

<style>
.avatar-sm {
    width: 34px;
    height: 34px;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.card { border: none; border-radius: 12px; }
.table th { font-weight: 600; background-color: #f8f9fa !important; }
.badge { font-size: 0.75em; }
.pagination { margin-bottom: 0; flex-wrap: wrap; }
.page-link { color: #4361EE; border: 1px solid #dee2e6; padding: 0.5rem 0.75rem; font-size: 0.875rem; min-width: 42px; text-align: center; }
.page-link:hover { color: #4361EE; background-color: #e9ecef; }
.page-item.active .page-link { background-color: #4361EE; border-color: #4361EE; color: white; }
.page-item.disabled .page-link { color: #6c757d; background-color: #fff; }

/* Baris yang dicentang jadi sedikit berwarna */
tr.row-selected { background-color: rgba(67, 97, 238, 0.06) !important; }

@media (max-width: 768px) {
    .d-flex.justify-content-between { flex-direction: column; gap: 1rem; }
    .d-flex.justify-content-between > div:first-child { text-align: center; }
}
</style>

<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // Session alerts
    const successMsg = $('#session-success').text();
    const errorMsg   = $('#session-error').text();
    if (successMsg) Swal.fire({ icon: 'success', title: 'Berhasil', text: successMsg, timer: 3000, showConfirmButton: false });
    if (errorMsg)   Swal.fire({ icon: 'error',   title: 'Error',    text: errorMsg,   timer: 4000 });

    // Auto-search delay
    let searchTimer;
    $('input[name="search"]').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => $('#filterForm').submit(), 800);
    });
});

// ── Pilih semua / uncheck all ──────────────────────────────────────────────
function toggleCheckAll(master) {
    document.querySelectorAll('.sticker-check').forEach(cb => {
        cb.checked = master.checked;
        cb.closest('tr').classList.toggle('row-selected', master.checked);
    });
    updateBatchCount();
}

// ── Update jumlah & tampilkan/sembunyikan tombol batch ────────────────────
function updateBatchCount() {
    const checked = document.querySelectorAll('.sticker-check:checked');
    const btn     = document.getElementById('btnStickerBatch');
    const label   = document.getElementById('jumlahDipilih');

    label.textContent = checked.length;

    if (checked.length > 0) {
        btn.style.removeProperty('display');
        btn.disabled = false;
    } else {
        btn.style.setProperty('display', 'none', 'important');
        btn.disabled = true;
    }

    // Highlight baris yang sedang dicentang
    document.querySelectorAll('.sticker-check').forEach(cb => {
        cb.closest('tr').classList.toggle('row-selected', cb.checked);
    });

    // Sync master checkbox
    const all    = document.querySelectorAll('.sticker-check');
    const master = document.getElementById('checkAll');
    if (master) {
        master.checked       = checked.length === all.length && all.length > 0;
        master.indeterminate = checked.length > 0 && checked.length < all.length;
    }
}

// ── Cetak satu sticker — buka tab baru ────────────────────────────────────
function cetakStickerSatu(id) {
    window.open('{{ url("kalibrasi") }}/' + id + '/sticker', '_blank');
}

// ── Cetak batch via POST form tersembunyi ─────────────────────────────────
function cetakStickerBatch() {
    const checked = document.querySelectorAll('.sticker-check:checked');
    if (checked.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Belum ada yang dipilih', text: 'Centang minimal satu data kalibrasi terlebih dahulu.' });
        return;
    }

    const container = document.getElementById('batchIdsContainer');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    document.getElementById('stickerBatchForm').submit();
}

// ── Modal: Tambah ──────────────────────────────────────────────────────────
function showCreateModal() {
    $.ajax({
        url: '{{ route("kalibrasi.create") }}',
        type: 'GET',
        success: function (response) {
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
                initFormValidation();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form tambah data' });
            }
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form tambah data' });
        }
    });
}

// ── Modal: Edit ────────────────────────────────────────────────────────────
function showEditModal(id) {
    $.ajax({
        url: '{{ url("kalibrasi") }}/' + id + '/edit',
        type: 'GET',
        success: function (response) {
            if (response.success) {
                $('#dynamicModalContent').html(response.html);
                $('#dynamicModal').modal('show');
                initFormValidation();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form edit' });
            }
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat form edit' });
        }
    });
}

// ── Hapus ──────────────────────────────────────────────────────────────────
function deleteKalibrasi(id) {
    Swal.fire({
        title: 'Hapus data kalibrasi?',
        text: 'Data yang dihapus tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '{{ url("kalibrasi") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Gagal menghapus data' });
                    }
                },
                error: function (xhr) {
                    Swal.close();
                    const msg = xhr.responseJSON?.message || 'Gagal menghapus data';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        }
    });
}

// ── Validasi form ──────────────────────────────────────────────────────────
function initFormValidation() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

$(document).on('submit', '#createForm, #editForm', function (e) {
    e.preventDefault();

    const form        = $(this);
    const url         = form.attr('action');
    const method      = form.attr('method');
    const formData    = form.serialize();
    const submitBtn   = form.find('button[type="submit"]');
    const originalTxt = submitBtn.html();

    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

    $.ajax({
        url: url,
        type: method,
        data: formData,
        success: function (response) {
            submitBtn.prop('disabled', false).html(originalTxt);
            if (response.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => { $('#dynamicModal').modal('hide'); location.reload(); });
            }
        },
        error: function (xhr) {
            submitBtn.prop('disabled', false).html(originalTxt);
            if (xhr.status === 422) {
                clearValidationErrors();
                const errors = xhr.responseJSON.errors;
                for (const field in errors) {
                    const input = $('[name="' + field + '"]');
                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback">' + errors[field][0] + '</div>');
                }
                $('.is-invalid').first().focus();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan saat menyimpan data' });
            }
        }
    });
});

$('#dynamicModal').on('hidden.bs.modal', function () {
    $('#dynamicModalContent').html('');
});
</script>
@endsection