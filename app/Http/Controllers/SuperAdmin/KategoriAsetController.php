<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\KategoriAset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriAsetController extends Controller
{
    /**
     * Tampilkan daftar kategori aset.
     */
    public function index(Request $request): View
    {
        $filters = [
            'tipe' => $request->input('tipe', 'Semua Tipe'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];

        $assetRows = $this->verifiedAssetRows($filters);
        $allAssetRows = $this->verifiedAssetRows([
            'tipe' => 'Semua Tipe',
            'bidang_id' => 'Semua Bidang',
            'search' => null,
        ]);

        return view('pages.super-admin.KategoriAset.index', [
            'assets' => $this->paginateAssetRows($assetRows, $request),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'totalCount' => $allAssetRows->count(),
            'registerCount' => $allAssetRows->where('tipe', 'Register')->count(),
            'smkiCount' => $allAssetRows->where('tipe', 'SMKI')->count(),
        ]);
    }

    /**
     * Tampilkan form kategori baru.
     */
    public function create(): View
    {
        return view('pages.super-admin.KategoriAset.create');
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request): RedirectResponse
    {
        KategoriAset::create($this->validated($request));

        return redirect()
            ->route('super-admin.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit kategori.
     */
    public function edit(KategoriAset $kategori_aset): View
    {
        return view('pages.super-admin.KategoriAset.edit', [
            'category' => $kategori_aset,
            'usageCount' => $this->usageCount($kategori_aset),
        ]);
    }

    /**
     * Perbarui kategori aset.
     */
    public function update(Request $request, KategoriAset $kategori_aset): RedirectResponse
    {
        $validated = $this->validated($request, $kategori_aset);

        if (
            $this->usageCount($kategori_aset) > 0
            && ($validated['tipe'] !== $kategori_aset->tipe || $validated['nama_kategori'] !== $kategori_aset->nama_kategori)
        ) {
            return redirect()
                ->route('super-admin.kategori-aset.edit', $kategori_aset->id)
                ->with('error', 'Nama atau tipe kategori tidak dapat diubah karena masih digunakan oleh data aset.')
                ->withInput();
        }

        $kategori_aset->update($validated);

        return redirect()
            ->route('super-admin.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil diperbarui.');
    }

    /**
     * Hapus kategori aset jika belum dipakai aset.
     */
    public function destroy(KategoriAset $kategori_aset): RedirectResponse
    {
        if ($this->usageCount($kategori_aset) > 0) {
            return redirect()
                ->route('super-admin.kategori-aset.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data aset.');
        }

        $kategori_aset->delete();

        return redirect()
            ->route('super-admin.kategori-aset.index')
            ->with('success', 'Kategori aset berhasil dihapus.');
    }

    private function validated(Request $request, ?KategoriAset $category = null): array
    {
        return $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_aset', 'nama_kategori')
                    ->where(function ($query) use ($request, $category) {
                        $query->where('tipe', $request->input('tipe'));

                        if ($category?->bidang_id) {
                            $query->where('bidang_id', $category->bidang_id);
                        } else {
                            $query->whereNull('bidang_id');
                        }
                    })
                    ->ignore($category?->id),
            ],
            'tipe' => ['required', Rule::in(['Register', 'SMKI'])],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'nama_kategori' => 'Nama Kategori',
            'tipe' => 'Tipe Kategori',
            'deskripsi' => 'Deskripsi',
        ]);
    }

    private function usageCount(KategoriAset $category): int
    {
        if ($category->tipe === 'Register') {
            $query = AsetRegister::notDeleted()->where('kode_barang', $category->nama_kategori);

            if ($category->bidang_id) {
                $query->where('bidang_id', $category->bidang_id);
            }

            return $query->count();
        }

        $query = AsetSmki::notDeleted()->where('jenis_barang', $category->nama_kategori);

        if ($category->bidang_id) {
            $query->where('bidang_id', $category->bidang_id);
        }

        return $query->count();
    }

    private function verifiedAssetRows(array $filters): Collection
    {
        $rows = collect();

        if (($filters['tipe'] ?? 'Semua Tipe') !== 'SMKI') {
            $registerQuery = AsetRegister::with('bidang')
                ->notDeleted()
                ->where('status_verifikasi', 'Terverifikasi');

            $this->applyCommonAssetFilters($registerQuery, $filters, 'register');

            $rows = $rows->merge(
                $registerQuery->latest('created_at')->get()->map(fn (AsetRegister $asset) => (object) [
                    'tipe' => 'Register',
                    'asset_name' => $asset->nama_aset,
                    'asset_code' => $asset->kode_aset,
                    'category_name' => $asset->kode_barang,
                    'bidang' => $asset->bidang,
                    'description' => $asset->keterangan,
                    'created_at' => $asset->created_at,
                    'detail_asset_url' => route('super-admin.verifikasi-aset.show', ['register', $asset->id]),
                ])
            );
        }

        if (($filters['tipe'] ?? 'Semua Tipe') !== 'Register') {
            $smkiQuery = AsetSmki::with('bidang')
                ->notDeleted()
                ->where('status_verifikasi', 'Terverifikasi');

            $this->applyCommonAssetFilters($smkiQuery, $filters, 'smki');

            $rows = $rows->merge(
                $smkiQuery->latest('created_at')->get()->map(fn (AsetSmki $asset) => (object) [
                    'tipe' => 'SMKI',
                    'asset_name' => $asset->merk_model,
                    'asset_code' => $asset->nomor_kode_barang,
                    'category_name' => $asset->jenis_barang,
                    'bidang' => $asset->bidang,
                    'description' => $asset->keterangan,
                    'created_at' => $asset->created_at,
                    'detail_asset_url' => route('super-admin.verifikasi-aset.show', ['smki', $asset->id]),
                ])
            );
        }

        return $rows
            ->sortByDesc(fn (object $asset) => optional($asset->created_at)->timestamp ?? 0)
            ->values();
    }

    private function applyCommonAssetFilters(Builder $query, array $filters, string $type): void
    {
        if (($filters['bidang_id'] ?? 'Semua Bidang') !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if (! ($filters['search'] ?? null)) {
            return;
        }

        $search = $filters['search'];

        $query->where(function (Builder $query) use ($search, $type) {
            if ($type === 'register') {
                $query->where('nama_aset', 'like', '%' . $search . '%')
                    ->orWhere('kode_aset', 'like', '%' . $search . '%')
                    ->orWhere('kode_barang', 'like', '%' . $search . '%')
                    ->orWhere('keterangan', 'like', '%' . $search . '%')
                    ->orWhere('lokasi_aset', 'like', '%' . $search . '%');
            } else {
                $query->where('merk_model', 'like', '%' . $search . '%')
                    ->orWhere('nomor_kode_barang', 'like', '%' . $search . '%')
                    ->orWhere('jenis_barang', 'like', '%' . $search . '%')
                    ->orWhere('keterangan', 'like', '%' . $search . '%')
                    ->orWhere('ruangan', 'like', '%' . $search . '%');
            }

            $query->orWhereHas('bidang', function (Builder $bidangQuery) use ($search) {
                $bidangQuery->where('nama_bidang', 'like', '%' . $search . '%');
            });
        });
    }

    private function paginateAssetRows(Collection $assetRows, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        return new LengthAwarePaginator(
            $assetRows->forPage($page, $perPage)->values(),
            $assetRows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function assetBackedCategoryQuery(): Builder
    {
        return KategoriAset::query()
            ->where(function (Builder $query) {
                $query->where(function (Builder $registerQuery) {
                    $registerQuery
                        ->where('tipe', 'Register')
                        ->whereExists(function ($assetQuery) {
                            $assetQuery
                                ->selectRaw('1')
                                ->from('aset_register')
                                ->whereColumn('aset_register.kode_barang', 'kategori_aset.nama_kategori')
                                ->where('aset_register.status_verifikasi', 'Terverifikasi')
                                ->where(function ($assetQuery) {
                                    $assetQuery->whereNull('aset_register.status')
                                        ->orWhere('aset_register.status', '!=', 'Dihapus');
                                })
                                ->where(function ($assetQuery) {
                                    $assetQuery
                                        ->whereColumn('aset_register.bidang_id', 'kategori_aset.bidang_id')
                                        ->orWhereNull('kategori_aset.bidang_id');
                                });
                        });
                })->orWhere(function (Builder $smkiQuery) {
                    $smkiQuery
                        ->where('tipe', 'SMKI')
                        ->whereExists(function ($assetQuery) {
                            $assetQuery
                                ->selectRaw('1')
                                ->from('aset_smki')
                                ->whereColumn('aset_smki.jenis_barang', 'kategori_aset.nama_kategori')
                                ->where('aset_smki.status_verifikasi', 'Terverifikasi')
                                ->where(function ($assetQuery) {
                                    $assetQuery->whereNull('aset_smki.status')
                                        ->orWhere('aset_smki.status', '!=', 'Dihapus');
                                })
                                ->where(function ($assetQuery) {
                                    $assetQuery
                                        ->whereColumn('aset_smki.bidang_id', 'kategori_aset.bidang_id')
                                        ->orWhereNull('kategori_aset.bidang_id');
                                });
                        });
                });
            });
    }

    private function assetDetailUrl(KategoriAset $category): ?string
    {
        $asset = $this->detailAssetForCategory($category);

        if (! $asset) {
            return null;
        }

        return route('super-admin.verifikasi-aset.show', [
            strtolower($category->tipe),
            $asset->id,
        ]);
    }

    private function detailAssetForCategory(KategoriAset $category): AsetRegister|AsetSmki|null
    {
        if ($category->tipe === 'Register') {
            $query = AsetRegister::notDeleted()
                ->where('kode_barang', $category->nama_kategori)
                ->where('status_verifikasi', 'Terverifikasi');

            if ($category->bidang_id) {
                $query->where('bidang_id', $category->bidang_id);
            }

            return $query->latest('updated_at')->first();
        }

        $query = AsetSmki::notDeleted()
            ->where('jenis_barang', $category->nama_kategori)
            ->where('status_verifikasi', 'Terverifikasi');

        if ($category->bidang_id) {
            $query->where('bidang_id', $category->bidang_id);
        }

        return $query->latest('updated_at')->first();
    }

    private function syncCategoriesFromAssets(): void
    {
        $this->removeLegacyAutoCategories();

        AsetRegister::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi')
            ->whereNotNull('kode_barang')
            ->latest('updated_at')
            ->get(['kode_barang', 'keterangan', 'bidang_id', 'updated_at'])
            ->unique(fn (AsetRegister $asset) => $asset->bidang_id . '|' . trim($asset->kode_barang))
            ->each(fn (AsetRegister $asset) => $this->syncCategoryFromAsset(
                'Register',
                $asset->kode_barang,
                $asset->keterangan,
                $asset->bidang_id
            ));

        AsetSmki::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi')
            ->whereNotNull('jenis_barang')
            ->latest('updated_at')
            ->get(['jenis_barang', 'keterangan', 'bidang_id', 'updated_at'])
            ->unique(fn (AsetSmki $asset) => $asset->bidang_id . '|' . trim($asset->jenis_barang))
            ->each(fn (AsetSmki $asset) => $this->syncCategoryFromAsset(
                'SMKI',
                $asset->jenis_barang,
                $asset->keterangan,
                $asset->bidang_id
            ));
    }

    private function syncCategoryFromAsset(string $type, ?string $name, ?string $description, ?int $bidangId): void
    {
        $name = trim((string) $name);
        $description = trim((string) $description);

        if ($name === '') {
            return;
        }

        $category = KategoriAset::where([
            'tipe' => $type,
            'nama_kategori' => $name,
            'bidang_id' => $bidangId,
        ])->first();

        $category ??= KategoriAset::where('tipe', $type)
            ->where('nama_kategori', $name)
            ->whereNull('bidang_id')
            ->first();

        $payload = [
            'deskripsi' => $description !== '' ? $description : null,
            'bidang_id' => $bidangId,
        ];

        if ($category) {
            $category->update($payload);

            return;
        }

        KategoriAset::create([
            'tipe' => $type,
            'nama_kategori' => $name,
            ...$payload,
        ]);
    }

    private function removeLegacyAutoCategories(): void
    {
        KategoriAset::whereNull('bidang_id')
            ->where(function ($query) {
                $query->where('deskripsi', 'like', 'Dibuat otomatis dari input data aset%')
                    ->orWhere('deskripsi', 'like', 'Diambil otomatis dari data aset%');
            })
            ->delete();
    }
}
