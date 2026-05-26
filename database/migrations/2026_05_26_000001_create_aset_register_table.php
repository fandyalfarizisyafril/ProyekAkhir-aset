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
        Schema::create('aset_register', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique();
            $table->string('nama_aset');
            $table->string('kode_barang');
            $table->string('kode_urut_barang');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang')->nullOnDelete();
            $table->string('status_barang');
            $table->string('pemilik_aset');
            $table->string('pengguna');
            $table->string('lokasi_aset');
            $table->string('metode_pemusnahan')->nullable();
            $table->string('kerahasiaan');
            $table->string('kritikalitas');
            $table->decimal('nilai', 15, 2);
            $table->text('keterangan')->nullable();
            $table->string('kondisi');
            $table->string('status');
            $table->string('qr_code_path')->nullable();
            $table->string('status_verifikasi');
            $table->foreignId('dinput_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_register');
    }
};
