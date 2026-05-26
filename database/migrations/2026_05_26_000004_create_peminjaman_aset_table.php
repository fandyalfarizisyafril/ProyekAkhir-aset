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
        Schema::create('peminjaman_aset', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_aset');
            $table->foreignId('aset_register_id')->nullable()->constrained('aset_register')->nullOnDelete();
            $table->foreignId('aset_smki_id')->nullable()->constrained('aset_smki')->nullOnDelete();
            $table->foreignId('peminjam_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_rencana_kembali');
            $table->date('tanggal_kembali')->nullable();
            $table->text('keperluan');
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_aset');
    }
};
