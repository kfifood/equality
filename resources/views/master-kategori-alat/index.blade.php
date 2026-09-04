@extends('layouts.app')
@section('title', 'Master Kategori Alat')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tags me-2"></i>Master Kategori Alat
                    </h5>
                    <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                        onclick="showCreateModal()">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
                    </button>
                </div>
                <div class="card-body">
                    <!-- Session Messages -->
                    @if(session('success'))
                    <div class="d-none" id="session-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                    <div class="d-none" id="session-error">{{ session('error') }}</div>
                    @endif

                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="{{ route('master-kategori-alat.index') }}" method="GET" id="filterForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status_aktif" class="form-select"
                                            onchange="document.getElementById('filterForm').submit()">
                                            <option value="">Semua Status</option>
                                            <option value="1" {{ request('status_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ request('status_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pencarian</label>
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Cari kode atau nama kategori..."
                                                value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary" type="submit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            @if(request()->anyFilled(['status_aktif', 'search']))
                                            <a href="{{ route('master-kategori-alat.index') }}" class="btn btn-outline-danger">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead style="color:#4361EE;">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode</th>
                                    <th>Nama Kategori</th>
                                    <th>Satuan Default</th>
                                    <th class="text-center">Jumlah Peralatan</th>
                                    <th class="text-center">Status</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoriList as $index => $kategori)
                                <tr>
                                    <td class="text-center">{{ $kategoriList->firstItem() + $index }}</td>
                                    <td><span class="font-monospace">{{ $kategori->kode_kategori }}</span></td>
                                    <td><strong>{{ $kategori->nama_kategori }}</strong></td>
                                    <td>{{ $kategori->satuan_default ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $kategori->peralatan_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($kategori->status_aktif)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-warning" title="Edit"
                                                onclick="showEditModal({{ $kategori->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" title="Hapus"
                                                onclick="deleteKategori({{ $kategori->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada kategori alat. Klik "Tambah Kategori" untuk mulai.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($kategoriList->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $kategoriList->firstItem() ?? 0 }} hingga {{ $kategoriList->lastItem() ?? 0 }}
                            dari {{ $kategoriList->total() ?? 0 }} kategori
                        </div>
                        <nav>
                            {{ $kategoriList->links() }}
                        </nav>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create / Edit (dipakai bersama) -->
<div class="modal fade" id="kategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:white; color:#4361EE;">
                <h5 class="modal-title" id="kategoriModalTitle">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Kategori Alat
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="kategoriForm">
                @csrf
                <input type="hidden" id="kategoriMethod" value="POST">
                <input type="hidden" id="kategoriId" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_kategori" class="form-label">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="kode_kategori" name="kode_kategori"
                               placeholder="Contoh: TMB, TRM, RFR" maxlength="50" required>
                        <div class="form-text">Kode singkat, unik, contoh: TMB untuk Timbangan.</div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori"
                               placeholder="Contoh: Timbangan, Termometer, Refraktometer" required>
                    </div>
                    <div class="mb-3">
                        <label for="satuan_default" class="form-label">Satuan Default</label>
                        <input type="text" class="form-control" id="satuan_default" name="satuan_default"
                               placeholder="Contoh: gr, °C, %Brix (opsional)">
                        <div class="form-text">Hanya sebagai hint tampilan, tidak wajib diisi.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status_aktif" name="status_aktif" value="1" checked>
                        <label class="form-check-label" for="status_aktif">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card { border: none; border-radius: 12px; }
.table th { font-weight: 600; background-color: #f8f9fa !important; }
.badge { font-size: 0.75em; }
</style>
@endsection

@push('scripts')
<script>
const kategoriModal = new bootstrap.Modal(document.getElementById('kategoriModal'));

$(document).ready(function() {
    const successMessage = $('#session-success').text();
    const errorMessage   = $('#session-error').text();

    if (successMessage) {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: successMessage, timer: 3000, showConfirmButton: false });
    }
    if (errorMessage) {
        Swal.fire({ icon: 'error', title: 'Error', text: errorMessage, timer: 4000 });
    }

    let searchTimer;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { $('#filterForm').submit(); }, 800);
    });

    $('#kode_kategori').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
});

function resetKategoriForm() {
    $('#kategoriForm')[0].reset();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    $('#status_aktif').prop('checked', true);
}

function showCreateModal() {
    resetKategoriForm();
    $('#kategoriModalTitle').html('<i class="bi bi-plus-circle me-2"></i>Tambah Kategori Alat');
    $('#kategoriMethod').val('POST');
    $('#kategoriId').val('');
    kategoriModal.show();
}

function showEditModal(id) {
    $.ajax({
        url: '{{ url("master/kategori-alat") }}/' + id + '/edit',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                resetKategoriForm();
                const data = response.data;
                $('#kategoriModalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Kategori Alat');
                $('#kategoriMethod').val('PUT');
                $('#kategoriId').val(data.id);
                $('#kode_kategori').val(data.kode_kategori);
                $('#nama_kategori').val(data.nama_kategori);
                $('#satuan_default').val(data.satuan_default);
                $('#status_aktif').prop('checked', !!data.status_aktif);
                kategoriModal.show();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data kategori' });
            }
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data kategori' });
        }
    });
}

function deleteKategori(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Kategori akan dihapus permanen. Kategori yang masih dipakai peralatan tidak bisa dihapus.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("master/kategori-alat") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 2000, showConfirmButton: false })
                            .then(() => { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Gagal menghapus kategori' });
                    }
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Gagal menghapus kategori';
                    Swal.fire({ icon: 'error', title: 'Error', text: errorMessage });
                }
            });
        }
    });
}

$('#kategoriForm').on('submit', function(e) {
    e.preventDefault();

    const method = $('#kategoriMethod').val();
    const id     = $('#kategoriId').val();
    const url    = method === 'PUT'
        ? '{{ url("master/kategori-alat") }}/' + id
        : '{{ route("master-kategori-alat.store") }}';

    const payload = {
        _token: '{{ csrf_token() }}',
        kode_kategori: $('#kode_kategori').val(),
        nama_kategori: $('#nama_kategori').val(),
        satuan_default: $('#satuan_default').val(),
        status_aktif: $('#status_aktif').is(':checked') ? 1 : 0,
    };
    if (method === 'PUT') payload._method = 'PUT';

    const submitBtn = $('#kategoriForm button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');

    $.ajax({
        url: url,
        type: 'POST', // pakai method spoofing _method untuk PUT
        data: payload,
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);
            if (response.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => { kategoriModal.hide(); location.reload(); });
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                for (const field in errors) {
                    const input = $('[name="' + field + '"]');
                    input.addClass('is-invalid');
                    input.after('<div class="invalid-feedback">' + errors[field][0] + '</div>');
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan saat menyimpan data' });
            }
        }
    });
});
</script>
@endpush