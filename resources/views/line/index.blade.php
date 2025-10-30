@extends('layouts.app')
@section('title', 'Master Line Produksi')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>Master Line Produksi
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLineModal">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Line
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="lineTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Line</th>
                                    <th>Nama Line</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $index => $line)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $line->kode_line }}</td>
                                    <td>{{ $line->nama_line }}</td>
                                    <td>{{ $line->department }}</td>
                                    <td>
                                        <span class="badge bg-{{ $line->status_aktif ? 'success' : 'danger' }}">
                                            {{ $line->status_aktif ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-warning edit-line" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editLineModal"
                                                    data-id="{{ $line->id }}"
                                                    data-kode="{{ $line->kode_line }}"
                                                    data-nama="{{ $line->nama_line }}"
                                                    data-department="{{ $line->department }}"
                                                    data-status="{{ $line->status_aktif }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('line.destroy', $line->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" 
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus line ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Line Modal -->
<div class="modal fade" id="addLineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('line.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Line Produksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Line</label>
                        <input type="text" class="form-control" name="kode_line" required 
                               placeholder="Contoh: LN-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Line</label>
                        <input type="text" class="form-control" name="nama_line" required 
                               placeholder="Contoh: Line Fillet">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department" required 
                               placeholder="Contoh: Produksi">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Line Modal -->
<div class="modal fade" id="editLineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editLineForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Line Produksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Line</label>
                        <input type="text" class="form-control" name="kode_line" id="edit_kode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Line</label>
                        <input type="text" class="form-control" name="nama_line" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department" id="edit_department" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status_aktif" id="edit_status">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#lineTable').DataTable();

    $('.edit-line').click(function() {
        var id = $(this).data('id');
        var kode = $(this).data('kode');
        var nama = $(this).data('nama');
        var department = $(this).data('department');
        var status = $(this).data('status');

        $('#edit_kode').val(kode);
        $('#edit_nama').val(nama);
        $('#edit_department').val(department);
        $('#edit_status').val(status ? '1' : '0');
        
        $('#editLineForm').attr('action', '/master/line/' + id);
    });
});
</script>
@endsection