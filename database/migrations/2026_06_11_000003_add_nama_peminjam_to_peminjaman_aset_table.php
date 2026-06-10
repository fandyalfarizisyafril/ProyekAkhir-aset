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
            $table->string('nama_peminjam')->nullable()->after('peminjam_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_aset', function (Blueprint $table) {
            $table->dropColumn('nama_peminjam');
        });
    }
};
