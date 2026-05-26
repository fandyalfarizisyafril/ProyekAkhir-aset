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
        Schema::create('mutasi_aset', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_aset');
            $table->foreignId('aset_register_id')->nullable()->constrained('aset_register')->nullOnDelete();
            $table->foreignId('aset_smki_id')->nullable()->constrained('aset_smki')->nullOnDelete();
            $table->foreignId('bidang_asal_id')->constrained('bidang')->cascadeOnDelete();
            $table->foreignId('bidang_tujuan_id')->constrained('bidang')->cascadeOnDelete();
            $table->text('alasan');
            $table->string('status');
            $table->foreignId('diajukan_oleh')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mutasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_aset');
    }
};
