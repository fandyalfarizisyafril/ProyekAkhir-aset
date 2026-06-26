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
        Schema::table('penyusutan_aset', function (Blueprint $table) {
            $table->foreignId('dihitung_oleh')
                ->nullable()
                ->after('metode')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('tanggal_hitung')->nullable()->after('dihitung_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyusutan_aset', function (Blueprint $table) {
            $table->dropForeign(['dihitung_oleh']);
            $table->dropColumn(['dihitung_oleh', 'tanggal_hitung']);
        });
    }
};
