<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('aset_register', 'tanggal_perolehan')) {
            Schema::table('aset_register', function (Blueprint $table): void {
                $table->date('tanggal_perolehan')->nullable()->after('nilai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('aset_register', 'tanggal_perolehan')) {
            Schema::table('aset_register', function (Blueprint $table): void {
                $table->dropColumn('tanggal_perolehan');
            });
        }
    }
};
