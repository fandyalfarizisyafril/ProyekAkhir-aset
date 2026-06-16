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
        Schema::create('penghapusan_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_register_id')->nullable()->constrained('aset_register')->nullOnDelete();
            $table->foreignId('aset_smki_id')->nullable()->constrained('aset_smki')->nullOnDelete();
            $table->string('jenis_aset');
            $table->string('kode_aset');
            $table->string('nama_aset');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang')->nullOnDelete();
            $table->decimal('nilai_buku', 15, 2)->nullable();
            $table->date('tanggal_penghapusan');
            $table->string('metode_penghapusan');
            $table->text('alasan');
            $table->string('status_sebelum')->nullable();
            $table->foreignId('dihapus_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis_aset', 'tanggal_penghapusan']);
            $table->index(['bidang_id', 'tanggal_penghapusan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penghapusan_aset');
    }
};
