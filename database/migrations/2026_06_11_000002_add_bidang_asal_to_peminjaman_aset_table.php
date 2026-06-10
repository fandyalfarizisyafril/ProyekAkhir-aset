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
        Schema::table('peminjaman_aset', function (Blueprint $table) {
            $table->foreignId('bidang_asal_id')
                ->nullable()
                ->after('aset_smki_id')
                ->constrained('bidang')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_aset', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bidang_asal_id');
        });
    }
};
