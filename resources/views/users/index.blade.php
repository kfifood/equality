@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people me-2"></i>Manajemen User
                    </h5>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Tambah User
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>RFID Code</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $user->username }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->full_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ match($user->role) {
                                            'superadmin' => 'danger',
                                            'manager' => 'warning', 
                                            'supervisor' => 'info',
                                            'admin' => 'primary',
                                            default => 'secondary'
                                        } }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td>{{ $user->department ?: '-' }}</td>
                                    <td>
                                        @if($user->rfid_code)
                                            <code>{{ $user->rfid_code }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('users.update-status', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-{{ $user->is_active ? 'success' : 'secondary' }}">
                                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($user->last_login_at)
                                            <small class="text-muted">
                                                {{ $user->last_login_at->format('d/m/Y H:i') }}
                                            </small>
                                        @else
                                            <span class="text-muted">Belum login</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#passwordModal{{ $user->id }}"
                                                    title="Ubah Password">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <a href="{{ route('users.edit', $user->id) }}" 
                                               class="btn btn-info" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('users.destroy', $user->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" 
                                                        title="Hapus" 
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Password Modal -->
                                        <div class="modal fade" id="passwordModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('users.change-password', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Ubah Password - {{ $user->full_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="new_password" class="form-label">Password Baru</label>
                                                                <input type="password" class="form-control" 
                                                                       id="new_password" name="new_password" 
                                                                       placeholder="Masukkan password baru" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan Password</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $users->firstItem() }} hingga {{ $users->lastItem() }} dari {{ $users->total() }} user
                        </div>
                        <nav>
                            {{ $users->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 0.9rem;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}
</style>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        "paging": false,
        "searching": true,
        "ordering": true,
        "info": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        }
    });
});
</script>
@endsection