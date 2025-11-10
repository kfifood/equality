<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-pencil me-2"></i>Edit Peralatan
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="editForm" action="{{ route('timbangan.update', $timbangan->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="kode_asset" class="form-label">Kode Asset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kode_asset" name="kode_asset" 
                           value="{{ old('kode_asset', $timbangan->kode_asset) }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal_datang" class="form-label">Tanggal Datang <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_datang" name="tanggal_datang" 
                           value="{{ old('tanggal_datang', $timbangan->tanggal_datang ? $timbangan->tanggal_datang->format('Y-m-d') : '') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="lokasi_asli" class="form-label">Lokasi Asli <span class="text-danger">*</span></label>
                    <select class="form-select" id="lokasi_asli" name="lokasi_asli" required>
                        <option value="">Pilih Line</option>
                        <option value="Lab" {{ old('lokasi_asli', $timbangan->lokasi_asli) == 'Lab' ? 'selected' : '' }}>Lab</option>
                        @foreach($lineList as $line)
                            <option value="{{ $line }}" {{ old('lokasi_asli', $timbangan->lokasi_asli) == $line ? 'selected' : '' }}>
                                {{ $line }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="merk_tipe_no_seri" class="form-label">Merk, Tipe & No Seri <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="merk_tipe_no_seri" name="merk_tipe_no_seri" 
                   value="{{ old('merk_tipe_no_seri', $timbangan->merk_tipe_no_seri) }}" required>
        </div>

        <!-- Info Status Saat Ini -->
        <div class="card bg-light mb-3">
            <div class="card-body">
                <h6 class="card-title">Status Saat Ini</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Lokasi Saat Ini:</small><br>
                        @if($timbangan->status_line)
                            <span class="badge bg-info">{{ $timbangan->status_line }}</span>
                        @else
                            <span class="badge bg-secondary">Lab</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Kondisi:</small><br>
                        @php
                            $badgeColor = match($timbangan->kondisi_saat_ini) {
                                'Baik' => 'success',
                                'Rusak' => 'danger',
                                'Dalam Perbaikan' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ $timbangan->kondisi_saat_ini }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Lokasi asli</strong> dapat diubah untuk mengubah line tujuan utama timbangan ini.<br>
                <strong>Lokasi saat ini</strong> dan <strong>kondisi</strong> diubah melalui menu <strong>Penggunaan</strong> atau <strong>Perbaikan</strong>.
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Update
        </button>
    </div>
</form>