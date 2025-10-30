<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_pengembalian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timbangan_id')->constrained('timbangan')->onDelete('cascade');
            $table->string('dari_lokasi');
            $table->enum('alasan', ['Rusak', 'Tidak Akurat', 'Tidak Dibutuhkan', 'Lainnya']);
            $table->text('deskripsi_masalah')->nullable();
            $table->date('tanggal_dikembalikan');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_pengembalian');
    }
};