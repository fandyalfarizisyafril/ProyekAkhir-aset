<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PenghapusanAset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenghapusanAsetController extends Controller
{
    /**
     * Tampilkan aset yang dapat dinonaktifkan dan riwayat penghapusan.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $assets = $this->eligibleAssets($filters);
        $paginatedAssets = $this->paginateCollection($assets, $request);

        $historyQuery = PenghapusanAset::with(['bidang', 'remover'])->latest('tanggal_penghapusan')->latest('id');

        return view('pages.super-admin.PenghapusanAset.index', [
            'assets' => $paginatedAssets,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'history' => $historyQuery->take(10)->get(),
            'summary' => [
                'eligibleCount' => $assets->count(),
                'deletionCount' => PenghapusanAset::count(),
                'damagedCount' => $assets->filter(fn ($asset) => $asset->is_damaged)->count(),
                'registerDeletionCount' => PenghapusanAset::where('jenis_aset', 'register')->count(),
            ],
        ]);
    }

    /**
     * Nonaktifkan aset dari inventaris aktif dan simpan riwayat penghapusan.
     */
    public function store(Request $request, string $type, int $id): RedirectResponse
    {
        abort_unless(in_array($type, ['register', 'smki'], true), 404);

        $validated = $request->validate([
            'tanggal_penghapusan' => ['required', 'date', 'before_or_equal:today'],
            'metode_penghapusan' => ['required', 'string', 'max:255', Rule::in($this->deletionMethods())],
            'alasan' => ['required', 'string', 'max:1000'],
            'jenis' => ['nullable', 'string'],
            'bidang_id' => ['nullable'],
            'search' => ['nullable', 'string', 'max:255'],
        ], [], [
            'tanggal_penghapusan' => 'Tanggal Penghapusan',
            'metode_penghapusan' => 'Metode Penghapusan',
            'alasan' => 'Alasan',
        ]);

        $asset = $this->resolveAsset($type, $id);

        abort_unless($asset->status_verifikasi === 'Terverifikasi', 403, 'Penghapusan hanya dapat dilakukan untuk aset terverifikasi.');

        if (($asset->status ?? null) === 'Dihapus') {
            return $this->redirectBack($request)
                ->with('error', 'Aset ini sudah dinonaktifkan dari inventaris aktif.');
        }

        if ($this->hasActiveLoan($asset)) {
            return $this->redirectBack($request)
                ->with('error', 'Aset ini masih memiliki peminjaman aktif atau menunggu verifikasi.');
        }

        DB::transaction(function () use ($asset, $type, $validated, $request): void {
            PenghapusanAset::create([
                'aset_register_id' => $type === 'register' ? $asset->id : null,
                'aset_smki_id' => $type === 'smki' ? $asset->id : null,
                'jenis_aset' => $type,
                'kode_aset' => $this->assetCode($asset, $type),
                'nama_aset' => $this->assetName($asset, $type),
                'bidang_id' => $asset->bidang_id,
                'nilai_buku' => $this->bookValue($asset, $type),
                'tanggal_penghapusan' => $validated['tanggal_penghapusan'],
                'metode_penghapusan' => $validated['metode_penghapusan'],
                'alasan' => $validated['alasan'],
                'status_sebelum' => $asset->status ?? null,
                'dihapus_oleh' => $request->user()->id,
            ]);

            $updates = ['status' => 'Dihapus'];

            if ($type === 'register') {
                $updates['metode_pemusnahan'] = $validated['metode_penghapusan'];
            }

            $asset->update($updates);
        });

        return $this->redirectBack($request)
            ->with('success', 'Aset ' . $this->assetName($asset, $type) . ' berhasil dinonaktifkan dari inventaris aktif.');
    }

    private function filters(Request $request): array
    {
        return [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];
    }

    private function eligibleAssets(array $filters): Collection
    {
        $assets = collect();

        if (in_array($filters['jenis'], ['Semua Jenis', 'register'], true)) {
            $assets = $assets->merge($this->registerAssets($filters));
        }

        if (in_array($filters['jenis'], ['Semua Jenis', 'smki'], true)) {
            $assets = $assets->merge($this->smkiAssets($filters));
        }

        return $assets
            ->sortBy([
                ['is_damaged', 'desc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function registerAssets(array $filters): Collection
    {
        $query = AsetRegister::with(['bidang', 'penyusutan', 'peminjaman'])
            ->where('status_verifikasi', 'Terverifikasi')
            ->where('status', '!=', 'Dihapus');

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['search']) {
            $query->where(function ($searchQuery) use ($filters): void {
                $searchQuery->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kondisi', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->get()->map(fn (AsetRegister $asset) => (object) [
            'id' => $asset->id,
            'type' => 'register',
            'type_label' => 'Register',
            'code' => $asset->kode_aset,
            'name' => $asset->nama_aset,
            'category' => $asset->kode_barang,
            'bidang' => $asset->bidang,
            'condition' => $asset->kondisi,
            'status' => $this->displayAssetStatus($asset->status),
            'book_value' => $this->bookValue($asset, 'register'),
            'latest_depreciation_year' => $asset->penyusutan->sortByDesc('tahun')->first()?->tahun,
            'is_damaged' => $asset->kondisi === 'Rusak Berat' || $asset->status === 'Rusak',
            'has_active_loan' => $this->hasActiveLoan($asset),
        ]);
    }

    private function smkiAssets(array $filters): Collection
    {
        $query = AsetSmki::with(['bidang', 'peminjaman'])
            ->where('status_verifikasi', 'Terverifikasi')
            ->where(function ($statusQuery): void {
                $statusQuery->whereNull('status')
                    ->orWhere('status', '!=', 'Dihapus');
            });

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['search']) {
            $query->where(function ($searchQuery) use ($filters): void {
                $searchQuery->where('jenis_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('merk_model', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('keadaan_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->get()->map(fn (AsetSmki $asset) => (object) [
            'id' => $asset->id,
            'type' => 'smki',
            'type_label' => 'SMKI',
            'code' => $asset->nomor_kode_barang,
            'name' => $asset->jenis_barang,
            'category' => $asset->merk_model,
            'bidang' => $asset->bidang,
            'condition' => $asset->keadaan_barang,
            'status' => $this->displayAssetStatus($asset->status),
            'book_value' => null,
            'latest_depreciation_year' => null,
            'is_damaged' => $asset->keadaan_barang === 'Rusak Berat' || $asset->status === 'Rusak',
            'has_active_loan' => $this->hasActiveLoan($asset),
        ]);
    }

    private function paginateCollection(Collection $assets, Request $request): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();
        $perPage = 10;

        return new Paginator(
            $assets->forPage($page, $perPage)->values(),
            $assets->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function resolveAsset(string $type, int $id): Model
    {
        return $type === 'register'
            ? AsetRegister::with(['penyusutan', 'peminjaman'])->findOrFail($id)
            : AsetSmki::with('peminjaman')->findOrFail($id);
    }

    private function bookValue(Model $asset, string $type): ?float
    {
        if ($type === 'smki') {
            return null;
        }

        $latestDepreciation = $asset->penyusutan->sortByDesc('tahun')->first();

        return (float) ($latestDepreciation?->nilai_akhir_tahun ?? $asset->nilai);
    }

    private function hasActiveLoan(Model $asset): bool
    {
        return $asset->peminjaman
            ->whereIn('status', ['Menunggu Verifikasi', 'Disetujui'])
            ->whereNull('tanggal_kembali')
            ->isNotEmpty();
    }

    private function displayAssetStatus(?string $status): string
    {
        return match ($status) {
            null, 'Aktif' => 'Tersedia',
            default => $status,
        };
    }

    private function assetCode(Model $asset, string $type): string
    {
        return $type === 'register' ? $asset->kode_aset : $asset->nomor_kode_barang;
    }

    private function assetName(Model $asset, string $type): string
    {
        return $type === 'register' ? $asset->nama_aset : $asset->jenis_barang;
    }

    private function deletionMethods(): array
    {
        return ['Pemusnahan', 'Penjualan', 'Hibah', 'Pengalihan', 'Lainnya'];
    }

    private function redirectBack(Request $request): RedirectResponse
    {
        return redirect()->route('super-admin.penghapusan-aset.index', [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ]);
    }
}
