@extends('layouts.app')
@section('title', 'Master Keluhan')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>Master Keluhan
                        </h5>
                        <small class="text-muted">Daftar keluhan alat yang tersedia untuk laporan kerusakan</small>
                    </div>
                    <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                        onclick="showTambahKeluhanModal()">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Keluhan
                    </button>
                </div>
                <div class="card-body">

                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="keluhanTable">
                            <thead style="color:#4361EE; background-color:#f8f9fa;">
                                <tr>
                                    <th width="60" class="text-center">No</th>
                                    <th>Nama Keluhan</th>
                                    <th width="120" class="text-center">Dipakai Di</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($keluhan as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $keluhan->firstItem() + $index }}</td>
                                    <td>
                                        <i class="bi bi-tag me-2 text-primary"></i>
                                        <strong>{{ $item->nama_keluhan }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @php $jumlah = $item->laporanKerusakan()->count(); @endphp
                                        @if($jumlah > 0)
                                            <span class="badge bg-info">{{ $jumlah }} laporan</span>
                                        @else
                                            <span class="badge bg-secondary">Belum dipakai</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            onclick="showEditKeluhanModal({{ $item->id }})"
                                            data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="hapusKeluhan({{ $item->id }}, '{{ addslashes($item->nama_keluhan) }}')"
                                            data-bs-toggle="tooltip" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        Belum ada data keluhan. Tambahkan keluhan pertama.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($keluhan->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $keluhan->firstItem() }} hingga {{ $keluhan->lastItem() }}
                            dari {{ $keluhan->total() }} keluhan
                        </div>
                        <nav>{{ $keluhan->links() }}</nav>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Tambah Keluhan ────────────────────────────────────────────── -->
<div class="modal fade" id="modalTambahKeluhan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color:white; color:#4361EE;">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Keluhan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahKeluhan">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nama Keluhan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_keluhan" id="inputNamaKeluhan"
                            class="form-control" placeholder="Contoh: Layar tidak menyala, Timbangan tidak akurat..."
                            maxlength="255" required>
                        <div class="invalid-feedback" id="errorNamaKeluhan"></div>
                        <div class="form-text">Maksimal 255 karakter.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnTambahKeluhan">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Edit Keluhan ──────────────────────────────────────────────── -->
<div class="modal fade" id="modalEditKeluhan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color:white; color:#4361EE;">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Keluhan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKeluhan">
                @csrf
                @method('PUT')
                <input type="hidden" id="editKeluhanId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nama Keluhan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_keluhan" id="editNamaKeluhan"
                            class="form-control" maxlength="255" required>
                        <div class="invalid-feedback" id="errorEditNamaKeluhan"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnEditKeluhan">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Init tooltips
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });
});

// ── Buka modal tambah ──────────────────────────────────────────────────────
function showTambahKeluhanModal() {
    $('#formTambahKeluhan')[0].reset();
    $('#inputNamaKeluhan').removeClass('is-invalid');
    $('#errorNamaKeluhan').text('');
    $('#modalTambahKeluhan').modal('show');
    setTimeout(() => $('#inputNamaKeluhan').focus(), 300);
}

// ── Submit tambah keluhan ──────────────────────────────────────────────────
$('#formTambahKeluhan').on('submit', function (e) {
    e.preventDefault();
    const btn = $('#btnTambahKeluhan');
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: '{{ route("master-keluhan.store") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            nama_keluhan: $('#inputNamaKeluhan').val()
        },
        success: function (res) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
            if (res.success) {
                $('#modalTambahKeluhan').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2500, showConfirmButton: false })
                    .then(() => location.reload());
            }
        },
        error: function (xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                const err = xhr.responseJSON.errors.nama_keluhan?.[0] || xhr.responseJSON.message;
                $('#inputNamaKeluhan').addClass('is-invalid');
                $('#errorNamaKeluhan').text(err);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
            }
        }
    });
});

// ── Buka modal edit ────────────────────────────────────────────────────────
function showEditKeluhanModal(id) {
    $.ajax({
        url: '{{ url("master/keluhan") }}/' + id,
        method: 'GET',
        success: function (res) {
            if (res.success) {
                $('#editKeluhanId').val(res.data.id);
                $('#editNamaKeluhan').val(res.data.nama_keluhan).removeClass('is-invalid');
                $('#errorEditNamaKeluhan').text('');
                $('#modalEditKeluhan').modal('show');
                setTimeout(() => $('#editNamaKeluhan').focus(), 300);
            }
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data keluhan.' });
        }
    });
}

// ── Submit edit keluhan ────────────────────────────────────────────────────
$('#formEditKeluhan').on('submit', function (e) {
    e.preventDefault();
    const id  = $('#editKeluhanId').val();
    const btn = $('#btnEditKeluhan');
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: '{{ url("master/keluhan") }}/' + id,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            nama_keluhan: $('#editNamaKeluhan').val()
        },
        success: function (res) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Perubahan');
            if (res.success) {
                $('#modalEditKeluhan').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2500, showConfirmButton: false })
                    .then(() => location.reload());
            }
        },
        error: function (xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Perubahan');
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                const err = xhr.responseJSON.errors.nama_keluhan?.[0] || xhr.responseJSON.message;
                $('#editNamaKeluhan').addClass('is-invalid');
                $('#errorEditNamaKeluhan').text(err);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
            }
        }
    });
});

// ── Hapus keluhan ──────────────────────────────────────────────────────────
function hapusKeluhan(id, nama) {
    Swal.fire({
        title: 'Hapus Keluhan?',
        html: 'Keluhan <strong>"' + nama + '"</strong> akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("master/keluhan") }}/' + id,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 2000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Tidak Bisa Dihapus', text: res.message });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Gagal menghapus.' });
                }
            });
        }
    });
}
</script>
@endpush