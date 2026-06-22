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
            ->with(['bidang', 'penyusutan' => fn ($query) => $query->where('tahun', $filters['tahun'])])
            ->latest();

        $assetIds = (clone $assetQuery)->pluck('id');
        $depreciationQuery = PenyusutanAset::where('tahun', $filters['tahun'])
            ->whereIn('aset_register_id', $assetIds);

        return view('pages.super-admin.PenyusutanAset.index', [
            'assets' => $assetQuery->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'summary' => [
                'eligibleCount' => $assetIds->count(),
                'calculatedCount' => (clone $depreciationQuery)->count(),
                'totalAcquisitionValue' => (float) $this->assetQuery($filters)->sum('nilai'),
                'totalDepreciationExpense' => (float) (clone $depreciationQuery)->sum('beban_penyusutan'),
                'totalBookValue' => (float) (clone $depreciationQuery)->sum('nilai_akhir_tahun'),
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

        $assets->each(fn (AsetRegister $asset) => $this->calculateForAsset(
            $asset,
            (int) $validated['tahun'],
            (int) $validated['umur_manfaat_tahun'],
            (float) $validated['nilai_residu']
        ));

        return redirect()
            ->route('super-admin.penyusutan-aset.index', [
                'tahun' => $validated['tahun'],
                'bidang_id' => $filters['bidang_id'],
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
            (int) $validated['umur_manfaat_tahun'],
            (float) $validated['nilai_residu']
        );

        return redirect()
            ->route('super-admin.penyusutan-aset.index', [
                'tahun' => $validated['tahun'],
                'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
                'search' => $request->input('search'),
            ])
            ->with('success', 'Penyusutan aset ' . $aset_register->nama_aset . ' berhasil dihitung.');
    }

    private function filters(Request $request): array
    {
        return [
            'tahun' => (int) $request->input('tahun', now()->year),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
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
            'umur_manfaat_tahun' => ['required', 'integer', 'min:1', 'max:50'],
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
            'search' => ['nullable', 'string', 'max:255'],
        ], [], [
            'tahun' => 'Tahun Penyusutan',
            'umur_manfaat_tahun' => 'Umur Manfaat',
            'nilai_residu' => 'Nilai Residu',
        ]);
    }

    private function calculateForAsset(AsetRegister $asset, int $year, int $usefulLife, float $residualValue): PenyusutanAset
    {
        $acquisitionValue = (float) $asset->nilai;
        $residualValue = min($residualValue, $acquisitionValue);
        $acquisitionYear = $asset->created_at?->year ?? $year;
        $elapsedYearsBefore = max(0, $year - $acquisitionYear);

        $annualDepreciation = max(($acquisitionValue - $residualValue) / $usefulLife, 0);
        $depreciableYearsBefore = min($elapsedYearsBefore, $usefulLife);
        $openingValue = max($acquisitionValue - ($annualDepreciation * $depreciableYearsBefore), $residualValue);
        $remainingDepreciableValue = max($openingValue - $residualValue, 0);
        $expense = $elapsedYearsBefore >= $usefulLife ? 0 : min($annualDepreciation, $remainingDepreciableValue);
        $closingValue = max($openingValue - $expense, $residualValue);

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
            ]
        );
    }
}
