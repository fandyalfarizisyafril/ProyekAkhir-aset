<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penghapusan_aset', function (Blueprint $table) {
            $table->decimal('nilai_perolehan', 15, 2)->nullable()->after('bidang_id');
            $table->decimal('beban_penyusutan', 15, 2)->nullable()->after('nilai_perolehan');
            $table->unsignedSmallInteger('tahun_penyusutan')->nullable()->after('beban_penyusutan');
        });

        DB::table('penghapusan_aset')
            ->where('jenis_aset', 'register')
            ->whereNotNull('aset_register_id')
            ->orderBy('id')
            ->chunkById(100, function ($deletions): void {
                foreach ($deletions as $deletion) {
                    $asset = DB::table('aset_register')
                        ->select('nilai')
                        ->where('id', $deletion->aset_register_id)
                        ->first();

                    if (! $asset) {
                        continue;
                    }

                    $depreciationQuery = DB::table('penyusutan_aset')
                        ->where('aset_register_id', $deletion->aset_register_id);
                    $deletionYear = (int) substr((string) $deletion->tanggal_penghapusan, 0, 4);

                    if ($deletionYear > 0) {
                        $depreciationQuery->where('tahun', '<=', $deletionYear);
                    }

                    $depreciation = $depreciationQuery
                        ->orderByDesc('tahun')
                        ->orderByDesc('id')
                        ->first();

                    DB::table('penghapusan_aset')
                        ->where('id', $deletion->id)
                        ->update([
                            'nilai_perolehan' => $asset->nilai,
                            'beban_penyusutan' => $depreciation?->beban_penyusutan,
                            'tahun_penyusutan' => $depreciation?->tahun,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penghapusan_aset', function (Blueprint $table) {
            $table->dropColumn(['nilai_perolehan', 'beban_penyusutan', 'tahun_penyusutan']);
        });
    }
};
