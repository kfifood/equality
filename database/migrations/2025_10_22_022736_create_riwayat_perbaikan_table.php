<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_perbaikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timbangan_id')->constrained('timbangan')->onDelete('cascade');
            $table->string('line_sebelumnya'); // Line tempat timbangan digunakan sebelum rusak
            $table->string('penggunaan_terakhir'); // Siapa yang terakhir menggunakan
            $table->text('deskripsi_keluhan'); // Fluktuasi, kalibrasi, dll
            $table->text('tindakan_perbaikan')->nullable(); // Deskripsi perbaikan yang dilakukan
            $table->text('perbaikan_eksternal')->nullable(); // Jika ada perbaikan eksternal
            $table->date('tanggal_masuk_lab');
            $table->date('tanggal_selesai_perbaikan')->nullable();
            $table->string('line_tujuan')->nullable(); // Line tempat akan diserahkan setelah perbaikan
            $table->enum('status_perbaikan', ['Masuk Lab', 'Dalam Perbaikan', 'Selesai', 'Dikirim Eksternal'])->default('Masuk Lab');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_perbaikan');
    }
};