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
        Schema::create('riwayat_kondisi_smki', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_smki_id')->constrained('aset_smki')->cascadeOnDelete();
            $table->string('keadaan_lama');
            $table->string('keadaan_baru');
            $table->text('catatan')->nullable();
            $table->string('foto_path')->nullable();
            $table->foreignId('diupdate_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_kondisi_smki');
    }
};
