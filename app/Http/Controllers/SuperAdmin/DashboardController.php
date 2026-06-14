<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Super Admin.
     */
    public function index(Request $request): View
    {
        $filters = [
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
        ];

        $registerBase = $this->applyBidangFilter(AsetRegister::query(), $filters);
        $smkiBase = $this->applyBidangFilter(AsetSmki::query(), $filters);

        $registerQuery = $this->applyFilters(clone $registerBase, 'register', $filters);
        $smkiQuery = $this->applyFilters(clone $smkiBase, 'smki', $filters);

        $totalRegister = (clone $registerQuery)->count();
        $totalSmki = (clone $smkiQuery)->count();
        $totalAssets = $totalRegister + $totalSmki;

        $goodCount = $this->countByCondition($registerQuery, $smkiQuery, 'Baik');
        $lightDamageCount = $this->countByCondition($registerQuery, $smkiQuery, 'Rusak Ringan');
        $heavyDamageCount = $this->countByCondition($registerQuery, $smkiQuery, 'Rusak Berat');
        $damagedCount = $lightDamageCount + $heavyDamageCount;

        $summary = [
            'totalAssets' => $totalAssets,
            'goodCount' => $goodCount,
            'damagedCount' => $damagedCount,
            'heavyDamageCount' => $heavyDamageCount,
            'registerCount' => $totalRegister,
            'smkiCount' => $totalSmki,
            'verifiedCount' => (clone $registerQuery)->where('status_verifikasi', 'Terverifikasi')->count()
                + (clone $smkiQuery)->where('status_verifikasi', 'Terverifikasi')->count(),
            'pendingCount' => (clone $registerQuery)->where('status_verifikasi', 'Perlu Verifikasi')->count()
                + (clone $smkiQuery)->where('status_verifikasi', 'Perlu Verifikasi')->count(),
            'borrowedCount' => (clone $registerQuery)->where('status', 'Dipinjam')->count()
                + (clone $smkiQuery)->where('status', 'Dipinjam')->count(),
            'totalRegisterValue' => (float) (clone $registerQuery)->sum('nilai'),
        ];

        return view('pages.super-admin.dashboard', [
            'filters' => $filters,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'categoryOptions' => $this->categoryOptions($registerBase, $smkiBase),
            'conditionOptions' => $this->conditionOptions($registerBase, $smkiBase),
            'summary' => $summary,
            'bidangStats' => $this->bidangStats($registerQuery, $smkiQuery),
            'conditionStats' => $this->conditionStats($goodCount, $lightDamageCount, $heavyDamageCount, $totalAssets),
            'assetTypeStats' => $this->assetTypeStats($totalRegister, $totalSmki, $totalAssets),
        ]);
    }

    private function applyBidangFilter(Builder $query, array $filters): Builder
    {
        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        return $query;
    }

    private function applyFilters(Builder $query, string $type, array $filters): Builder
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

    private function bidangStats(Builder $registerQuery, Builder $smkiQuery): Collection
    {
        $registerStats = (clone $registerQuery)
            ->selectRaw('bidang_id, count(*) as total')
            ->groupBy('bidang_id')
            ->pluck('total', 'bidang_id');

        $smkiStats = (clone $smkiQuery)
            ->selectRaw('bidang_id, count(*) as total')
            ->groupBy('bidang_id')
            ->pluck('total', 'bidang_id');

        $combined = collect();

        foreach ($registerStats as $bidangId => $total) {
            $combined[$bidangId] = ($combined[$bidangId] ?? 0) + (int) $total;
        }

        foreach ($smkiStats as $bidangId => $total) {
            $combined[$bidangId] = ($combined[$bidangId] ?? 0) + (int) $total;
        }

        $bidangNames = Bidang::whereIn('id', $combined->keys()->filter()->values())
            ->pluck('nama_bidang', 'id');
        $max = max((int) $combined->max(), 1);

        return $combined
            ->sortDesc()
            ->take(8)
            ->map(fn (int $count, string|int $bidangId) => [
                'name' => $bidangNames[$bidangId] ?? 'Tanpa Bidang',
                'count' => $count,
                'percentage' => (int) round(($count / $max) * 100),
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
}
