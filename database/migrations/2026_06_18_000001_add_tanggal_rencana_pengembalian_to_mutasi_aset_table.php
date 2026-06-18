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
        Schema::table('mutasi_aset', function (Blueprint $table) {
            $table->date('tanggal_rencana_pengembalian')->nullable()->after('tanggal_mutasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_aset', function (Blueprint $table) {
            $table->dropColumn('tanggal_rencana_pengembalian');
        });
    }
};
