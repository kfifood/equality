<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('timbangan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_asset')->unique();
            $table->string('merk_tipe_no_seri');
            $table->date('tanggal_datang');
            $table->date('tanggal_pemakaian')->nullable();
            $table->date('tanggal_kerusakan')->nullable();
            $table->text('keluhan')->nullable();
            $table->text('perbaikan')->nullable();
            $table->text('perbaikan_eksternal')->nullable();
            $table->date('tanggal_rilis')->nullable();
            $table->string('status_line')->nullable();
            $table->enum('kondisi_saat_ini', ['Baik', 'Rusak', 'Dalam Perbaikan'])->default('Baik');
            $table->string('lokasi_saat_ini');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('timbangan');
    }
};