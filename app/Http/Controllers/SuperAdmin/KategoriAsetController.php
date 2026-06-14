<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
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
            'search' => $request->input('search'),
        ];

        $query = KategoriAset::query()->latest();

        if ($filters['tipe'] !== 'Semua Tipe') {
            $query->where('tipe', $filters['tipe']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_kategori', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('deskripsi', 'like', '%' . $filters['search'] . '%');
            });
        }

        return view('pages.super-admin.KategoriAset.index', [
            'categories' => $query->paginate(10)->withQueryString(),
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
                    ->where(fn ($query) => $query->where('tipe', $request->input('tipe')))
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
            return AsetRegister::where('kode_barang', $category->nama_kategori)->count();
        }

        return AsetSmki::where('jenis_barang', $category->nama_kategori)->count();
    }

    private function syncCategoriesFromAssets(): void
    {
        AsetRegister::whereNotNull('kode_barang')
            ->distinct()
            ->pluck('kode_barang')
            ->each(fn ($name) => $this->firstOrCreateCategory(
                'Register',
                $name,
                'Diambil otomatis dari data aset Register.'
            ));

        AsetSmki::whereNotNull('jenis_barang')
            ->distinct()
            ->pluck('jenis_barang')
            ->each(fn ($name) => $this->firstOrCreateCategory(
                'SMKI',
                $name,
                'Diambil otomatis dari data aset SMKI.'
            ));
    }

    private function firstOrCreateCategory(string $type, ?string $name, string $description): void
    {
        $name = trim((string) $name);

        if ($name === '') {
            return;
        }

        KategoriAset::firstOrCreate(
            ['tipe' => $type, 'nama_kategori' => $name],
            ['deskripsi' => $description]
        );
    }
}
