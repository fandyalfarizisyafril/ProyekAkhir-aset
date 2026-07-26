<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\MutasiAset;
use App\Models\PeminjamanAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'tahun' => $request->input('tahun', 'Semua Tahun'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
        ];

        $registerBase = AsetRegister::notDeleted()->where('bidang_id', $bidangId);
        $smkiBase = AsetSmki::notDeleted()->where('bidang_id', $bidangId);
        $activeRegisterBase = $this->withoutRejectedVerification(clone $registerBase);
        $activeSmkiBase = $this->withoutRejectedVerification(clone $smkiBase);

        $registerQuery = $this->applyFilters(clone $activeRegisterBase, 'register', $filters);
        $smkiQuery = $this->applyFilters(clone $activeSmkiBase, 'smki', $filters);
        $activityRegisterQuery = $this->applyFilters(clone $registerBase, 'register', $filters);
        $activitySmkiQuery = $this->applyFilters(clone $smkiBase, 'smki', $filters);

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
            'categoryOptions' => $this->categoryOptions($activeRegisterBase, $activeSmkiBase),
            'yearOptions' => $this->yearOptions($activeRegisterBase, $activeSmkiBase),
            'conditionOptions' => $this->conditionOptions($activeRegisterBase, $activeSmkiBase),
            'summary' => $summary,
            'categoryStats' => $this->categoryStats($registerQuery, $smkiQuery),
            'conditionStats' => $this->conditionStats($goodCount, $lightDamageCount, $heavyDamageCount, $totalAssets),
            'assetTypeStats' => $this->assetTypeStats($totalRegister, $totalSmki, $totalAssets),
            'recentInputAssets' => $this->recentInputAssets($registerQuery, $smkiQuery),
            'pendingMutationCount' => $this->pendingMutationCount($user->id),
            'pendingMutationRequests' => $this->pendingMutationRequests($user->id),
            'pendingLoanCount' => $this->pendingLoanCount($bidangId),
            'pendingLoanRequests' => $this->pendingLoanRequests($bidangId),
            'activeLoanRequests' => $this->activeLoanRequests($bidangId),
            'recentActivities' => $this->recentActivities($activityRegisterQuery, $activitySmkiQuery, $user->id, $bidangId),
        ]);
    }

    private function withoutRejectedVerification(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('status_verifikasi')
                ->orWhere('status_verifikasi', '!=', 'Ditolak');
        });
    }

    private function applyFilters(Builder $query, string $type, array $filters): Builder
    {
        if ($filters['kategori'] !== 'Semua Kategori') {
            $query->where($type === 'register' ? 'kode_barang' : 'jenis_barang', $filters['kategori']);
        }

        if ($filters['tahun'] !== 'Semua Tahun') {
            $query->whereYear('created_at', (int) $filters['tahun']);
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

    private function yearOptions(Builder $registerBase, Builder $smkiBase): Collection
    {
        return (clone $registerBase)->whereNotNull('created_at')->pluck('created_at')
            ->merge((clone $smkiBase)->whereNotNull('created_at')->pluck('created_at'))
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
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
                    'created_at' => $mutasi->created_at,
                ];
            });
    }

    private function pendingMutationCount(int $userId): int
    {
        return MutasiAset::where('diajukan_oleh', $userId)
            ->where('status', 'Menunggu Verifikasi')
            ->count();
    }

    private function pendingLoanRequests(int $bidangId): Collection
    {
        return PeminjamanAset::with(['asetRegister.bidang', 'asetSmki.bidang', 'bidangAsal', 'peminjam'])
            ->where('status', 'Menunggu Verifikasi')
            ->whereHas('peminjam', fn (Builder $query) => $query->where('bidang_id', $bidangId))
            ->latest()
            ->take(5)
            ->get()
            ->map(function (PeminjamanAset $loan) {
                $asset = $loan->jenis_aset === 'register' ? $loan->asetRegister : $loan->asetSmki;
                $assetBidang = $loan->jenis_aset === 'register'
                    ? ($loan->asetRegister?->bidang)
                    : ($loan->asetSmki?->bidang);

                return (object) [
                    'id' => $loan->id,
                    'type_label' => strtoupper($loan->jenis_aset),
                    'asset_name' => $loan->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-'),
                    'asset_code' => $loan->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-'),
                    'bidang' => $loan->bidangAsal ?? $assetBidang,
                    'borrower_name' => $loan->nama_peminjam ?: ($loan->peminjam->nama ?? $loan->peminjam->name ?? '-'),
                    'tanggal_pinjam' => $loan->tanggal_pinjam ? Carbon::parse($loan->tanggal_pinjam) : null,
                    'tanggal_rencana_kembali' => $loan->tanggal_rencana_kembali ? Carbon::parse($loan->tanggal_rencana_kembali) : null,
                    'created_at' => $loan->created_at,
                ];
            });
    }

    private function pendingLoanCount(int $bidangId): int
    {
        return PeminjamanAset::where('status', 'Menunggu Verifikasi')
            ->whereHas('peminjam', fn (Builder $query) => $query->where('bidang_id', $bidangId))
            ->count();
    }

    private function activeLoanRequests(int $bidangId): Collection
    {
        return PeminjamanAset::with(['asetRegister.bidang', 'asetSmki.bidang', 'bidangAsal', 'peminjam'])
            ->where('status', 'Disetujui')
            ->whereNull('tanggal_kembali')
            ->whereHas('peminjam', fn (Builder $query) => $query->where('bidang_id', $bidangId))
            ->oldest('tanggal_rencana_kembali')
            ->take(5)
            ->get()
            ->map(function (PeminjamanAset $loan) {
                $asset = $loan->jenis_aset === 'register' ? $loan->asetRegister : $loan->asetSmki;
                $assetBidang = $loan->jenis_aset === 'register'
                    ? ($loan->asetRegister?->bidang)
                    : ($loan->asetSmki?->bidang);

                return (object) [
                    'id' => $loan->id,
                    'type_label' => strtoupper($loan->jenis_aset),
                    'asset_name' => $loan->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-'),
                    'asset_code' => $loan->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-'),
                    'bidang' => $loan->bidangAsal ?? $assetBidang,
                    'borrower_name' => $loan->nama_peminjam ?: ($loan->peminjam->nama ?? $loan->peminjam->name ?? '-'),
                    'tanggal_pinjam' => $loan->tanggal_pinjam ? Carbon::parse($loan->tanggal_pinjam) : null,
                    'tanggal_rencana_kembali' => $loan->tanggal_rencana_kembali ? Carbon::parse($loan->tanggal_rencana_kembali) : null,
                    'created_at' => $loan->created_at,
                ];
            });
    }

    private function recentActivities(Builder $registerQuery, Builder $smkiQuery, int $userId, int $bidangId): Collection
    {
        $registerActivities = (clone $registerQuery)
            ->with('verifier')
            ->whereIn('status_verifikasi', ['Terverifikasi', 'Ditolak'])
            ->whereNotNull('diverifikasi_oleh')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (AsetRegister $asset) => (object) [
                'title' => $asset->status_verifikasi === 'Terverifikasi' ? 'Aset Register diverifikasi' : 'Aset Register ditolak',
                'description' => $asset->nama_aset . ' - ' . $asset->kode_aset,
                'meta' => $asset->kode_barang ?: 'Register',
                'actor' => $asset->verifier->nama ?? $asset->verifier->name ?? 'Super Admin',
                'happened_at' => $asset->updated_at,
                'tone' => $asset->status_verifikasi === 'Terverifikasi' ? 'success' : 'danger',
                'url' => route('admin-perbidang.data-aset-register.edit', $asset->id),
            ]);

        $smkiActivities = (clone $smkiQuery)
            ->with('verifier')
            ->whereIn('status_verifikasi', ['Terverifikasi', 'Ditolak'])
            ->whereNotNull('diverifikasi_oleh')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (AsetSmki $asset) => (object) [
                'title' => $asset->status_verifikasi === 'Terverifikasi' ? 'Aset SMKI diverifikasi' : 'Aset SMKI ditolak',
                'description' => $asset->merk_model . ' - ' . $asset->nomor_kode_barang,
                'meta' => $asset->jenis_barang ?: 'SMKI',
                'actor' => $asset->verifier->nama ?? $asset->verifier->name ?? 'Super Admin',
                'happened_at' => $asset->updated_at,
                'tone' => $asset->status_verifikasi === 'Terverifikasi' ? 'success' : 'danger',
                'url' => route('admin-perbidang.data-aset-smki.edit', $asset->id),
            ]);

        return $registerActivities
            ->toBase()
            ->merge($smkiActivities)
            ->merge($this->recentMutationActivities($userId))
            ->merge($this->recentLoanActivities($bidangId))
            ->filter(fn ($activity) => $activity->happened_at !== null)
            ->sortByDesc('happened_at')
            ->take(3)
            ->values();
    }

    private function recentMutationActivities(int $userId): Collection
    {
        return MutasiAset::with(['asetRegister', 'asetSmki', 'bidangTujuan', 'penyetuju'])
            ->where('diajukan_oleh', $userId)
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->whereNotNull('disetujui_oleh')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(function (MutasiAset $mutation) {
                $asset = $mutation->jenis_aset === 'register' ? $mutation->asetRegister : $mutation->asetSmki;
                $assetName = $mutation->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
                $assetCode = $mutation->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');

                return (object) [
                    'title' => $mutation->status === 'Disetujui' ? 'Mutasi aset disetujui' : 'Mutasi aset ditolak',
                    'description' => $assetName . ' - ' . $assetCode,
                    'meta' => 'Tujuan ' . ($mutation->bidangTujuan->nama_bidang ?? '-'),
                    'actor' => $mutation->penyetuju->nama ?? $mutation->penyetuju->name ?? 'Super Admin',
                    'happened_at' => $mutation->updated_at,
                    'tone' => $mutation->status === 'Disetujui' ? 'success' : 'danger',
                    'url' => route('admin-perbidang.mutasi-aset.show', $mutation->id),
                ];
            });
    }

    private function recentLoanActivities(int $bidangId): Collection
    {
        return PeminjamanAset::with(['asetRegister', 'asetSmki', 'peminjam', 'penyetuju'])
            ->whereIn('status', ['Disetujui', 'Ditolak', 'Dikembalikan'])
            ->whereHas('peminjam', fn (Builder $query) => $query->where('bidang_id', $bidangId))
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(function (PeminjamanAset $loan) {
                $asset = $loan->jenis_aset === 'register' ? $loan->asetRegister : $loan->asetSmki;
                $assetName = $loan->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
                $assetCode = $loan->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');
                $borrower = $loan->nama_peminjam ?: ($loan->peminjam->nama ?? $loan->peminjam->name ?? '-');

                return (object) [
                    'title' => match ($loan->status) {
                        'Disetujui' => 'Peminjaman aset disetujui',
                        'Ditolak' => 'Peminjaman aset ditolak',
                        default => 'Peminjaman aset dikembalikan',
                    },
                    'description' => $assetName . ' - ' . $assetCode,
                    'meta' => $borrower,
                    'actor' => $loan->status === 'Dikembalikan'
                        ? ($loan->peminjam->nama ?? $loan->peminjam->name ?? $borrower)
                        : ($loan->penyetuju->nama ?? $loan->penyetuju->name ?? 'Super Admin'),
                    'happened_at' => $loan->updated_at,
                    'tone' => $loan->status === 'Ditolak' ? 'danger' : 'success',
                    'url' => route('admin-perbidang.peminjaman-aset.show', $loan->id),
                ];
            });
    }
}
