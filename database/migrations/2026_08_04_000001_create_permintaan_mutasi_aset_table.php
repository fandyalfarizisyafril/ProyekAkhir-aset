<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_mutasi_aset', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_aset');
            $table->string('kategori_aset');
            $table->string('nama_kebutuhan');
            $table->string('lokasi_penggunaan');
            $table->text('spesifikasi')->nullable();
            $table->text('alasan');
            $table->string('status')->default('Menunggu Verifikasi');
            $table->date('tanggal_permintaan');
            $table->foreignId('bidang_peminta_id')->constrained('bidang')->cascadeOnDelete();
            $table->foreignId('diminta_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('mutasi_aset_id')->nullable()->constrained('mutasi_aset')->nullOnDelete();
            $table->text('catatan_super_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_mutasi_aset');
    }
};
