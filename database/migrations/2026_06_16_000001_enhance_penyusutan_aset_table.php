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
            $table->unsignedInteger('umur_manfaat_tahun')->default(5)->after('tahun');
            $table->decimal('nilai_residu', 15, 2)->default(0)->after('nilai_awal_tahun');
            $table->unique(['aset_register_id', 'tahun'], 'penyusutan_aset_aset_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyusutan_aset', function (Blueprint $table) {
            $table->dropUnique('penyusutan_aset_aset_tahun_unique');
            $table->dropColumn(['umur_manfaat_tahun', 'nilai_residu']);
        });
    }
};
