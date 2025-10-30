<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_penggunaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timbangan_id')->constrained('timbangan')->onDelete('cascade');
            $table->string('line_tujuan');
            $table->date('tanggal_pemakaian');
            $table->string('pic')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_penggunaan');
    }
};