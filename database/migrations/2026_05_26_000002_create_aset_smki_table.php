<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aset_smki', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kode_barang')->unique();
            $table->string('jenis_barang');
            $table->string('merk_model');
            $table->string('no_ser_model')->nullable();
            $table->string('ukuran')->nullable();
            $table->string('bahan')->nullable();
            $table->integer('tahun_pembuatan');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->string('keadaan_barang');
            $table->text('keterangan')->nullable();
            $table->foreignId('bidang_id')->nullable()->constrained('bidang')->nullOnDelete();
            $table->string('ruangan')->nullable();
            $table->string('penanggung_jawab');
            $table->string('qr_code_path')->nullable();
            $table->string('status_verifikasi');
            $table->foreignId('dinput_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_smki');
    }
};
