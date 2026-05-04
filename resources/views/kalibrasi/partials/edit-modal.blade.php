<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-pencil me-2"></i>Edit Data Kalibrasi
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form id="editForm" action="{{ route('kalibrasi.update', $kalibrasi->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">

        {{-- Timbangan --}}
        <div class="mb-3">
            <label for="timbangan_id" class="form-label">
                Timbangan <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="timbangan_id" name="timbangan_id" required>
                <option value="">-- Pilih Timbangan --</option>
                @foreach($timbanganList as $t)
    <option value="{{ $t->id }}"
        data-certificate="{{ $t->certificate_number ?? '' }}"
        {{ old('timbangan_id', $kalibrasi->timbangan_id) == $t->id ? 'selected' : '' }}>
        {{ $t->kode_asset }} — {{ $t->merk_tipe_no_seri }}
    </option>
@endforeach
            </select>
        </div>

        <div class="row">
            {{-- Tanggal Pelaksanaan --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal_pelaksanaan" class="form-label">
                        Tanggal Pelaksanaan <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="tanggal_pelaksanaan"
                           name="tanggal_pelaksanaan"
                           value="{{ old('tanggal_pelaksanaan', $kalibrasi->tanggal_pelaksanaan?->format('Y-m-d')) }}"
                           required>
                </div>
            </div>
            {{-- Hasil --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="hasil" class="form-label">Hasil Kalibrasi</label>
                    <select class="form-select" id="hasil" name="hasil">
                        <option value="">-- Pilih Hasil --</option>
                        <option value="Lulus"
                            {{ old('hasil', $kalibrasi->hasil) == 'Lulus' ? 'selected' : '' }}>
                            Lulus
                        </option>
                        <option value="Tidak Lulus"
                            {{ old('hasil', $kalibrasi->hasil) == 'Tidak Lulus' ? 'selected' : '' }}>
                            Tidak Lulus
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Dept / Bagian --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="dept_bagian" class="form-label">Dept / Bagian</label>
                    <input type="text" class="form-control" id="dept_bagian" name="dept_bagian"
                           value="{{ old('dept_bagian', $kalibrasi->dept_bagian) }}"
                           placeholder="Contoh: QC, Produksi, Lab">
                </div>
            </div>
            {{-- Pelaksana --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="pelaksana" class="form-label">Pelaksana</label>
                    <input type="text" class="form-control" id="pelaksana" name="pelaksana"
                           value="{{ old('pelaksana', $kalibrasi->pelaksana) }}"
                           placeholder="Nama teknisi atau lembaga kalibrasi">
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Certificate Number --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="certificate_number" class="form-label">Certificate Number</label>
                    <input type="text" class="form-control" id="certificate_number"
                           name="certificate_number"
                           value="{{ old('certificate_number', $kalibrasi->certificate_number) }}"
                           placeholder="Contoh: CAL-2024-001">
                </div>
            </div>
            {{-- Beda Maksimum --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="beda_maksimum" class="form-label">
                        Beda Maksimum
                        <span class="text-muted small">(opsional)</span>
                    </label>
                    <input type="text" class="form-control" id="beda_maksimum"
                           name="beda_maksimum"
                           value="{{ old('beda_maksimum', $kalibrasi->beda_maksimum) }}"
                           placeholder="Contoh: ±0.5 g">
                </div>
            </div>
        </div>

        {{-- Catatan --}}
        <div class="mb-3">
            <label for="catatan" class="form-label">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3"
                      placeholder="Catatan tambahan (opsional)">{{ old('catatan', $kalibrasi->catatan) }}</textarea>
        </div>

        {{-- Info record --}}
        <div class="alert alert-light border small text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Dibuat: {{ $kalibrasi->created_at?->format('d/m/Y H:i') ?? '-' }}
            &nbsp;|&nbsp;
            Terakhir diubah: {{ $kalibrasi->updated_at?->format('d/m/Y H:i') ?? '-' }}
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Update
        </button>
    </div>
</form>
<script>
document.getElementById('timbangan_id').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const certNo   = selected.getAttribute('data-certificate') || '';
    document.getElementById('certificate_number').value = certNo;
});
</script>