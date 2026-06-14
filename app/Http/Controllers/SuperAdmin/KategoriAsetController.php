<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\KategoriAset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriAsetController extends Controller
{
    /**
     * Tampilkan daftar kategori aset.
     */
    public function index(Request $request): View
    {
        $this->syncCategoriesFromAssets();

        $filters = [
            'tipe' => $request->input('tipe', 'Semua Tipe'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];

        $query = KategoriAset::with('bidang')->latest();

        if ($filters['tipe'] !== 'Semua Tipe') {
            $query->where('tipe', $filters['tipe']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_kategori', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('deskripsi', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('bidang', function ($bidangQuery) use ($filters) {
                        $bidangQuery->where('nama_bidang', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        return view('pages.super-admin.KategoriAset.index', [
            'categories' => $query->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'totalCount' => KategoriAset::count(),
            'registerCount' => KategoriAset::where('tipe', 'Register')->count(),
            'smkiCount' => KategoriAset::where('tipe', 'SMKI')->count(),
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
            $query = AsetRegister::where('kode_barang', $category->nama_kategori);

            if ($category->bidang_id) {
                $query->where('bidang_id', $category->bidang_id);
            }

            return $query->count();
        }

        $query = AsetSmki::where('jenis_barang', $category->nama_kategori);

        if ($category->bidang_id) {
            $query->where('bidang_id', $category->bidang_id);
        }

        return $query->count();
    }

    private function syncCategoriesFromAssets(): void
    {
        $this->removeLegacyAutoCategories();

        AsetRegister::where('status_verifikasi', 'Terverifikasi')
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

        AsetSmki::where('status_verifikasi', 'Terverifikasi')
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
