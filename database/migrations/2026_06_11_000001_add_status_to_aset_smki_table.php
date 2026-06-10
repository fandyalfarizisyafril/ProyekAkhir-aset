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
        if (! Schema::hasColumn('aset_smki', 'status')) {
            Schema::table('aset_smki', function (Blueprint $table) {
                $table->string('status')->default('Tersedia')->after('penanggung_jawab');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('aset_smki', 'status')) {
            Schema::table('aset_smki', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
