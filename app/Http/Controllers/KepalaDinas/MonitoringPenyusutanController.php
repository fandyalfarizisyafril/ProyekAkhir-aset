<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\PenyusutanAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MonitoringPenyusutanController extends Controller
{
    /**
     * Tampilkan ringkasan penyusutan aset Register secara read-only.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $assetQuery = $this->assetQuery($filters);
        $assetIds = (clone $assetQuery)->pluck('id');
        $depreciationQuery = PenyusutanAset::query()
            ->where('tahun', $filters['tahun'])
            ->whereIn('aset_register_id', $assetIds);
        $calculatedIds = (clone $depreciationQuery)->pluck('aset_register_id');
        $uncalculatedValue = (float) (clone $assetQuery)
            ->whereNotIn('id', $calculatedIds)
            ->sum('nilai');

        return view('pages.kepala-dinas.MonitoringPenyusutan.index', [
            'assets' => $assetQuery
                ->with([
                    'bidang',
                    'penyusutan' => fn ($query) => $query
                        ->where('tahun', $filters['tahun'])
                        ->with('calculator'),
                ])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $filters,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'categories' => $this->categoryOptions(),
            'years' => $this->yearOptions(),
            'summary' => [
                'totalAcquisitionValue' => (float) (clone $assetQuery)->sum('nilai'),
                'totalDepreciationExpense' => (float) (clone $depreciationQuery)->sum('beban_penyusutan'),
                'totalBookValue' => (float) (clone $depreciationQuery)->sum('nilai_akhir_tahun') + $uncalculatedValue,
                'assetCount' => $assetIds->count(),
                'calculatedCount' => $calculatedIds->unique()->count(),
            ],
        ]);
    }

    /**
     * Tampilkan riwayat penyusutan satu aset Register secara read-only.
     */
    public function show(Request $request, AsetRegister $asetRegister): View
    {
        abort_unless(
            $asetRegister->status_verifikasi === 'Terverifikasi' && $asetRegister->status !== 'Dihapus',
            404
        );

        $asetRegister->load('bidang');
        $history = $asetRegister->penyusutan()
            ->with('calculator')
            ->orderByDesc('tahun')
            ->paginate(10)
            ->withQueryString();

        return view('pages.kepala-dinas.MonitoringPenyusutan.show', [
            'asset' => $asetRegister,
            'history' => $history,
            'latestDepreciation' => $history->getCollection()->first(),
            'backFilters' => $request->only(['tahun', 'bidang_id', 'kategori', 'search']),
        ]);
    }

    private function filters(Request $request): array
    {
        $year = (int) $request->input('tahun', now()->year);

        if ($year < 2000 || $year > now()->year + 1) {
            $year = now()->year;
        }

        return [
            'tahun' => $year,
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'search' => $request->input('search'),
        ];
    }

    private function assetQuery(array $filters): Builder
    {
        $query = AsetRegister::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi');

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['kategori'] !== 'Semua Kategori') {
            $query->where('kode_barang', $filters['kategori']);
        }

        if ($filters['search']) {
            $query->where(function (Builder $query) use ($filters): void {
                $query->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('bidang', fn (Builder $bidangQuery) => $bidangQuery
                        ->where('nama_bidang', 'like', '%' . $filters['search'] . '%'));
            });
        }

        return $query;
    }

    private function categoryOptions(): Collection
    {
        return AsetRegister::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi')
            ->whereNotNull('kode_barang')
            ->distinct()
            ->orderBy('kode_barang')
            ->pluck('kode_barang');
    }

    private function yearOptions(): Collection
    {
        return PenyusutanAset::query()
            ->distinct()
            ->pluck('tahun')
            ->push(now()->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }
}
