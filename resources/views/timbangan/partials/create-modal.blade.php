<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-plus-circle me-2"></i>Tambah Peralatan Baru
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="createForm" action="{{ route('timbangan.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="kode_asset" class="form-label">Kode Asset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kode_asset" name="kode_asset" 
                           value="{{ old('kode_asset') }}" placeholder="Contoh: W-002" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal_datang" class="form-label">Tanggal Datang <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_datang" name="tanggal_datang" 
                           value="{{ old('tanggal_datang') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="lokasi_asli" class="form-label">Lokasi Asli <span class="text-danger">*</span></label>
                    <select class="form-select" id="lokasi_asli" name="lokasi_asli" required>
                        <option value="">Pilih Line</option>
                        <option value="Lab">Lab</option>
                        @foreach($lineList as $line)
                            <option value="{{ $line }}" {{ old('lokasi_asli') == $line ? 'selected' : '' }}>
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
                   value="{{ old('merk_tipe_no_seri') }}" placeholder="Contoh: AND EK-2000i No.12345" required>
        </div>

        {{--Section alat ukur --}}
<hr class="my-3">
<h6 class="text-muted mb-3"><i class="bi bi-info-circle me-1"></i>Informasi Teknis <small class="fw-normal">(opsional)</small></h6>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="jenis_alat_ukur" class="form-label">Jenis Alat Ukur</label>
            <select class="form-select" id="jenis_alat_ukur" name="jenis_alat_ukur">
                <option value="">-- Pilih Jenis --</option>
                <option value="Timbangan" {{ old('jenis_alat_ukur') == 'Timbangan' ? 'selected' : '' }}>Timbangan</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="kapasitas" class="form-label">Kapasitas</label>
            <input type="text" class="form-control" id="kapasitas" name="kapasitas"
                   value="{{ old('kapasitas') }}" placeholder="Contoh: 30 kg, 5000 g">
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="certificate_number" class="form-label">Certificate Number</label>
    <input type="text" class="form-control" id="certificate_number" name="certificate_number"
           value="{{ old('certificate_number') }}" placeholder="Contoh: CERT-2024-001">
</div>
        <div class="alert alert-info">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                Timbangan baru otomatis akan disimpan di <strong>Lab</strong> dengan kondisi <strong>Baik</strong>.
                Lokasi asli menentukan line tujuan utama timbangan ini.
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan
        </button>
    </div>
</form>