@extends('layouts.app')
@section('title', 'Master Tindakan')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background-color:white; color:#4361EE;">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="bi bi-wrench-adjustable me-2"></i>Master Tindakan Perbaikan
                        </h5>
                        <small class="text-muted">Daftar tindakan yang tersedia saat memproses perbaikan alat</small>
                    </div>
                    <button class="btn btn-sm" style="background-color:#4361EE; color:white;"
                        onclick="showTambahTindakanModal()">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Tindakan
                    </button>
                </div>
                <div class="card-body">

                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="tindakanTable">
                            <thead style="color:#4361EE; background-color:#f8f9fa;">
                                <tr>
                                    <th width="60" class="text-center">No</th>
                                    <th>Nama Tindakan</th>
                                    <th width="140" class="text-center">Dipakai Di</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tindakan as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $tindakan->firstItem() + $index }}</td>
                                    <td>
                                        <i class="bi bi-tools me-2 text-primary"></i>
                                        <strong>{{ $item->nama_tindakan }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @php $jumlah = $item->detailTindakan()->count(); @endphp
                                        @if($jumlah > 0)
                                            <span class="badge bg-info">{{ $jumlah }} catatan</span>
                                        @else
                                            <span class="badge bg-secondary">Belum dipakai</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            onclick="showEditTindakanModal({{ $item->id }})"
                                            data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="hapusTindakan({{ $item->id }}, '{{ addslashes($item->nama_tindakan) }}')"
                                            data-bs-toggle="tooltip" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        Belum ada data tindakan. Tambahkan tindakan pertama.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($tindakan->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Menampilkan {{ $tindakan->firstItem() }} hingga {{ $tindakan->lastItem() }}
                            dari {{ $tindakan->total() }} tindakan
                        </div>
                        <nav>{{ $tindakan->links() }}</nav>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Tambah Tindakan ───────────────────────────────────────────── -->
<div class="modal fade" id="modalTambahTindakan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color:white; color:#4361EE;">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Tindakan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahTindakan">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nama Tindakan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_tindakan" id="inputNamaTindakan"
                            class="form-control" placeholder="Contoh: Kalibrasi, Penggantian baterai, Pembersihan sensor..."
                            maxlength="255" required>
                        <div class="invalid-feedback" id="errorNamaTindakan"></div>
                        <div class="form-text">Maksimal 255 karakter.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnTambahTindakan">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Edit Tindakan ────────────────────────────────────────────── -->
<div class="modal fade" id="modalEditTindakan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header" style="background-color:white; color:#4361EE;">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Tindakan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditTindakan">
                @csrf
                @method('PUT')
                <input type="hidden" id="editTindakanId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nama Tindakan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_tindakan" id="editNamaTindakan"
                            class="form-control" maxlength="255" required>
                        <div class="invalid-feedback" id="errorEditNamaTindakan"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnEditTindakan">
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
    $('[data-bs-toggle="tooltip"]').each(function () { new bootstrap.Tooltip(this); });
});

function showTambahTindakanModal() {
    $('#formTambahTindakan')[0].reset();
    $('#inputNamaTindakan').removeClass('is-invalid');
    $('#errorNamaTindakan').text('');
    $('#modalTambahTindakan').modal('show');
    setTimeout(() => $('#inputNamaTindakan').focus(), 300);
}

$('#formTambahTindakan').on('submit', function (e) {
    e.preventDefault();
    const btn = $('#btnTambahTindakan');
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: '{{ route("master-tindakan.store") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', nama_tindakan: $('#inputNamaTindakan').val() },
        success: function (res) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
            if (res.success) {
                $('#modalTambahTindakan').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2500, showConfirmButton: false })
                    .then(() => location.reload());
            }
        },
        error: function (xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                const err = xhr.responseJSON.errors.nama_tindakan?.[0] || xhr.responseJSON.message;
                $('#inputNamaTindakan').addClass('is-invalid');
                $('#errorNamaTindakan').text(err);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
            }
        }
    });
});

function showEditTindakanModal(id) {
    $.ajax({
        url: '{{ url("master/tindakan") }}/' + id,
        method: 'GET',
        success: function (res) {
            if (res.success) {
                $('#editTindakanId').val(res.data.id);
                $('#editNamaTindakan').val(res.data.nama_tindakan).removeClass('is-invalid');
                $('#errorEditNamaTindakan').text('');
                $('#modalEditTindakan').modal('show');
                setTimeout(() => $('#editNamaTindakan').focus(), 300);
            }
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data tindakan.' });
        }
    });
}

$('#formEditTindakan').on('submit', function (e) {
    e.preventDefault();
    const id  = $('#editTindakanId').val();
    const btn = $('#btnEditTindakan');
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: '{{ url("master/tindakan") }}/' + id,
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'PUT', nama_tindakan: $('#editNamaTindakan').val() },
        success: function (res) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Perubahan');
            if (res.success) {
                $('#modalEditTindakan').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2500, showConfirmButton: false })
                    .then(() => location.reload());
            }
        },
        error: function (xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Perubahan');
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                const err = xhr.responseJSON.errors.nama_tindakan?.[0] || xhr.responseJSON.message;
                $('#editNamaTindakan').addClass('is-invalid');
                $('#errorEditNamaTindakan').text(err);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
            }
        }
    });
});

function hapusTindakan(id, nama) {
    Swal.fire({
        title: 'Hapus Tindakan?',
        html: 'Tindakan <strong>"' + nama + '"</strong> akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("master/tindakan") }}/' + id,
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