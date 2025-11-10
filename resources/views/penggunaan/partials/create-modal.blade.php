<div class="modal-header" style="background-color:white; color:#4361EE;">
    <h5 class="modal-title">
        <i class="bi bi-arrow-right-circle me-2"></i>Catat Penggunaan Alat
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="createPenggunaanForm" action="{{ route('penggunaan.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Pilih Alat <span class="text-danger">*</span></label>
                    <select name="timbangan_id" class="form-select" id="timbanganSelect" required>
                        <option value="">Pilih Alat</option>
                        @foreach($timbangan as $item)
                            <option value="{{ $item->id }}" 
                                {{ $selectedTimbangan && $selectedTimbangan->id == $item->id ? 'selected' : '' }}
                                data-lokasi="{{ $item->status_line ? $item->status_line : 'Lab' }}">
                                {{ $item->kode_asset }} - {{ $item->merk_tipe_no_seri }} 
                                @if($item->status_line)
                                    (Sedang di {{ $item->status_line }})
                                @else
                                    (Lab)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text" id="lokasiInfo">
                        <!-- Info lokasi akan di-update via JavaScript -->
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

        <!-- Update alert info -->
        <div class="alert alert-info">
            <small>
                <i class="bi bi-info-circle me-1"></i>
                <strong>Informasi Penggunaan:</strong><br>
                • Hanya timbangan dengan kondisi <strong>Baik</strong> yang bisa digunakan<br>
                • Timbangan yang sedang digunakan di line lain <strong>bisa dipindahkan</strong><br>
                • Status penggunaan sebelumnya otomatis berubah menjadi "Selesai"<br>
                • Timbangan akan berpindah ke line tujuan yang baru
            </small>
        </div>

        <!-- Alert warning untuk timbangan yang sedang digunakan -->
        <div class="alert alert-warning d-none" id="warningAlert">
            <small>
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Perhatian:</strong> Timbangan ini sedang digunakan di line lain. 
                Status penggunaan sebelumnya akan otomatis berubah menjadi "Selesai".
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

<script>
$(document).ready(function() {
    // Update info lokasi saat timbangan dipilih
    $('#timbanganSelect').change(function() {
        const selectedOption = $(this).find('option:selected');
        const lokasi = selectedOption.data('lokasi');
        
        if (lokasi) {
            $('#lokasiInfo').html('Lokasi saat ini: <strong>' + lokasi + '</strong>');
            
            // Tampilkan warning jika timbangan sedang digunakan di line
            if (lokasi !== 'Lab') {
                $('#warningAlert').removeClass('d-none');
            } else {
                $('#warningAlert').addClass('d-none');
            }
        } else {
            $('#lokasiInfo').html('Pilih timbangan untuk melihat info lokasi');
            $('#warningAlert').addClass('d-none');
        }
    });

    // Trigger change event saat modal terbuka
    $('#timbanganSelect').trigger('change');
});
</script>