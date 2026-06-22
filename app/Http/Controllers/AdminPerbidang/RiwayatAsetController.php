<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\PenghapusanAset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RiwayatAsetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'search' => $request->input('search'),
        ];

        $assets = $this->deletedAssets((int) $request->user()->bidang_id, $filters);

        return view('pages.admin-perbidang.RiwayatAset.index', [
            'assets' => $this->paginateCollection($assets, $request),
            'filters' => $filters,
        ]);
    }

    private function deletedAssets(int $bidangId, array $filters): Collection
    {
        $assets = collect();

        if (in_array($filters['jenis'], ['Semua Jenis', 'register'], true)) {
            $assets = $assets->merge($this->registerAssets($bidangId, $filters));
        }

        if (in_array($filters['jenis'], ['Semua Jenis', 'smki'], true)) {
            $assets = $assets->merge($this->smkiAssets($bidangId, $filters));
        }

        return $assets
            ->sortByDesc(fn (object $asset) => $asset->deleted_at?->timestamp ?? $asset->updated_at?->timestamp ?? 0)
            ->values();
    }

    private function registerAssets(int $bidangId, array $filters): Collection
    {
        $query = AsetRegister::with(['bidang', 'penghapusan.remover'])
            ->where('bidang_id', $bidangId)
            ->where('status', 'Dihapus');

        if ($filters['search']) {
            $query->where(function ($searchQuery) use ($filters): void {
                $searchQuery->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kondisi', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->get()->map(function (AsetRegister $asset): object {
            $deletion = $this->latestDeletion($asset->penghapusan);

            return (object) [
                'id' => $asset->id,
                'type' => 'register',
                'type_label' => 'Register',
                'code' => $asset->kode_aset,
                'name' => $asset->nama_aset,
                'category' => $asset->kode_barang,
                'condition' => $asset->kondisi ?? $asset->status_barang,
                'status' => $asset->status,
                'bidang' => $asset->bidang,
                'book_value' => $deletion?->nilai_buku ?? $asset->nilai,
                'deleted_at' => $deletion?->tanggal_penghapusan,
                'updated_at' => $asset->updated_at,
                'deletion_method' => $deletion?->metode_penghapusan,
                'deletion_reason' => $deletion?->alasan,
                'removed_by' => $deletion?->remover?->name,
                'detail_route' => route('admin-perbidang.data-aset-register.show', $asset->id),
            ];
        });
    }

    private function smkiAssets(int $bidangId, array $filters): Collection
    {
        $query = AsetSmki::with(['bidang', 'penghapusan.remover'])
            ->where('bidang_id', $bidangId)
            ->where('status', 'Dihapus');

        if ($filters['search']) {
            $query->where(function ($searchQuery) use ($filters): void {
                $searchQuery->where('jenis_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('merk_model', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('keadaan_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->get()->map(function (AsetSmki $asset): object {
            $deletion = $this->latestDeletion($asset->penghapusan);

            return (object) [
                'id' => $asset->id,
                'type' => 'smki',
                'type_label' => 'SMKI',
                'code' => $asset->nomor_kode_barang,
                'name' => $asset->merk_model,
                'category' => $asset->jenis_barang,
                'condition' => $asset->keadaan_barang,
                'status' => $asset->status,
                'bidang' => $asset->bidang,
                'book_value' => $deletion?->nilai_buku,
                'deleted_at' => $deletion?->tanggal_penghapusan,
                'updated_at' => $asset->updated_at,
                'deletion_method' => $deletion?->metode_penghapusan,
                'deletion_reason' => $deletion?->alasan,
                'removed_by' => $deletion?->remover?->name,
                'detail_route' => route('admin-perbidang.data-aset-smki.show', $asset->id),
            ];
        });
    }

    private function latestDeletion(Collection $history): ?PenghapusanAset
    {
        return $history
            ->sortByDesc(fn (PenghapusanAset $item) => $item->tanggal_penghapusan?->timestamp ?? $item->id)
            ->first();
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
}
