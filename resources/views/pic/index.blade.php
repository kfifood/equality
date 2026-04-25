@extends('layouts.app')

@section('title', 'Master PIC')

@section('content')

{{-- Flash Messages --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toastr.success('{{ session('success') }}');
        });
    </script>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Master PIC</h4>
        <small class="text-muted">Daftar Person In Charge (PIC) per Line</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i> Tambah PIC
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablePic" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode PIC</th>
                        <th>Nama PIC</th>
                        <th>Jabatan</th>
                        <th>Line</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pics as $i => $pic)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">{{ $pic->kode_pic }}</span></td>
                        <td>{{ $pic->nama_pic }}</td>
                        <td>{{ $pic->jabatan ?? '-' }}</td>
                        <td>
                            @if($pic->line)
                                <span class="badge bg-info bg-opacity-15 text-white">
                                    <i class="bi bi-diagram-3 me-1"></i>{{ $pic->line->nama_line }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($pic->status_aktif)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- Tombol Edit --}}
                            <button class="btn btn-sm btn-outline-primary btn-edit"
                                data-id="{{ $pic->id }}"
                                data-kode="{{ $pic->kode_pic }}"
                                data-nama="{{ $pic->nama_pic }}"
                                data-jabatan="{{ $pic->jabatan }}"
                                data-line="{{ $pic->line_id }}"
                                data-status="{{ $pic->status_aktif ? '1' : '0' }}"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            {{-- Tombol Hapus --}}
                            <form action="{{ route('pic.destroy', $pic->id) }}" method="POST" class="d-inline form-hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===================== MODAL TAMBAH ===================== --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah PIC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pic.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <small><i class="bi bi-magic me-1"></i>Kode PIC akan di-generate otomatis.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama PIC <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pic" class="form-control" placeholder="Nama lengkap PIC" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Operator, Supervisor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Line <span class="text-danger">*</span></label>
                        <select name="line_id" class="form-select select2-line" required>
                            <option value="">-- Pilih Line --</option>
                            @foreach(\App\Models\MasterLine::aktif()->get() as $line)
                                <option value="{{ $line->id }}">{{ $line->nama_line }} ({{ $line->kode_line }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status_aktif" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL EDIT ===================== --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit PIC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode PIC</label>
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold fs-6" id="edit_kode_display">-</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama PIC <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pic" id="edit_nama_pic" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jabatan</label>
                        <input type="text" name="jabatan" id="edit_jabatan" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Line <span class="text-danger">*</span></label>
                        <select name="line_id" id="edit_line_id" class="form-select select2-line-edit" required>
                            <option value="">-- Pilih Line --</option>
                            @foreach(\App\Models\MasterLine::aktif()->get() as $line)
                                <option value="{{ $line->id }}">{{ $line->nama_line }} ({{ $line->kode_line }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status_aktif" id="edit_status" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // Init DataTables
    $('#tablePic').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
        },
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: 6 }]
    });

    // Init Select2
    $('.select2-line').select2({
        dropdownParent: $('#modalTambah'),
        placeholder: '-- Pilih Line --',
        theme: 'bootstrap-5',
        width: '100%',
    });
    $('.select2-line-edit').select2({
        dropdownParent: $('#modalEdit'),
        placeholder: '-- Pilih Line --',
        theme: 'bootstrap-5',
        width: '100%',
    });

    // Isi Modal Edit
    $(document).on('click', '.btn-edit', function () {
        const id     = $(this).data('id');
        const action = `/pic/${id}`;

        $('#formEdit').attr('action', action);
        $('#edit_kode_display').text($(this).data('kode'));
        $('#edit_nama_pic').val($(this).data('nama'));
        $('#edit_jabatan').val($(this).data('jabatan'));
        $('#edit_status').val($(this).data('status'));
        $('#edit_line_id').val($(this).data('line')).trigger('change');

        $('#modalEdit').modal('show');
    });

    // Konfirmasi Hapus
    $(document).on('submit', '.form-hapus', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus PIC?',
            text: 'Data PIC akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });

});
</script>
@endpush