@extends('layouts.app')
@section('title', 'Data Kalibrasi')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">

                {{-- ── Card Header ── --}}
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
                    style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-patch-check me-2"></i>Data Kalibrasi
                    </h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        {{-- Cetak Sticker Batch (muncul jika ada yg dicentang) --}}
                        <button class="btn btn-sm btn-outline-secondary" id="btnStickerBatch"
                            onclick="cetakStickerBatch()" disabled style="display:none!important;">
                            <i class="bi bi-printer me-1"></i>Cetak Sticker
                            (<span id="jumlahDipilih">0</span>)
                        </button>

                        @unless(auth()->user()->isGuest())
                        {{-- Import Excel --}}
                        <button class="btn btn-sm btn-outline-success" onclick="showImportModal()"
                            title="Import dari file Excel">
                            <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
                        </button>

                        {{-- Input Massal --}}
                        <button class="btn btn-sm btn-outline-primary" onclick="showBulkModal()"
                            title="Input banyak data kalibrasi sekaligus">
                            <i class="bi bi-table me-1"></i>Input Massal
                        </button>

                        {{-- Tambah single --}}
                        <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                            onclick="showCreateModal()">
                            <i class="bi bi-plus-circle me-1"></i>Tambah
                        </button>
                        @endunless
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="d-none" id="session-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="d-none" id="session-error">{{ session('error') }}</div>
                    @endif

                    {{-- ── Filter ── --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('kalibrasi.index') }}" method="GET" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Peralatan</label>
                                        <select name="peralatan_id" class="form-select"
                                            onchange="this.form.submit()">
                                            <option value="">Semua Peralatan</option>
                                            @foreach($peralatanList as $p)
                                                <option value="{{ $p->id }}"
                                                    {{ request('peralatan_id') == $p->id ? 'selected' : '' }}>
                                                    {{ $p->kode_asset }} — {{ $p->merk_tipe_lengkap }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Hasil</label>
                                        <select name="hasil" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Hasil</option>
                                            <option value="Lulus"       {{ request('hasil') == 'Lulus'       ? 'selected' : '' }}>Lulus</option>
                                            <option value="Tidak Lulus" {{ request('hasil') == 'Tidak Lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Dept / Bagian</label>
                                        <select name="dept_bagian" class="form-select" onchange="this.form.submit()">
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
                                            <input type="text" name="search" class="form-control" id="searchInput"
                                                placeholder="Cari kode asset, certificate, pelaksana..."
                                                value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            @if(request()->anyFilled(['peralatan_id', 'hasil', 'dept_bagian', 'search']))
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

                    {{-- ── Tabel ── --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead style="color:#4361EE;">
                                <tr>
                                    <th width="36" class="text-center">
                                        <input type="checkbox" id="checkAll" class="form-check-input"
                                            title="Pilih Semua" onchange="toggleCheckAll(this)">
                                    </th>
                                    <th width="50">No</th>
                                    <th>Kode Asset</th>
                                    <th class="d-none d-md-table-cell">Merk &amp; Seri</th>
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
                                    <td class="text-center align-middle">
                                        <input type="checkbox" class="form-check-input sticker-check"
                                            value="{{ $item->id }}" onchange="updateBatchCount()">
                                    </td>

                                    <td class="text-center align-middle">{{ $kalibrasi->firstItem() + $index }}</td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-patch-check text-white" style="font-size:0.85rem;"></i>
                                            </div>
                                            <strong>{{ $item->peralatan->kode_asset ?? '-' }}</strong>
                                        </div>
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        <span class="text-truncate d-inline-block" style="max-width:180px;"
                                            title="{{ $item->peralatan->merk_tipe_lengkap ?? '-' }}">
                                            {{ $item->peralatan->merk_tipe_lengkap ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $item->tanggal_pelaksanaan ? $item->tanggal_pelaksanaan->format('d/m/Y') : '-' }}
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        @if($item->dept_bagian)
                                            <span class="badge bg-info text-dark">
                                                <i class="bi bi-building me-1"></i>{{ $item->dept_bagian }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="d-none d-lg-table-cell">
                                        @if($item->certificate_number)
                                            <span class="font-monospace small">
                                                <i class="bi bi-file-earmark-text text-primary me-1"></i>{{ $item->certificate_number }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="d-none d-lg-table-cell">
                                        <span class="small">{{ $item->beda_maksimum ?? '-' }}</span>
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        {{ $item->pelaksana ?? '-' }}
                                    </td>

                                    <td>
                                        @php
                                            $hasilColor = $item->hasil === 'Lulus' ? 'success' : ($item->hasil === 'Tidak Lulus' ? 'danger' : 'secondary');
                                            $hasilIcon  = $item->hasil === 'Lulus' ? 'check-circle' : ($item->hasil === 'Tidak Lulus' ? 'x-circle' : 'dash-circle');
                                            $hasilLabel = $item->hasil ?? 'Belum diisi';
                                        @endphp
                                        <span class="badge bg-{{ $hasilColor }} rounded-pill">
                                            <i class="bi bi-{{ $hasilIcon }} me-1"></i>{{ $hasilLabel }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info" title="Cetak Sticker"
                                                onclick="cetakStickerSatu({{ $item->id }})">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            @unless(auth()->user()->isGuest())
                                            <button type="button" class="btn btn-warning" title="Edit"
                                                onclick="showEditModal({{ $item->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" title="Hapus"
                                                onclick="deleteKalibrasi({{ $item->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endunless
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

                    {{-- ── Pagination ── --}}
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                        <div class="text-muted small">
                            Menampilkan {{ $kalibrasi->firstItem() ?? 0 }}
                            hingga {{ $kalibrasi->lastItem() ?? 0 }}
                            dari {{ $kalibrasi->total() ?? 0 }} data kalibrasi
                        </div>

                        @if($kalibrasi->hasPages())
                        <nav>
                            <ul class="pagination mb-0">
                                @if($kalibrasi->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $kalibrasi->previousPageUrl() }}">&laquo;</a>
                                    </li>
                                @endif

                                @php
                                    $current = $kalibrasi->currentPage();
                                    $last    = $kalibrasi->lastPage();
                                    $start   = max(1, $current - 2);
                                    $end     = min($last, $current + 2);
                                @endphp

                                @if($start > 1)
                                    <li class="page-item"><a class="page-link" href="{{ $kalibrasi->url(1) }}">1</a></li>
                                    @if($start > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
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
                                    @if($end < $last - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                                    <li class="page-item"><a class="page-link" href="{{ $kalibrasi->url($last) }}">{{ $last }}</a></li>
                                @endif

                                @if($kalibrasi->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $kalibrasi->nextPageUrl() }}">&raquo;</a></li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                @endif
                            </ul>
                        </nav>
                        @endif
                    </div>

                </div>{{-- /card-body --}}
            </div>{{-- /card --}}
        </div>
    </div>
</div>

{{-- ── Modal Container ── --}}
<div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" id="dynamicModalDialog">
        <div class="modal-content" id="dynamicModalContent"></div>
    </div>
</div>

{{-- Form tersembunyi untuk batch sticker --}}
<form id="stickerBatchForm" action="{{ route('kalibrasi.sticker.batch') }}" method="POST" target="_blank">
    @csrf
    <div id="batchIdsContainer"></div>
</form>

{{-- ── Styles ── --}}
<style>
.avatar-sm { width:34px; height:34px; font-size:0.85rem; flex-shrink:0; }
.card { border:none; border-radius:12px; }
.table th { font-weight:600; background-color:#f8f9fa !important; }
.badge { font-size:0.75em; }
.pagination { flex-wrap:wrap; }
.page-link { color:#4361EE; min-width:38px; text-align:center; }
.page-link:hover { color:#4361EE; background-color:#e9ecef; }
.page-item.active .page-link { background-color:#4361EE; border-color:#4361EE; color:#fff; }
.page-item.disabled .page-link { color:#6c757d; }
tr.row-selected { background-color:rgba(67,97,238,.06) !important; }
</style>

{{-- ── Scripts ── --}}
{{-- jQuery dimuat di sini agar pasti tersedia sebelum fungsi dipanggil --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link  href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
// ── Pastikan DOM + jQuery siap ─────────────────────────────────────────────
$(function () {

    // Session alerts
    var successMsg = $('#session-success').text().trim();
    var errorMsg   = $('#session-error').text().trim();
    if (successMsg) Swal.fire({ icon:'success', title:'Berhasil', text:successMsg, timer:3000, showConfirmButton:false });
    if (errorMsg)   Swal.fire({ icon:'error',   title:'Error',    text:errorMsg,   timer:4000 });

    // Auto-search delay
    var searchTimer;
    $('#searchInput').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { $('#filterForm').submit(); }, 800);
    });

    // Reset modal size + content saat ditutup
    $('#dynamicModal').on('hidden.bs.modal', function () {
        $('#dynamicModalContent').html('');
        $('#dynamicModalDialog').removeClass('modal-xl').addClass('modal-lg');
    });
});

// ── Checkbox helpers ───────────────────────────────────────────────────────
function toggleCheckAll(master) {
    document.querySelectorAll('.sticker-check').forEach(function (cb) {
        cb.checked = master.checked;
        cb.closest('tr').classList.toggle('row-selected', master.checked);
    });
    updateBatchCount();
}

function updateBatchCount() {
    var checked = document.querySelectorAll('.sticker-check:checked');
    var btn     = document.getElementById('btnStickerBatch');
    var label   = document.getElementById('jumlahDipilih');

    label.textContent = checked.length;

    if (checked.length > 0) {
        btn.style.removeProperty('display');
        btn.disabled = false;
    } else {
        btn.style.setProperty('display', 'none', 'important');
        btn.disabled = true;
    }

    document.querySelectorAll('.sticker-check').forEach(function (cb) {
        cb.closest('tr').classList.toggle('row-selected', cb.checked);
    });

    var all    = document.querySelectorAll('.sticker-check');
    var master = document.getElementById('checkAll');
    if (master) {
        master.checked       = checked.length === all.length && all.length > 0;
        master.indeterminate = checked.length > 0 && checked.length < all.length;
    }
}

// ── Sticker ────────────────────────────────────────────────────────────────
function cetakStickerSatu(id) {
    window.open('{{ url("kalibrasi") }}/' + id + '/sticker', '_blank');
}

function cetakStickerBatch() {
    var checked = document.querySelectorAll('.sticker-check:checked');
    if (checked.length === 0) {
        Swal.fire({ icon:'warning', title:'Belum ada yang dipilih', text:'Centang minimal satu data kalibrasi.' });
        return;
    }
    var container = document.getElementById('batchIdsContainer');
    container.innerHTML = '';
    checked.forEach(function (cb) {
        var input   = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    document.getElementById('stickerBatchForm').submit();
}

// ── Helper: buka modal via AJAX ───────────────────────────────────────────
function loadModal(url, size) {
    size = size || 'modal-lg';
    Swal.fire({ title:'Memuat...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });

    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            Swal.close();
            if (response && response.success) {
                $('#dynamicModalDialog').removeClass('modal-lg modal-xl').addClass(size);
                $('#dynamicModalContent').html(response.html);
                var modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
                modal.show();
            } else {
                Swal.fire({ icon:'error', title:'Error', text:(response && response.message) || 'Gagal memuat form.' });
            }
        },
        error: function (xhr) {
            Swal.close();
            var msg = 'Gagal memuat form.';
            if (xhr.status === 404) msg = 'Halaman tidak ditemukan (404). Periksa route di web.php.';
            if (xhr.status === 500) msg = 'Server error (500). Periksa log Laravel.';
            if (xhr.status === 302) msg = 'Redirect (302). Kemungkinan route belum terdaftar atau tertangkap fallback.';
            Swal.fire({ icon:'error', title:'Error ' + xhr.status, text:msg });
        }
    });
}

// ── Modal: Tambah (single) ─────────────────────────────────────────────────
function showCreateModal() {
    loadModal('{{ route("kalibrasi.create") }}', 'modal-lg');
}

// ── Modal: Edit ────────────────────────────────────────────────────────────
function showEditModal(id) {
    loadModal('{{ url("kalibrasi") }}/' + id + '/edit', 'modal-lg');
}

// ── Modal: Input Massal ────────────────────────────────────────────────────
function showBulkModal() {
    loadModal('{{ route("kalibrasi.bulk") }}', 'modal-xl');
}

// ── Modal: Import Excel ────────────────────────────────────────────────────
function showImportModal() {
    loadModal('{{ route("kalibrasi.importModal") }}', 'modal-lg');
}

// ── Hapus ──────────────────────────────────────────────────────────────────
function deleteKalibrasi(id) {
    Swal.fire({
        title: 'Hapus data kalibrasi?',
        text:  'Data yang dihapus tidak bisa dikembalikan.',
        icon:  'warning',
        showCancelButton:    true,
        confirmButtonColor:  '#d33',
        cancelButtonColor:   '#3085d6',
        confirmButtonText:   'Ya, hapus!',
        cancelButtonText:    'Batal'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        Swal.fire({ title:'Menghapus...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });

        $.ajax({
            url:  '{{ url("kalibrasi") }}/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({ icon:'success', title:'Berhasil', text:response.message, timer:2000, showConfirmButton:false })
                        .then(function () { location.reload(); });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:response.message || 'Gagal menghapus data.' });
                }
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire({ icon:'error', title:'Error', text:xhr.responseJSON?.message || 'Gagal menghapus data.' });
            }
        });
    });
}

// ── Submit form single (create / edit) ─────────────────────────────────────
$(document).on('submit', '#createForm, #editForm', function (e) {
    e.preventDefault();

    var form      = $(this);
    var submitBtn = form.find('button[type="submit"]');
    var oriTxt    = submitBtn.html();

    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

    // Bersihkan error lama
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();

    $.ajax({
        url:  form.attr('action'),
        type: form.attr('method'),
        data: form.serialize(),
        success: function (response) {
            submitBtn.prop('disabled', false).html(oriTxt);
            if (response.success) {
                Swal.fire({ icon:'success', title:'Berhasil', text:response.message, timer:2000, showConfirmButton:false })
                    .then(function () { $('#dynamicModal').modal('hide'); location.reload(); });
            }
        },
        error: function (xhr) {
            submitBtn.prop('disabled', false).html(oriTxt);
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                var errors = xhr.responseJSON.errors;
                for (var field in errors) {
                    var input = form.find('[name="' + field + '"]');
                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback">' + errors[field][0] + '</div>');
                }
                form.find('.is-invalid').first().focus();
            } else {
                Swal.fire({ icon:'error', title:'Error', text:'Terjadi kesalahan saat menyimpan data.' });
            }
        }
    });
});
</script>

@endsection