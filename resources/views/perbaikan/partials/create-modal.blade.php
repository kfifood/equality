<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-tools me-2"></i>Catat Perbaikan Timbangan
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="createPerbaikanForm" action="{{ route('perbaikan.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Pilih Timbangan <span class="text-danger">*</span></label>
                    <select name="timbangan_id" class="form-select" required>
                        <option value="">Pilih Timbangan</option>
                        @foreach($timbangan as $item)
                            <option value="{{ $item->id }}" 
                                {{ $selectedTimbangan && $selectedTimbangan->id == $item->id ? 'selected' : '' }}>
                                {{ $item->kode_asset }} 
                                @if($item->nomor_seri_unik)
                                    - {{ $item->nomor_seri_unik }}
                                @endif
                                - {{ $item->merk_tipe_no_seri }}
                                ({{ $item->status_line }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Hanya menampilkan timbangan dengan kondisi <strong>Rusak</strong> atau <strong>Dalam Perbaikan</strong> yang masih di <strong>Line</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Line Sebelumnya <span class="text-danger">*</span></label>
                    <select name="line_sebelumnya" class="form-select" required>
                        <option value="">Pilih Line</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->nama_line }}">
                                {{ $line->nama_line }} ({{ $line->department }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tanggal Masuk Lab <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_masuk_lab" class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Penggunaan Terakhir</label>
                    <input type="text" name="penggunaan_terakhir" class="form-control" 
                           placeholder="Siapa yang terakhir menggunakan">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi Keluhan <span class="text-danger">*</span></label>
            <textarea name="deskripsi_keluhan" class="form-control" rows="4" required
                      placeholder="Jelaskan keluhan/kerusakan yang dialami (fluktuasi, tidak akurat, dll)"></textarea>
        </div>

        <div class="alert alert-warning">
            <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Timbangan akan dikembalikan ke <strong>Lab</strong> dan status berubah menjadi <strong>Dalam Perbaikan</strong>.
            </small>
        </div>

        <!-- Di bagian alert info, tambahkan informasi -->
<div class="alert alert-info">
    <small>
        <i class="bi bi-info-circle me-1"></i>
        <strong>Informasi:</strong><br>
        • Hanya timbangan dengan kondisi <strong>Baik</strong> dan berada di <strong>Lab</strong> yang bisa digunakan<br>
        • Setelah dicatat, timbangan akan berpindah status ke line tujuan<br>
        • Timbangan dengan nomor seri berbeda dapat memiliki kode asset yang sama<br>
        • <strong>Timbangan yang baru selesai perbaikan akan tersedia di Lab</strong>
    </small>
</div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan Perbaikan
        </button>
    </div>
</form>