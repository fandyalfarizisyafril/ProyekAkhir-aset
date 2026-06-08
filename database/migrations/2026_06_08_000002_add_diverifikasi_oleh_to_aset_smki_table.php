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
        if (! Schema::hasColumn('aset_smki', 'diverifikasi_oleh')) {
            Schema::table('aset_smki', function (Blueprint $table) {
                $table->foreignId('diverifikasi_oleh')
                    ->nullable()
                    ->after('dinput_oleh')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('aset_smki', 'diverifikasi_oleh')) {
            Schema::table('aset_smki', function (Blueprint $table) {
                $table->dropConstrainedForeignId('diverifikasi_oleh');
            });
        }
    }
};
