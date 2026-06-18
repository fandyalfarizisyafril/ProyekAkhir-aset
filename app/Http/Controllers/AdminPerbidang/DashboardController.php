<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\MutasiAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard Admin Perbidang.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $bidangId = $user->bidang_id;
        $filters = [
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
        ];

        $registerBase = AsetRegister::query()->where('bidang_id', $bidangId);
        $smkiBase = AsetSmki::query()->where('bidang_id', $bidangId);

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

        return view('pages.admin-perbidang.dashboard', [
            'bidangName' => $user->bidang->nama_bidang ?? 'Bidang Anda',
            'filters' => $filters,
            'categoryOptions' => $this->categoryOptions($registerBase, $smkiBase),
            'conditionOptions' => $this->conditionOptions($registerBase, $smkiBase),
            'summary' => $summary,
            'categoryStats' => $this->categoryStats($registerQuery, $smkiQuery),
            'conditionStats' => $this->conditionStats($goodCount, $lightDamageCount, $heavyDamageCount, $totalAssets),
            'assetTypeStats' => $this->assetTypeStats($totalRegister, $totalSmki, $totalAssets),
            'recentInputAssets' => $this->recentInputAssets($registerQuery, $smkiQuery),
            'pendingMutationRequests' => $this->pendingMutationRequests($user->id),
        ]);
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

    private function categoryStats(Builder $registerQuery, Builder $smkiQuery): Collection
    {
        $registerStats = (clone $registerQuery)
            ->selectRaw('kode_barang as name, count(*) as total')
            ->groupBy('kode_barang')
            ->pluck('total', 'name');

        $smkiStats = (clone $smkiQuery)
            ->selectRaw('jenis_barang as name, count(*) as total')
            ->groupBy('jenis_barang')
            ->pluck('total', 'name');

        $combined = collect();

        foreach ($registerStats as $name => $total) {
            $combined[$name] = ($combined[$name] ?? 0) + (int) $total;
        }

        foreach ($smkiStats as $name => $total) {
            $combined[$name] = ($combined[$name] ?? 0) + (int) $total;
        }

        $max = max((int) $combined->max(), 1);

        return $combined
            ->sortDesc()
            ->take(8)
            ->map(fn (int $count, string $name) => [
                'name' => $name ?: 'Tanpa Kategori',
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

    private function recentInputAssets(Builder $registerQuery, Builder $smkiQuery): Collection
    {
        $registerAssets = (clone $registerQuery)
            ->with(['bidang', 'inputter', 'verifier'])
            ->where('status_verifikasi', 'Perlu Verifikasi')
            ->latest()
            ->get()
            ->map(fn (AsetRegister $asset) => (object) [
                'id' => $asset->id,
                'type' => 'register',
                'type_label' => 'Register',
                'name' => $asset->nama_aset,
                'code' => $asset->kode_aset,
                'category' => $asset->kode_barang,
                'status_verifikasi' => $asset->status_verifikasi,
                'inputter' => $asset->inputter,
                'verifier' => $asset->verifier,
                'created_at' => $asset->created_at,
                'verified_at' => $asset->diverifikasi_oleh ? $asset->updated_at : null,
                'detail_route' => route('admin-perbidang.data-aset-register.edit', $asset->id),
            ]);

        $smkiAssets = (clone $smkiQuery)
            ->with(['bidang', 'inputter', 'verifier'])
            ->where('status_verifikasi', 'Perlu Verifikasi')
            ->latest()
            ->get()
            ->map(fn (AsetSmki $asset) => (object) [
                'id' => $asset->id,
                'type' => 'smki',
                'type_label' => 'SMKI',
                'name' => $asset->merk_model,
                'code' => $asset->nomor_kode_barang,
                'category' => $asset->jenis_barang,
                'status_verifikasi' => $asset->status_verifikasi,
                'inputter' => $asset->inputter,
                'verifier' => $asset->verifier,
                'created_at' => $asset->created_at,
                'verified_at' => $asset->diverifikasi_oleh ? $asset->updated_at : null,
                'detail_route' => route('admin-perbidang.data-aset-smki.edit', $asset->id),
            ]);

        return $registerAssets
            ->toBase()
            ->merge($smkiAssets)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();
    }

    private function pendingMutationRequests(int $userId): Collection
    {
        return MutasiAset::with(['asetRegister', 'asetSmki', 'bidangTujuan'])
            ->where('diajukan_oleh', $userId)
            ->where('status', 'Menunggu Verifikasi')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (MutasiAset $mutasi) {
                $asset = $mutasi->jenis_aset === 'register' ? $mutasi->asetRegister : $mutasi->asetSmki;

                return (object) [
                    'id' => $mutasi->id,
                    'type_label' => strtoupper($mutasi->jenis_aset),
                    'asset_name' => $mutasi->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-'),
                    'asset_code' => $mutasi->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-'),
                    'bidang_tujuan' => $mutasi->bidangTujuan,
                    'tanggal_mutasi' => $mutasi->tanggal_mutasi,
                    'tanggal_rencana_pengembalian' => $mutasi->tanggal_rencana_pengembalian,
                    'created_at' => $mutasi->created_at,
                ];
            });
    }
}
