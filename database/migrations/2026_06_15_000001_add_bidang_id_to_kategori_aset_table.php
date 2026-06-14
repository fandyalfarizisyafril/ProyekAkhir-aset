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
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->foreignId('bidang_id')
                ->nullable()
                ->after('tipe')
                ->constrained('bidang')
                ->nullOnDelete();
        });

        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->dropUnique('kategori_aset_tipe_nama_kategori_unique');
            $table->unique(['tipe', 'nama_kategori', 'bidang_id'], 'kategori_aset_tipe_nama_bidang_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->dropUnique('kategori_aset_tipe_nama_bidang_unique');
            $table->unique(['tipe', 'nama_kategori'], 'kategori_aset_tipe_nama_kategori_unique');
        });

        Schema::table('kategori_aset', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bidang_id');
        });
    }
};
