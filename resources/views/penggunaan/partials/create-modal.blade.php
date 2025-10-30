<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-arrow-right-circle me-2"></i>Catat Penggunaan Timbangan
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="createPenggunaanForm" action="{{ route('penggunaan.store') }}" method="POST">
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
                                (Lab)
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Hanya menampilkan timbangan dengan kondisi <strong>Baik</strong> yang berada di <strong>Lab</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Line Tujuan <span class="text-danger">*</span></label>
                    <select name="line_tujuan" class="form-select" required>
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
                    <label class="form-label">Tanggal Pemakaian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_pemakaian" class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">PIC</label>
                    <input type="text" name="pic" class="form-control" 
                           placeholder="Nama penanggung jawab">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" 
                      placeholder="Keterangan penggunaan (opsional)"></textarea>
        </div>

        <!-- Di bagian alert info -->
<div class="alert alert-info">
    <small>
        <i class="bi bi-info-circle me-1"></i>
        <strong>Informasi Penggunaan:</strong><br>
        • Hanya timbangan dengan kondisi <strong>Baik</strong> dan berada di <strong>Lab</strong> yang bisa digunakan<br>
        • <strong>Timbangan yang baru selesai perbaikan akan otomatis tersedia di Lab</strong><br>
        • Setelah dicatat, timbangan akan berpindah status ke line tujuan dengan status "Masih Digunakan"<br>
        • Untuk menggunakan timbangan yang sama lagi, buat data penggunaan baru setelah perbaikan selesai
    </small>
</div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan Penggunaan
        </button>
    </div>
</form>