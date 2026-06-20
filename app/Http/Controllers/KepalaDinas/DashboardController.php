<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PenghapusanAset;
use App\Models\PenyusutanAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard pimpinan berbasis agregasi data aset terverifikasi.
     */
    public function index(Request $request): View
    {
        $filters = [
            'tahun' => (int) $request->input('tahun', now()->year),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
        ];

        $registerBase = $this->applyBidangFilter($this->verifiedRegisterQuery(), $filters);
        $smkiBase = $this->applyBidangFilter($this->verifiedSmkiQuery(), $filters);

        $registerQuery = $this->applyAssetFilters(clone $registerBase, 'register', $filters);
        $smkiQuery = $this->applyAssetFilters(clone $smkiBase, 'smki', $filters);

        $totalRegister = (clone $registerQuery)->count();
        $totalSmki = (clone $smkiQuery)->count();
        $totalAssets = $totalRegister + $totalSmki;

        $goodCount = $this->countByCondition($registerQuery, $smkiQuery, 'Baik');
        $lightDamageCount = $this->countByCondition($registerQuery, $smkiQuery, 'Rusak Ringan');
        $heavyDamageCount = $this->countByCondition($registerQuery, $smkiQuery, 'Rusak Berat');
        $damagedCount = $lightDamageCount + $heavyDamageCount;

        $registerAssetIds = (clone $registerQuery)->pluck('id');
        $totalRegisterValue = (float) (clone $registerQuery)->sum('nilai');
        $depreciationSummary = $this->depreciationSummary($registerAssetIds, $totalRegisterValue, $filters['tahun']);

        $summary = [
            'totalAssets' => $totalAssets,
            'registerCount' => $totalRegister,
            'smkiCount' => $totalSmki,
            'goodCount' => $goodCount,
            'damagedCount' => $damagedCount,
            'heavyDamageCount' => $heavyDamageCount,
            'totalRegisterValue' => $totalRegisterValue,
            'depreciationExpense' => $depreciationSummary['expense'],
            'bookValue' => $depreciationSummary['bookValue'],
            'calculatedDepreciationCount' => $depreciationSummary['calculatedCount'],
            'deletedCount' => $this->deletedCount($filters),
        ];

        return view('pages.kepala-dinas.dashboard', [
            'filters' => $filters,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'yearOptions' => $this->yearOptions(),
            'categoryOptions' => $this->categoryOptions($registerBase, $smkiBase),
            'conditionOptions' => $this->conditionOptions($registerBase, $smkiBase),
            'summary' => $summary,
            'bidangStats' => $this->bidangStats($registerQuery, $smkiQuery),
            'conditionStats' => $this->conditionStats($goodCount, $lightDamageCount, $heavyDamageCount, $totalAssets),
            'assetTypeStats' => $this->assetTypeStats($totalRegister, $totalSmki, $totalAssets),
            'topRegisterAssets' => $this->topRegisterAssets($registerQuery, $filters['tahun']),
        ]);
    }

    private function verifiedRegisterQuery(): Builder
    {
        return AsetRegister::query()
            ->where('status_verifikasi', 'Terverifikasi')
            ->where(function (Builder $query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'Dihapus');
            });
    }

    private function verifiedSmkiQuery(): Builder
    {
        return AsetSmki::query()
            ->where('status_verifikasi', 'Terverifikasi')
            ->where(function (Builder $query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'Dihapus');
            });
    }

    private function applyBidangFilter(Builder $query, array $filters): Builder
    {
        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        return $query;
    }

    private function applyAssetFilters(Builder $query, string $type, array $filters): Builder
    {
        if ($filters['kategori'] !== 'Semua Kategori') {
            $query->where($type === 'register' ? 'kode_barang' : 'jenis_barang', $filters['kategori']);
        }

        if ($filters['kondisi'] !== 'Semua Kondisi') {
            $query->where($type === 'register' ? 'kondisi' : 'keadaan_barang', $filters['kondisi']);
        }

        return $query;
    }

    private function categoryOptions(Builder $registerBase, Builder $smkiBase): Collection
    {
        return (clone $registerBase)->whereNotNull('kode_barang')->distinct()->pluck('kode_barang')
            ->merge((clone $smkiBase)->whereNotNull('jenis_barang')->distinct()->pluck('jenis_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function conditionOptions(Builder $registerBase, Builder $smkiBase): Collection
    {
        return (clone $registerBase)->whereNotNull('kondisi')->distinct()->pluck('kondisi')
            ->merge((clone $smkiBase)->whereNotNull('keadaan_barang')->distinct()->pluck('keadaan_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function countByCondition(Builder $registerQuery, Builder $smkiQuery, string $condition): int
    {
        return (clone $registerQuery)->where('kondisi', $condition)->count()
            + (clone $smkiQuery)->where('keadaan_barang', $condition)->count();
    }

    private function depreciationSummary(Collection $registerAssetIds, float $totalRegisterValue, int $year): array
    {
        if ($registerAssetIds->isEmpty()) {
            return [
                'expense' => 0.0,
                'bookValue' => 0.0,
                'calculatedCount' => 0,
            ];
        }

        $depreciationRows = PenyusutanAset::where('tahun', $year)
            ->whereIn('aset_register_id', $registerAssetIds)
            ->get();
        $calculatedIds = $depreciationRows->pluck('aset_register_id')->unique();
        $uncalculatedValue = AsetRegister::whereIn('id', $registerAssetIds->diff($calculatedIds))->sum('nilai');

        return [
            'expense' => (float) $depreciationRows->sum('beban_penyusutan'),
            'bookValue' => (float) $depreciationRows->sum('nilai_akhir_tahun') + (float) $uncalculatedValue,
            'calculatedCount' => $calculatedIds->count(),
        ];
    }

    private function deletedCount(array $filters): int
    {
        $query = PenghapusanAset::query();

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['kategori'] !== 'Semua Kategori') {
            $query->where(function (Builder $query) use ($filters) {
                $query->whereHas('asetRegister', fn (Builder $assetQuery) => $assetQuery->where('kode_barang', $filters['kategori']))
                    ->orWhereHas('asetSmki', fn (Builder $assetQuery) => $assetQuery->where('jenis_barang', $filters['kategori']));
            });
        }

        return $query->count();
    }

    private function bidangStats(Builder $registerQuery, Builder $smkiQuery): Collection
    {
        $registerStats = (clone $registerQuery)
            ->selectRaw('bidang_id, count(*) as total, sum(nilai) as total_value')
            ->groupBy('bidang_id')
            ->get()
            ->keyBy('bidang_id');

        $smkiStats = (clone $smkiQuery)
            ->selectRaw('bidang_id, count(*) as total')
            ->groupBy('bidang_id')
            ->pluck('total', 'bidang_id');

        $combined = collect();

        foreach ($registerStats as $bidangId => $stat) {
            $combined[$bidangId] = [
                'count' => (int) $stat->total,
                'value' => (float) $stat->total_value,
            ];
        }

        foreach ($smkiStats as $bidangId => $total) {
            $current = $combined[$bidangId] ?? ['count' => 0, 'value' => 0.0];
            $current['count'] += (int) $total;
            $combined[$bidangId] = $current;
        }

        $bidangNames = Bidang::whereIn('id', $combined->keys()->filter()->values())
            ->pluck('nama_bidang', 'id');
        $max = max((int) $combined->pluck('count')->max(), 1);

        return $combined
            ->sortByDesc('count')
            ->take(8)
            ->map(fn (array $stat, string|int $bidangId) => [
                'name' => $bidangNames[$bidangId] ?? 'Tanpa Bidang',
                'count' => $stat['count'],
                'value' => $stat['value'],
                'percentage' => (int) round(($stat['count'] / $max) * 100),
            ])
            ->values();
    }

    private function conditionStats(int $goodCount, int $lightDamageCount, int $heavyDamageCount, int $totalAssets): Collection
    {
        $circumference = 188.4;
        $offset = 0.0;

        return collect([
            ['name' => 'Baik', 'count' => $goodCount, 'color' => '#10B981'],
            ['name' => 'Rusak Ringan', 'count' => $lightDamageCount, 'color' => '#F59E0B'],
            ['name' => 'Rusak Berat', 'count' => $heavyDamageCount, 'color' => '#EF4444'],
        ])->map(function (array $item) use ($totalAssets, $circumference, &$offset) {
            $length = $totalAssets > 0 ? ($item['count'] / $totalAssets) * $circumference : 0;
            $segment = [
                ...$item,
                'percent' => $totalAssets > 0 ? round(($item['count'] / $totalAssets) * 100, 1) : 0,
                'dasharray' => number_format($length, 2, '.', '') . ' ' . number_format($circumference, 2, '.', ''),
                'dashoffset' => number_format(-$offset, 2, '.', ''),
            ];
            $offset += $length;

            return $segment;
        });
    }

    private function assetTypeStats(int $totalRegister, int $totalSmki, int $totalAssets): Collection
    {
        return collect([
            [
                'name' => 'Aset Register',
                'count' => $totalRegister,
                'percentage' => $totalAssets > 0 ? (int) round(($totalRegister / $totalAssets) * 100) : 0,
                'color' => 'bg-[#0F3092]',
            ],
            [
                'name' => 'Aset SMKI',
                'count' => $totalSmki,
                'percentage' => $totalAssets > 0 ? (int) round(($totalSmki / $totalAssets) * 100) : 0,
                'color' => 'bg-sky-400',
            ],
        ]);
    }

    private function topRegisterAssets(Builder $registerQuery, int $year): Collection
    {
        return (clone $registerQuery)
            ->with(['bidang', 'penyusutan' => fn ($query) => $query->where('tahun', $year)])
            ->orderByDesc('nilai')
            ->take(5)
            ->get()
            ->map(function (AsetRegister $asset) {
                $depreciation = $asset->penyusutan->first();

                return (object) [
                    'name' => $asset->nama_aset,
                    'code' => $asset->kode_aset,
                    'category' => $asset->kode_barang,
                    'bidang' => $asset->bidang,
                    'value' => (float) $asset->nilai,
                    'book_value' => $depreciation ? (float) $depreciation->nilai_akhir_tahun : (float) $asset->nilai,
                    'condition' => $asset->kondisi,
                ];
            });
    }

    private function yearOptions(): Collection
    {
        return PenyusutanAset::query()
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->push(now()->year)
            ->unique()
            ->sortDesc()
            ->values();
    }
}
