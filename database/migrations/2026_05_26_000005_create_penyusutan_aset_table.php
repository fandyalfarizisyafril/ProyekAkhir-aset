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
        Schema::create('penyusutan_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset_register_id')->constrained('aset_register')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('nilai_awal_tahun', 15, 2);
            $table->decimal('beban_penyusutan', 15, 2);
            $table->decimal('nilai_akhir_tahun', 15, 2);
            $table->string('metode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyusutan_aset');
    }
};
