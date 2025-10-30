@extends('layouts.app')
@section('title', 'Monitoring Kondisi Timbangan')
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monitoring Kondisi Timbangan</h5>
                </div>
                <div class="card-body">
                    <!-- Statistik Kondisi -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start border-4 border-primary h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="text-uppercase text-muted mb-2">Total Timbangan</h6>
                                            <h2 class="mb-0">{{ $statistik['total'] }}</h2>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-speedometer fa-2x text-primary opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start border-4 border-success h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="text-uppercase text-muted mb-2">Kondisi Baik</h6>
                                            <h2 class="mb-0">{{ $statistik['baik'] }}</h2>
                                            <small class="text-muted">{{ $statistik['total'] > 0 ? round(($statistik['baik'] / $statistik['total']) * 100, 1) : 0 }}% dari total</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-check-circle fa-2x text-success opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start border-4 border-danger h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="text-uppercase text-muted mb-2">Timbangan Rusak</h6>
                                            <h2 class="mb-0">{{ $statistik['rusak'] }}</h2>
                                            <small class="text-muted">Perlu perbaikan</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-exclamation-triangle fa-2x text-danger opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stat-card border-start border-4 border-warning h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="text-uppercase text-muted mb-2">Dalam Perbaikan</h6>
                                            <h2 class="mb-0">{{ $statistik['perbaikan'] }}</h2>
                                            <small class="text-muted">Sedang diperbaiki</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-tools fa-2x text-warning opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="kondisiTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Asset</th>
                                    <th>Tipe</th>
                                    <th>Lokasi Saat Ini</th>
                                    <th>Kondisi Saat Ini</th>
                                    <th>Update Terakhir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timbangan as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $item->kode_asset }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->tipe == 'Besar' ? 'info' : 'secondary' }}">
                                            {{ $item->tipe }}
                                        </span>
                                    </td>
                                    <td>{{ $item->lokasi_saat_ini }}</td>
                                    <td>
                                        <form action="{{ route('kondisi.update', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <select name="kondisi_saat_ini" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()" 
                                                    style="width: auto; display: inline-block;">
                                                <option value="Baik" {{ $item->kondisi_saat_ini == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                <option value="Rusak" {{ $item->kondisi_saat_ini == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                                <option value="Dalam Perbaikan" {{ $item->kondisi_saat_ini == 'Dalam Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>{{ $item->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('timbangan.riwayat', $item->id) }}" 
                                           class="btn btn-info btn-sm" title="Lihat Riwayat">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
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

<style>
.stat-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.form-select-sm {
    width: auto !important;
    display: inline-block !important;
    min-width: 150px;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#kondisiTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ]
        });
    });
</script>
@endsection