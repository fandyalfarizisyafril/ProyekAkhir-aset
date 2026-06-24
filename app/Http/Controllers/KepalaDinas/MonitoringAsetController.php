<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MonitoringAsetController extends Controller
{
    public function data(Request $request): View
    {
        return $this->monitoringView($request, 'data');
    }

    public function kondisi(Request $request): View
    {
        return $this->monitoringView($request, 'kondisi');
    }

    public function status(Request $request): View
    {
        return $this->monitoringView($request, 'status');
    }

    public function show(Request $request, string $type, int $id): View
    {
        abort_unless(in_array($type, ['register', 'smki'], true), 404);

        $asset = $type === 'register'
            ? AsetRegister::notDeleted()->with(['bidang', 'inputter', 'verifier'])->where('status_verifikasi', 'Terverifikasi')->findOrFail($id)
            : AsetSmki::notDeleted()->with(['bidang', 'inputter', 'verifier'])->where('status_verifikasi', 'Terverifikasi')->findOrFail($id);

        return view('pages.kepala-dinas.MonitoringAset.show', [
            'asset' => $asset,
            'type' => $type,
            'backRoute' => $this->routeForMode($request->input('from', 'data')),
        ]);
    }

    private function monitoringView(Request $request, string $mode): View
    {
        $filters = $this->filters($request);
        $assets = $this->assets($filters);

        return view('pages.kepala-dinas.MonitoringAset.index', [
            'mode' => $mode,
            'pageTitle' => $this->titleForMode($mode),
            'pageSubtitle' => $this->subtitleForMode($mode),
            'routeName' => $this->routeForMode($mode),
            'assets' => $this->paginateCollection($assets, $request),
            'filters' => $filters,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'categoryOptions' => $this->categoryOptions(),
            'conditionOptions' => $this->conditionOptions(),
            'statusOptions' => $this->statusOptions(),
            'summaryCards' => $this->summaryCards($assets, $mode),
        ]);
    }

    private function filters(Request $request): array
    {
        return [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
            'status' => $request->input('status', 'Semua Status'),
            'search' => $request->input('search'),
        ];
    }

    private function assets(array $filters): Collection
    {
        $assets = collect();

        if (in_array($filters['jenis'], ['Semua Jenis', 'register'], true)) {
            $assets = $assets->merge($this->registerAssets($filters));
        }

        if (in_array($filters['jenis'], ['Semua Jenis', 'smki'], true)) {
            $assets = $assets->merge($this->smkiAssets($filters));
        }

        return $assets
            ->sortByDesc(fn (object $asset) => $asset->created_at?->timestamp ?? 0)
            ->values();
    }

    private function registerAssets(array $filters): Collection
    {
        return $this->applyFilters(
            AsetRegister::notDeleted()->with('bidang')->where('status_verifikasi', 'Terverifikasi'),
            'register',
            $filters
        )->latest()->get()->map(fn (AsetRegister $asset) => (object) [
            'id' => $asset->id,
            'type' => 'register',
            'type_label' => 'Register',
            'code' => $asset->kode_aset,
            'name' => $asset->nama_aset,
            'category' => $asset->kode_barang,
            'bidang' => $asset->bidang,
            'condition' => $asset->kondisi ?? $asset->status_barang,
            'status' => $this->displayAssetStatus($asset->status),
            'location' => $asset->lokasi_aset,
            'value' => (float) $asset->nilai,
            'created_at' => $asset->created_at,
            'detail_route' => route('kepala-dinas.monitoring-aset.show', ['register', $asset->id]),
        ]);
    }

    private function smkiAssets(array $filters): Collection
    {
        return $this->applyFilters(
            AsetSmki::notDeleted()->with('bidang')->where('status_verifikasi', 'Terverifikasi'),
            'smki',
            $filters
        )->latest()->get()->map(fn (AsetSmki $asset) => (object) [
            'id' => $asset->id,
            'type' => 'smki',
            'type_label' => 'SMKI',
            'code' => $asset->nomor_kode_barang,
            'name' => $asset->merk_model,
            'category' => $asset->jenis_barang,
            'bidang' => $asset->bidang,
            'condition' => $asset->keadaan_barang,
            'status' => $this->displayAssetStatus($asset->status),
            'location' => $asset->ruangan,
            'value' => null,
            'created_at' => $asset->created_at,
            'detail_route' => route('kepala-dinas.monitoring-aset.show', ['smki', $asset->id]),
        ]);
    }

    private function applyFilters(Builder $query, string $type, array $filters): Builder
    {
        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['kategori'] !== 'Semua Kategori') {
            $query->where($type === 'register' ? 'kode_barang' : 'jenis_barang', $filters['kategori']);
        }

        if ($filters['kondisi'] !== 'Semua Kondisi') {
            $query->where($type === 'register' ? 'kondisi' : 'keadaan_barang', $filters['kondisi']);
        }

        if ($filters['status'] !== 'Semua Status') {
            $query->where(function (Builder $query) use ($filters): void {
                if ($filters['status'] === 'Tersedia') {
                    $query->whereNull('status')->orWhereIn('status', ['Aktif', 'Tersedia']);
                } else {
                    $query->where('status', $filters['status']);
                }
            });
        }

        if ($filters['search']) {
            $query->where(function (Builder $query) use ($filters, $type): void {
                if ($type === 'register') {
                    $query->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('lokasi_aset', 'like', '%' . $filters['search'] . '%')
                        ->orWhereHas('bidang', fn (Builder $bidangQuery) => $bidangQuery->where('nama_bidang', 'like', '%' . $filters['search'] . '%'));
                } else {
                    $query->where('merk_model', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('jenis_barang', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('ruangan', 'like', '%' . $filters['search'] . '%')
                        ->orWhereHas('bidang', fn (Builder $bidangQuery) => $bidangQuery->where('nama_bidang', 'like', '%' . $filters['search'] . '%'));
                }
            });
        }

        return $query;
    }

    private function summaryCards(Collection $assets, string $mode): array
    {
        if ($mode === 'kondisi') {
            return [
                ['label' => 'Kondisi Baik', 'value' => $assets->where('condition', 'Baik')->count(), 'hint' => 'Aset siap digunakan'],
                ['label' => 'Rusak Ringan', 'value' => $assets->where('condition', 'Rusak Ringan')->count(), 'hint' => 'Butuh pemeliharaan'],
                ['label' => 'Rusak Berat', 'value' => $assets->where('condition', 'Rusak Berat')->count(), 'hint' => 'Prioritas evaluasi'],
            ];
        }

        if ($mode === 'status') {
            return [
                ['label' => 'Tersedia', 'value' => $assets->where('status', 'Tersedia')->count(), 'hint' => 'Aset aktif siap pakai'],
                ['label' => 'Dipinjam', 'value' => $assets->where('status', 'Dipinjam')->count(), 'hint' => 'Sedang dalam peminjaman'],
                ['label' => 'Maintenance / Rusak', 'value' => $assets->filter(fn ($asset) => in_array($asset->status, ['Maintenance', 'Rusak'], true))->count(), 'hint' => 'Perlu perhatian'],
            ];
        }

        return [
            ['label' => 'Total Aset', 'value' => $assets->count(), 'hint' => $assets->where('type', 'register')->count() . ' Register, ' . $assets->where('type', 'smki')->count() . ' SMKI'],
            ['label' => 'Nilai Register', 'value' => 'Rp ' . number_format((float) $assets->where('type', 'register')->sum('value'), 0, ',', '.'), 'hint' => 'Nilai perolehan aset Register'],
            ['label' => 'Bidang Terpantau', 'value' => $assets->pluck('bidang.id')->filter()->unique()->count(), 'hint' => 'Berdasarkan filter aktif'],
        ];
    }

    private function categoryOptions(): Collection
    {
        return AsetRegister::notDeleted()->where('status_verifikasi', 'Terverifikasi')->whereNotNull('kode_barang')->distinct()->pluck('kode_barang')
            ->merge(AsetSmki::notDeleted()->where('status_verifikasi', 'Terverifikasi')->whereNotNull('jenis_barang')->distinct()->pluck('jenis_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function conditionOptions(): Collection
    {
        return AsetRegister::notDeleted()->where('status_verifikasi', 'Terverifikasi')->whereNotNull('kondisi')->distinct()->pluck('kondisi')
            ->merge(AsetSmki::notDeleted()->where('status_verifikasi', 'Terverifikasi')->whereNotNull('keadaan_barang')->distinct()->pluck('keadaan_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function statusOptions(): array
    {
        return ['Tersedia', 'Dipinjam', 'Maintenance', 'Rusak'];
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

    private function titleForMode(string $mode): string
    {
        return match ($mode) {
            'kondisi' => 'Monitoring Kondisi Aset',
            'status' => 'Monitoring Status Aset',
            default => 'Monitoring Data Aset',
        };
    }

    private function subtitleForMode(string $mode): string
    {
        return match ($mode) {
            'kondisi' => 'Pantau kondisi fisik aset terverifikasi di seluruh bidang.',
            'status' => 'Pantau status ketersediaan dan operasional aset terverifikasi.',
            default => 'Pantau daftar aset Register dan SMKI terverifikasi di seluruh bidang.',
        };
    }

    private function routeForMode(string $mode): string
    {
        return match ($mode) {
            'kondisi' => 'kepala-dinas.monitoring-aset.kondisi',
            'status' => 'kepala-dinas.monitoring-aset.status',
            default => 'kepala-dinas.monitoring-aset.data',
        };
    }

    private function displayAssetStatus(?string $status): string
    {
        return match ($status) {
            null, 'Aktif' => 'Tersedia',
            default => $status,
        };
    }
}
