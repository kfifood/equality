<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-pencil me-2"></i>Edit Peralatan
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="editForm" action="{{ route('peralatan.update', $peralatan->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="kode_asset" class="form-label">Kode Asset <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="kode_asset" name="kode_asset"
                           value="{{ old('kode_asset', $peralatan->kode_asset) }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="kategori_alat_id" class="form-label">Kategori Alat <span class="text-danger">*</span></label>
                    <select class="form-select" id="kategori_alat_id" name="kategori_alat_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoriList as $kategori)
                            <option value="{{ $kategori->id }}"
                                {{ old('kategori_alat_id', $peralatan->kategori_alat_id) == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tanggal_datang" class="form-label">Tanggal Datang <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_datang" name="tanggal_datang"
                           value="{{ old('tanggal_datang', $peralatan->tanggal_datang ? $peralatan->tanggal_datang->format('Y-m-d') : '') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="lokasi_asli" class="form-label">Lokasi Asli <span class="text-danger">*</span></label>
                    <select class="form-select" id="lokasi_asli" name="lokasi_asli" required>
                        <option value="">Pilih Line</option>
                        <option value="Lab" {{ old('lokasi_asli', $peralatan->lokasi_asli) == 'Lab' ? 'selected' : '' }}>Lab</option>
                        @foreach($lineList as $line)
                            <option value="{{ $line }}" {{ old('lokasi_asli', $peralatan->lokasi_asli) == $line ? 'selected' : '' }}>
                                {{ $line }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="merk" class="form-label">Merk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="merk" name="merk"
                           value="{{ old('merk', $peralatan->merk) }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <input type="text" class="form-control" id="type" name="type"
                           value="{{ old('type', $peralatan->type) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="serial_number" class="form-label">No. Seri</label>
                    <input type="text" class="form-control" id="serial_number" name="serial_number"
                           value="{{ old('serial_number', $peralatan->serial_number) }}">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="certificate_number" class="form-label">Certificate Number</label>
            <input type="text" class="form-control" id="certificate_number" name="certificate_number"
                   value="{{ old('certificate_number', $peralatan->certificate_number) }}" placeholder="Contoh: CERT-2024-001">
        </div>

        {{-- Section Spesifikasi Dinamis, terisi dari data existing --}}
        <hr class="my-3">
        <h6 class="text-muted mb-2">
            <i class="bi bi-list-check me-1"></i>Spesifikasi Alat <small class="fw-normal">(opsional)</small>
        </h6>

        <div id="spesifikasiContainer">
            @php
                $existingSpek = $peralatan->spesifikasi ?? [];
            @endphp
            @forelse($existingSpek as $label => $value)
                <div class="row spesifikasi-row g-2 mb-2">
                    <div class="col-5">
                        <input type="text" class="form-control" name="spesifikasi_label[]" value="{{ $label }}" placeholder="Nama Spesifikasi">
                    </div>
                    <div class="col-5">
                        <input type="text" class="form-control" name="spesifikasi_value[]" value="{{ $value }}" placeholder="Nilai">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-outline-danger w-100 btn-remove-spek">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="row spesifikasi-row g-2 mb-2">
                    <div class="col-5">
                        <input type="text" class="form-control" name="spesifikasi_label[]" placeholder="Nama Spesifikasi">
                    </div>
                    <div class="col-5">
                        <input type="text" class="form-control" name="spesifikasi_value[]" placeholder="Nilai">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-outline-danger w-100 btn-remove-spek">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="btnTambahSpek">
            <i class="bi bi-plus-lg me-1"></i>Tambah Spesifikasi
        </button>

        <!-- Info Status Saat Ini -->
        <div class="card bg-light mb-3">
            <div class="card-body">
                <h6 class="card-title">Status Saat Ini</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Lokasi Saat Ini:</small><br>
                        @if($peralatan->status_line)
                            <span class="badge bg-info">{{ $peralatan->status_line }}</span>
                        @else
                            <span class="badge bg-secondary">Lab</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Kondisi:</small><br>
                        @php
                            $badgeColor = match($peralatan->kondisi_saat_ini) {
                                'Baik' => 'success',
                                'Rusak' => 'danger',
                                'Dalam Perbaikan' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ $peralatan->kondisi_saat_ini }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Lokasi asli</strong> dapat diubah untuk mengubah line tujuan utama peralatan ini.<br>
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

<script>
(function() {
    const container = document.getElementById('spesifikasiContainer');
    const btnTambah  = document.getElementById('btnTambahSpek');

    function rowTemplate() {
        const row = document.createElement('div');
        row.className = 'row spesifikasi-row g-2 mb-2';
        row.innerHTML = `
            <div class="col-5">
                <input type="text" class="form-control" name="spesifikasi_label[]" placeholder="Nama Spesifikasi">
            </div>
            <div class="col-5">
                <input type="text" class="form-control" name="spesifikasi_value[]" placeholder="Nilai">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger w-100 btn-remove-spek">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
        return row;
    }

    btnTambah.addEventListener('click', function() {
        container.appendChild(rowTemplate());
    });

    container.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-spek');
        if (!btn) return;
        const rows = container.querySelectorAll('.spesifikasi-row');
        if (rows.length > 1) {
            btn.closest('.spesifikasi-row').remove();
        } else {
            btn.closest('.spesifikasi-row').querySelectorAll('input').forEach(i => i.value = '');
        }
    });
})();
</script>