<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\PeminjamanAset;
use App\Models\PenghapusanAset;
use App\Models\PermintaanMutasiAset;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'tahun' => $request->input('tahun', 'Semua Tahun'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
        ];

        $registerBase = $this->applyBidangFilter(AsetRegister::notDeleted(), $filters);
        $smkiBase = $this->applyBidangFilter(AsetSmki::notDeleted(), $filters);
        $verifiedRegisterBase = $this->verifiedInventory(clone $registerBase);
        $verifiedSmkiBase = $this->verifiedInventory(clone $smkiBase);

        $registerQuery = $this->applyFilters(clone $verifiedRegisterBase, 'register', $filters);
        $smkiQuery = $this->applyFilters(clone $verifiedSmkiBase, 'smki', $filters);
        $pendingRegisterQuery = $this->applyFilters(clone $registerBase, 'register', $filters);
        $pendingSmkiQuery = $this->applyFilters(clone $smkiBase, 'smki', $filters);
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
            'verifiedCount' => $totalAssets,
            'pendingCount' => (clone $pendingRegisterQuery)->where('status_verifikasi', 'Perlu Verifikasi')->count()
                + (clone $pendingSmkiQuery)->where('status_verifikasi', 'Perlu Verifikasi')->count(),
            'borrowedCount' => (clone $registerQuery)->where('status', 'Dipinjam')->count()
                + (clone $smkiQuery)->where('status', 'Dipinjam')->count(),
            'totalRegisterValue' => (float) (clone $registerQuery)->sum('nilai'),
        ];

        $userSummary = [
            'totalUsers' => User::count(),
            'superAdminCount' => User::where('role', 'Super Admin')->count(),
            'suspendedCount' => User::where('status', 'Ditangguhkan')->count(),
        ];

        return view('pages.super-admin.dashboard', [
            'filters' => $filters,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'yearOptions' => $this->yearOptions($verifiedRegisterBase, $verifiedSmkiBase),
            'conditionOptions' => $this->conditionOptions($verifiedRegisterBase, $verifiedSmkiBase),
            'summary' => $summary,
            'bidangStats' => $this->bidangStats($registerQuery, $smkiQuery),
            'conditionStats' => $this->conditionStats($goodCount, $lightDamageCount, $heavyDamageCount, $totalAssets),
            'assetTypeStats' => $this->assetTypeStats($totalRegister, $totalSmki, $totalAssets),
            'userSummary' => $userSummary,
            'deletionSummary' => $this->deletionSummary($filters),
            'pendingVerificationAssets' => $this->pendingVerificationAssets($pendingRegisterQuery, $pendingSmkiQuery),
            'pendingMutationCount' => (clone $this->pendingMutationQuery($filters))->count(),
            'pendingMutationRequests' => $this->pendingMutationRequests($filters),
            'pendingMutationDemandCount' => (clone $this->pendingMutationDemandQuery($filters))->count(),
            'pendingMutationDemandRequests' => $this->pendingMutationDemandRequests($filters),
            'pendingLoanCount' => (clone $this->pendingLoanQuery($filters))->count(),
            'pendingLoanRequests' => $this->pendingLoanRequests($filters),
            'recentActivities' => $this->recentActivities($activityRegisterQuery, $activitySmkiQuery, $filters),
            'priorityIssueAssets' => $this->priorityIssueAssets($registerQuery, $smkiQuery),
        ]);
    }

    private function verifiedInventory(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'Terverifikasi');
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
        if ($filters['tahun'] !== 'Semua Tahun') {
            $query->whereYear('created_at', (int) $filters['tahun']);
        }

        if ($filters['kondisi'] !== 'Semua Kondisi') {
            $query->where($type === 'register' ? 'kondisi' : 'keadaan_barang', $filters['kondisi']);
        }

        return $query;
    }

    private function yearOptions(Builder $registerBase, Builder $smkiBase): Collection
    {
        return (clone $registerBase)->whereNotNull('created_at')->pluck('created_at')
            ->merge((clone $smkiBase)->whereNotNull('created_at')->pluck('created_at'))
            ->map(fn ($date) => $date?->year)
            ->filter()
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

    private function deletionSummary(array $filters): array
    {
        $query = PenghapusanAset::query()
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('tanggal_penghapusan', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', fn (Builder $query) => $query->where('bidang_id', $filters['bidang_id']));

        return [
            'total' => (clone $query)->count(),
            'registerCount' => (clone $query)->where('jenis_aset', 'register')->count(),
            'smkiCount' => (clone $query)->where('jenis_aset', 'smki')->count(),
        ];
    }

    private function pendingVerificationAssets(Builder $registerQuery, Builder $smkiQuery): Collection
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
                'bidang' => $asset->bidang,
                'inputter' => $asset->inputter,
                'verifier' => $asset->verifier,
                'created_at' => $asset->created_at,
                'verified_at' => $asset->diverifikasi_oleh ? $asset->updated_at : null,
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
                'bidang' => $asset->bidang,
                'inputter' => $asset->inputter,
                'verifier' => $asset->verifier,
                'created_at' => $asset->created_at,
                'verified_at' => $asset->diverifikasi_oleh ? $asset->updated_at : null,
            ]);

        return $registerAssets
            ->toBase()
            ->merge($smkiAssets)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();
    }

    private function pendingMutationQuery(array $filters): Builder
    {
        return MutasiAset::query()
            ->where('status', 'Menunggu Verifikasi')
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('created_at', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', function (Builder $query) use ($filters) {
                $query->where(function (Builder $query) use ($filters) {
                    $query->where('bidang_asal_id', $filters['bidang_id'])
                        ->orWhere('bidang_tujuan_id', $filters['bidang_id']);
                });
            });
    }

    private function pendingMutationRequests(array $filters): Collection
    {
        return $this->pendingMutationQuery($filters)
            ->with(['asetRegister', 'asetSmki', 'bidangAsal', 'bidangTujuan', 'pemohon'])
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
                    'bidang_asal' => $mutasi->bidangAsal,
                    'bidang_tujuan' => $mutasi->bidangTujuan,
                    'pemohon' => $mutasi->pemohon,
                    'tanggal_mutasi' => $mutasi->tanggal_mutasi,
                    'created_at' => $mutasi->created_at,
                ];
            });
    }

    private function pendingMutationDemandQuery(array $filters): Builder
    {
        return PermintaanMutasiAset::query()
            ->where('status', 'Menunggu Verifikasi')
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('created_at', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', fn (Builder $query) => $query->where('bidang_peminta_id', $filters['bidang_id']));
    }

    private function pendingMutationDemandRequests(array $filters): Collection
    {
        return $this->pendingMutationDemandQuery($filters)
            ->with(['bidangPeminta', 'peminta'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (PermintaanMutasiAset $request) => (object) [
                'id' => $request->id,
                'type_label' => strtoupper($request->jenis_aset),
                'name' => $request->nama_kebutuhan,
                'category' => $request->kategori_aset,
                'location' => $request->lokasi_penggunaan,
                'bidang' => $request->bidangPeminta,
                'requester' => $request->peminta,
                'tanggal_permintaan' => $request->tanggal_permintaan,
                'created_at' => $request->created_at,
            ]);
    }

    private function pendingLoanQuery(array $filters): Builder
    {
        return PeminjamanAset::query()
            ->where('status', 'Menunggu Verifikasi')
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('created_at', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', function (Builder $query) use ($filters) {
                $query->where(function (Builder $query) use ($filters) {
                    $query->where('bidang_asal_id', $filters['bidang_id'])
                        ->orWhereHas('asetRegister', fn (Builder $assetQuery) => $assetQuery->where('bidang_id', $filters['bidang_id']))
                        ->orWhereHas('asetSmki', fn (Builder $assetQuery) => $assetQuery->where('bidang_id', $filters['bidang_id']))
                        ->orWhereHas('peminjam', fn (Builder $userQuery) => $userQuery->where('bidang_id', $filters['bidang_id']));
                });
            });
    }

    private function pendingLoanRequests(array $filters): Collection
    {
        return $this->pendingLoanQuery($filters)
            ->with(['asetRegister.bidang', 'asetSmki.bidang', 'bidangAsal', 'peminjam'])
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
                    'purpose' => $loan->keperluan,
                    'tanggal_pinjam' => $loan->tanggal_pinjam ? Carbon::parse($loan->tanggal_pinjam) : null,
                    'tanggal_rencana_kembali' => $loan->tanggal_rencana_kembali ? Carbon::parse($loan->tanggal_rencana_kembali) : null,
                    'created_at' => $loan->created_at,
                ];
            });
    }

    private function recentActivities(Builder $registerQuery, Builder $smkiQuery, array $filters): Collection
    {
        $registerActivities = (clone $registerQuery)
            ->with(['bidang', 'verifier'])
            ->whereIn('status_verifikasi', ['Terverifikasi', 'Ditolak'])
            ->whereNotNull('diverifikasi_oleh')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (AsetRegister $asset) => (object) [
                'title' => $asset->status_verifikasi === 'Terverifikasi' ? 'Aset Register diverifikasi' : 'Aset Register ditolak',
                'description' => $asset->nama_aset . ' - ' . $asset->kode_aset,
                'meta' => $asset->bidang->nama_bidang ?? 'Tanpa Bidang',
                'actor' => $asset->verifier->nama ?? $asset->verifier->name ?? 'Super Admin',
                'happened_at' => $asset->updated_at,
                'tone' => $asset->status_verifikasi === 'Terverifikasi' ? 'success' : 'danger',
                'url' => route('super-admin.verifikasi-aset.show', ['register', $asset->id]),
            ]);

        $smkiActivities = (clone $smkiQuery)
            ->with(['bidang', 'verifier'])
            ->whereIn('status_verifikasi', ['Terverifikasi', 'Ditolak'])
            ->whereNotNull('diverifikasi_oleh')
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn (AsetSmki $asset) => (object) [
                'title' => $asset->status_verifikasi === 'Terverifikasi' ? 'Aset SMKI diverifikasi' : 'Aset SMKI ditolak',
                'description' => $asset->merk_model . ' - ' . $asset->nomor_kode_barang,
                'meta' => $asset->bidang->nama_bidang ?? 'Tanpa Bidang',
                'actor' => $asset->verifier->nama ?? $asset->verifier->name ?? 'Super Admin',
                'happened_at' => $asset->updated_at,
                'tone' => $asset->status_verifikasi === 'Terverifikasi' ? 'success' : 'danger',
                'url' => route('super-admin.verifikasi-aset.show', ['smki', $asset->id]),
            ]);

        return $registerActivities
            ->toBase()
            ->merge($smkiActivities)
            ->merge($this->recentMutationActivities($filters))
            ->merge($this->recentLoanActivities($filters))
            ->merge($this->recentDeletionActivities($filters))
            ->filter(fn ($activity) => $activity->happened_at !== null)
            ->sortByDesc('happened_at')
            ->take(3)
            ->values();
    }

    private function recentMutationActivities(array $filters): Collection
    {
        return MutasiAset::with(['asetRegister', 'asetSmki', 'bidangAsal', 'bidangTujuan', 'penyetuju'])
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->whereNotNull('disetujui_oleh')
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('updated_at', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', function (Builder $query) use ($filters) {
                $query->where(function (Builder $query) use ($filters) {
                    $query->where('bidang_asal_id', $filters['bidang_id'])
                        ->orWhere('bidang_tujuan_id', $filters['bidang_id']);
                });
            })
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
                    'meta' => ($mutation->bidangAsal->nama_bidang ?? '-') . ' ke ' . ($mutation->bidangTujuan->nama_bidang ?? '-'),
                    'actor' => $mutation->penyetuju->nama ?? $mutation->penyetuju->name ?? 'Super Admin',
                    'happened_at' => $mutation->updated_at,
                    'tone' => $mutation->status === 'Disetujui' ? 'success' : 'danger',
                    'url' => route('super-admin.verifikasi-mutasi.show', $mutation->id),
                ];
            });
    }

    private function recentLoanActivities(array $filters): Collection
    {
        return PeminjamanAset::with(['asetRegister.bidang', 'asetSmki.bidang', 'bidangAsal', 'peminjam', 'penyetuju'])
            ->whereIn('status', ['Disetujui', 'Ditolak'])
            ->whereNotNull('disetujui_oleh')
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('updated_at', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', function (Builder $query) use ($filters) {
                $query->where(function (Builder $query) use ($filters) {
                    $query->where('bidang_asal_id', $filters['bidang_id'])
                        ->orWhereHas('asetRegister', fn (Builder $assetQuery) => $assetQuery->where('bidang_id', $filters['bidang_id']))
                        ->orWhereHas('asetSmki', fn (Builder $assetQuery) => $assetQuery->where('bidang_id', $filters['bidang_id']))
                        ->orWhereHas('peminjam', fn (Builder $userQuery) => $userQuery->where('bidang_id', $filters['bidang_id']));
                });
            })
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(function (PeminjamanAset $loan) {
                $asset = $loan->jenis_aset === 'register' ? $loan->asetRegister : $loan->asetSmki;
                $assetBidang = $loan->bidangAsal
                    ?? ($loan->jenis_aset === 'register' ? $loan->asetRegister?->bidang : $loan->asetSmki?->bidang);
                $assetName = $loan->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
                $assetCode = $loan->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');
                $borrower = $loan->nama_peminjam ?: ($loan->peminjam->nama ?? $loan->peminjam->name ?? '-');

                return (object) [
                    'title' => $loan->status === 'Disetujui' ? 'Peminjaman aset disetujui' : 'Peminjaman aset ditolak',
                    'description' => $assetName . ' - ' . $assetCode,
                    'meta' => $borrower . ' / ' . ($assetBidang->nama_bidang ?? '-'),
                    'actor' => $loan->penyetuju->nama ?? $loan->penyetuju->name ?? 'Super Admin',
                    'happened_at' => $loan->updated_at,
                    'tone' => $loan->status === 'Disetujui' ? 'success' : 'danger',
                    'url' => route('super-admin.verifikasi-peminjaman.show', $loan->id),
                ];
            });
    }

    private function recentDeletionActivities(array $filters): Collection
    {
        return PenghapusanAset::with(['bidang', 'remover'])
            ->when($filters['tahun'] !== 'Semua Tahun', fn (Builder $query) => $query->whereYear('created_at', (int) $filters['tahun']))
            ->when($filters['bidang_id'] !== 'Semua Bidang', fn (Builder $query) => $query->where('bidang_id', $filters['bidang_id']))
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (PenghapusanAset $deletion) => (object) [
                'title' => 'Aset dihapus dari inventaris',
                'description' => $deletion->nama_aset . ' - ' . $deletion->kode_aset,
                'meta' => ($deletion->bidang->nama_bidang ?? 'Tanpa Bidang') . ' / ' . $deletion->metode_penghapusan,
                'actor' => $deletion->remover->nama ?? $deletion->remover->name ?? 'Super Admin',
                'happened_at' => $deletion->created_at,
                'tone' => 'neutral',
                'url' => route('super-admin.penghapusan-aset.index'),
            ]);
    }

    private function priorityIssueAssets(Builder $registerQuery, Builder $smkiQuery): Collection
    {
        $registerAssets = (clone $registerQuery)
            ->with('bidang')
            ->whereIn('kondisi', ['Rusak Berat', 'Rusak Ringan'])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn (AsetRegister $asset) => (object) [
                'type_label' => 'Register',
                'name' => $asset->nama_aset,
                'code' => $asset->kode_aset,
                'condition' => $asset->kondisi,
                'bidang' => $asset->bidang,
                'updated_at' => $asset->updated_at,
                'priority' => $asset->kondisi === 'Rusak Berat' ? 2 : 1,
                'url' => route('super-admin.verifikasi-aset.show', ['register', $asset->id]),
            ]);

        $smkiAssets = (clone $smkiQuery)
            ->with('bidang')
            ->whereIn('keadaan_barang', ['Rusak Berat', 'Rusak Ringan'])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn (AsetSmki $asset) => (object) [
                'type_label' => 'SMKI',
                'name' => $asset->merk_model,
                'code' => $asset->nomor_kode_barang,
                'condition' => $asset->keadaan_barang,
                'bidang' => $asset->bidang,
                'updated_at' => $asset->updated_at,
                'priority' => $asset->keadaan_barang === 'Rusak Berat' ? 2 : 1,
                'url' => route('super-admin.verifikasi-aset.show', ['smki', $asset->id]),
            ]);

        return $registerAssets
            ->toBase()
            ->merge($smkiAssets)
            ->sort(function ($first, $second) {
                if ($first->priority !== $second->priority) {
                    return $second->priority <=> $first->priority;
                }

                return ($second->updated_at?->timestamp ?? 0) <=> ($first->updated_at?->timestamp ?? 0);
            })
            ->take(3)
            ->values();
    }
}
