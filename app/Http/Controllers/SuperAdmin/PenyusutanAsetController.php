<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\PenyusutanAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenyusutanAsetController extends Controller
{
    /**
     * Tampilkan daftar aset Register dan nilai penyusutannya.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $assetQuery = $this->assetQuery($filters)
            ->with([
                'bidang',
                'penyusutan' => fn ($query) => $query->where('tahun', $filters['tahun'])->with('calculator'),
            ])
            ->latest();

        $assetIds = (clone $assetQuery)->pluck('id');
        $depreciationQuery = PenyusutanAset::where('tahun', $filters['tahun'])
            ->whereIn('aset_register_id', $assetIds);
        $calculatedIds = (clone $depreciationQuery)->pluck('aset_register_id');
        $uncalculatedValue = (float) $this->assetQuery($filters)
            ->whereNotIn('id', $calculatedIds)
            ->sum('nilai');

        return view('pages.super-admin.PenyusutanAset.index', [
            'assets' => $assetQuery->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'categories' => $this->categoryOptions(),
            'filters' => $filters,
            'usefulLifePresets' => $this->usefulLifePresets(),
            'summary' => [
                'eligibleCount' => $assetIds->count(),
                'calculatedCount' => (clone $depreciationQuery)->count(),
                'uncalculatedCount' => max($assetIds->count() - (clone $depreciationQuery)->count(), 0),
                'totalAcquisitionValue' => (float) $this->assetQuery($filters)->sum('nilai'),
                'totalDepreciationExpense' => (float) (clone $depreciationQuery)->sum('beban_penyusutan'),
                'totalBookValue' => (float) (clone $depreciationQuery)->sum('nilai_akhir_tahun') + $uncalculatedValue,
            ],
        ]);
    }

    /**
     * Tampilkan halaman khusus untuk menghitung penyusutan secara massal.
     */
    public function bulk(Request $request): View
    {
        $filters = $this->filters($request);
        $assetQuery = $this->assetQuery($filters);
        $assetIds = (clone $assetQuery)->pluck('id');
        $depreciationQuery = PenyusutanAset::where('tahun', $filters['tahun'])
            ->whereIn('aset_register_id', $assetIds);
        $assets = (clone $assetQuery)
            ->with([
                'bidang',
                'penyusutan' => fn ($query) => $query->where('tahun', $filters['tahun'])->with('calculator'),
            ])
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('pages.super-admin.PenyusutanAset.bulk', [
            'assets' => $assets,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'categories' => $this->categoryOptions(),
            'filters' => $filters,
            'usefulLifePresets' => $this->usefulLifePresets(),
            'summary' => [
                'targetCount' => $assetIds->count(),
                'calculatedCount' => (clone $depreciationQuery)->count(),
                'uncalculatedCount' => max($assetIds->count() - (clone $depreciationQuery)->count(), 0),
                'totalAcquisitionValue' => (float) (clone $assetQuery)->sum('nilai'),
            ],
        ]);
    }

    /**
     * Hitung penyusutan untuk seluruh aset Register terfilter.
     */
    public function calculateAll(Request $request): RedirectResponse
    {
        $validated = $this->validatedCalculation($request);
        $filters = $this->filters($request);
        $assets = $this->assetQuery($filters)->get();

        $assets->each(function (AsetRegister $asset) use ($validated): void {
            $this->calculateForAsset(
                $asset,
                (int) $validated['tahun'],
                $this->usefulLifeForAsset($asset, $validated),
                (float) $validated['nilai_residu']
            );
        });

        return redirect()
            ->route('super-admin.penyusutan-aset.index', [
                'tahun' => $validated['tahun'],
                'bidang_id' => $filters['bidang_id'],
                'kategori' => $filters['kategori'],
                'status_penyusutan' => $filters['status_penyusutan'],
                'search' => $filters['search'],
            ])
            ->with('success', 'Penyusutan berhasil dihitung untuk ' . $assets->count() . ' aset Register.');
    }

    /**
     * Hitung penyusutan untuk satu aset Register.
     */
    public function calculate(Request $request, AsetRegister $aset_register): RedirectResponse
    {
        abort_unless($aset_register->status_verifikasi === 'Terverifikasi' && $aset_register->status !== 'Dihapus', 403, 'Penyusutan hanya dapat dihitung untuk aset aktif terverifikasi.');

        $validated = $this->validatedCalculation($request);
        $this->calculateForAsset(
            $aset_register,
            (int) $validated['tahun'],
            $this->usefulLifeForAsset($aset_register, $validated),
            (float) $validated['nilai_residu']
        );

        return redirect()
            ->route('super-admin.penyusutan-aset.index', [
                'tahun' => $validated['tahun'],
                'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
                'kategori' => $request->input('kategori', 'Semua Kategori'),
                'status_penyusutan' => $request->input('status_penyusutan', 'Semua Status'),
                'search' => $request->input('search'),
            ])
            ->with('success', 'Penyusutan aset ' . $aset_register->nama_aset . ' berhasil dihitung.');
    }

    /**
     * Tampilkan proyeksi jadwal penyusutan berdasarkan parameter yang tersimpan.
     */
    public function schedule(Request $request, AsetRegister $aset_register): View
    {
        abort_unless($aset_register->status_verifikasi === 'Terverifikasi' && $aset_register->status !== 'Dihapus', 404);

        $requestedYear = (int) $request->input('tahun', now()->year);
        $depreciation = $aset_register->penyusutan()
            ->with('calculator')
            ->where('tahun', $requestedYear)
            ->first()
            ?? $aset_register->penyusutan()
                ->with('calculator')
                ->orderByDesc('tahun')
                ->first();

        abort_unless($depreciation, 404, 'Aset ini belum memiliki perhitungan penyusutan.');

        $aset_register->load('bidang');
        $acquisitionYear = $this->acquisitionYear($aset_register, $depreciation->tahun);

        return view('pages.super-admin.PenyusutanAset.schedule', [
            'asset' => $aset_register,
            'depreciation' => $depreciation,
            'schedule' => $this->depreciationSchedule($aset_register, $depreciation, $acquisitionYear),
            'selectedPeriod' => max(0, $depreciation->tahun - $acquisitionYear + 1),
            'usefulLifeCategoryLabel' => $this->suggestedUsefulLifeLabel($aset_register->kode_barang),
            'backFilters' => $request->only(['tahun', 'bidang_id', 'kategori', 'status_penyusutan', 'search']),
        ]);
    }

    private function filters(Request $request): array
    {
        $status = $request->input('status_penyusutan', 'Semua Status');

        if (! in_array($status, ['Semua Status', 'Sudah Dihitung', 'Belum Dihitung'], true)) {
            $status = 'Semua Status';
        }

        return [
            'tahun' => (int) $request->input('tahun', now()->year),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'status_penyusutan' => $status,
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

        if ($filters['status_penyusutan'] === 'Sudah Dihitung') {
            $query->whereHas('penyusutan', fn (Builder $depreciationQuery) => $depreciationQuery
                ->where('tahun', $filters['tahun']));
        }

        if ($filters['status_penyusutan'] === 'Belum Dihitung') {
            $query->whereDoesntHave('penyusutan', fn (Builder $depreciationQuery) => $depreciationQuery
                ->where('tahun', $filters['tahun']));
        }

        if ($filters['search']) {
            $query->where(function ($searchQuery) use ($filters) {
                $searchQuery->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }

    private function validatedCalculation(Request $request): array
    {
        return $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'umur_manfaat_mode' => ['required', 'in:preset,manual'],
            'umur_manfaat_tahun' => ['nullable', 'required_if:umur_manfaat_mode,manual', 'integer', 'min:1', 'max:50'],
            'nilai_residu' => ['required', 'numeric', 'min:0'],
            'bidang_id' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === 'Semua Bidang') {
                        return;
                    }

                    if (! Bidang::whereKey($value)->exists()) {
                        $fail('Bidang yang dipilih tidak valid.');
                    }
                },
            ],
            'kategori' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
        ], [], [
            'tahun' => 'Tahun Penyusutan',
            'umur_manfaat_mode' => 'Mode Umur Manfaat',
            'umur_manfaat_tahun' => 'Umur Manfaat',
            'nilai_residu' => 'Nilai Residu',
        ]);
    }

    private function calculateForAsset(AsetRegister $asset, int $year, int $usefulLife, float $residualValue): PenyusutanAset
    {
        $acquisitionValue = (float) $asset->nilai;
        $residualValue = min($residualValue, $acquisitionValue);
        $acquisitionYear = $this->acquisitionYear($asset, $year);
        $annualDepreciation = max(($acquisitionValue - $residualValue) / $usefulLife, 0);

        if ($year < $acquisitionYear) {
            $openingValue = $acquisitionValue;
            $expense = 0;
            $closingValue = $acquisitionValue;
        } else {
            $elapsedYearsBefore = $year - $acquisitionYear;
            $depreciableYearsBefore = min($elapsedYearsBefore, $usefulLife);
            $openingValue = max($acquisitionValue - ($annualDepreciation * $depreciableYearsBefore), $residualValue);
            $remainingDepreciableValue = max($openingValue - $residualValue, 0);
            $expense = $elapsedYearsBefore >= $usefulLife ? 0 : min($annualDepreciation, $remainingDepreciableValue);
            $closingValue = max($openingValue - $expense, $residualValue);
        }

        return PenyusutanAset::updateOrCreate(
            [
                'aset_register_id' => $asset->id,
                'tahun' => $year,
            ],
            [
                'umur_manfaat_tahun' => $usefulLife,
                'nilai_awal_tahun' => round($openingValue, 2),
                'nilai_residu' => round($residualValue, 2),
                'beban_penyusutan' => round($expense, 2),
                'nilai_akhir_tahun' => round($closingValue, 2),
                'metode' => 'Garis Lurus',
                'dihitung_oleh' => auth()->id(),
                'tanggal_hitung' => now(),
            ]
        );
    }

    private function acquisitionYear(AsetRegister $asset, ?int $fallbackYear = null): int
    {
        return $asset->tanggal_perolehan?->year
            ?? $asset->created_at?->year
            ?? $fallbackYear
            ?? now()->year;
    }

    private function depreciationSchedule(AsetRegister $asset, PenyusutanAset $depreciation, int $acquisitionYear): array
    {
        $acquisitionValue = (float) $asset->nilai;
        $residualValue = min((float) $depreciation->nilai_residu, $acquisitionValue);
        $usefulLife = max(1, (int) $depreciation->umur_manfaat_tahun);
        $annualDepreciation = max(($acquisitionValue - $residualValue) / $usefulLife, 0);
        $schedule = [];

        for ($period = 1; $period <= $usefulLife; $period++) {
            $openingValue = max($acquisitionValue - ($annualDepreciation * ($period - 1)), $residualValue);
            $expense = min($annualDepreciation, max($openingValue - $residualValue, 0));
            $closingValue = max($openingValue - $expense, $residualValue);

            $schedule[] = (object) [
                'period' => $period,
                'year' => $acquisitionYear + $period - 1,
                'opening_value' => round($openingValue, 2),
                'expense' => round($expense, 2),
                'accumulated_depreciation' => round($acquisitionValue - $closingValue, 2),
                'book_value' => round($closingValue, 2),
            ];
        }

        return $schedule;
    }

    private function usefulLifeForAsset(AsetRegister $asset, array $validated): int
    {
        if (($validated['umur_manfaat_mode'] ?? 'manual') === 'preset') {
            return $this->suggestedUsefulLife($asset->kode_barang);
        }

        return (int) $validated['umur_manfaat_tahun'];
    }

    private function suggestedUsefulLife(?string $category): int
    {
        $category = mb_strtolower((string) $category);

        foreach ($this->usefulLifePresets() as $preset) {
            foreach ($preset['keywords'] as $keyword) {
                if (str_contains($category, $keyword)) {
                    return $preset['years'];
                }
            }
        }

        return 5;
    }

    private function suggestedUsefulLifeLabel(?string $category): string
    {
        $category = mb_strtolower((string) $category);

        foreach ($this->usefulLifePresets() as $preset) {
            foreach ($preset['keywords'] as $keyword) {
                if (str_contains($category, $keyword)) {
                    return $preset['label'];
                }
            }
        }

        return 'Kategori lain';
    }

    private function usefulLifePresets(): array
    {
        return [
            [
                'label' => 'Peralatan TIK / elektronik',
                'years' => 4,
                'keywords' => ['komputer', 'laptop', 'pc', 'server', 'jaringan', 'router', 'switch', 'firewall', 'printer', 'scanner', 'proyektor', 'elektronik'],
            ],
            [
                'label' => 'Mebel / perabot kantor',
                'years' => 5,
                'keywords' => ['mebel', 'perabot', 'meja', 'kursi', 'lemari', 'locker', 'sofa'],
            ],
            [
                'label' => 'Kendaraan',
                'years' => 8,
                'keywords' => ['kendaraan', 'motor', 'mobil'],
            ],
            [
                'label' => 'Gedung / bangunan',
                'years' => 20,
                'keywords' => ['gedung', 'bangunan'],
            ],
        ];
    }

    private function categoryOptions()
    {
        return AsetRegister::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi')
            ->whereNotNull('kode_barang')
            ->distinct()
            ->pluck('kode_barang')
            ->filter()
            ->sort()
            ->values();
    }
}
