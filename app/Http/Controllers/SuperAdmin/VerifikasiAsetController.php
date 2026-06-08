<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class VerifikasiAsetController extends Controller
{
    /**
     * Tampilkan daftar aset yang perlu diverifikasi.
     */
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'status' => $request->input('status', 'Perlu Verifikasi'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];

        $assets = $this->filteredAssets($filters)
            ->sortByDesc('created_at')
            ->values();

        $perPage = 10;
        $page = (int) $request->input('page', 1);
        $paginatedAssets = new LengthAwarePaginator(
            $assets->forPage($page, $perPage)->values(),
            $assets->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.super-admin.VerifikasiAset.index', [
            'assets' => $paginatedAssets,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'pendingCount' => $this->countByStatus('Perlu Verifikasi'),
            'verifiedCount' => $this->countByStatus('Terverifikasi'),
            'rejectedCount' => $this->countByStatus('Ditolak'),
        ]);
    }

    /**
     * Tampilkan detail aset untuk ditinjau Super Admin.
     */
    public function show(string $type, int $id): View
    {
        [$asset, $assetData] = $this->resolveAsset($type, $id);

        return view('pages.super-admin.VerifikasiAset.show', [
            'asset' => $asset,
            'assetData' => $assetData,
            'type' => $type,
        ]);
    }

    /**
     * Setujui aset yang diajukan.
     */
    public function approve(string $type, int $id): RedirectResponse
    {
        [$asset] = $this->resolveAsset($type, $id);

        $asset->update([
            'status_verifikasi' => 'Terverifikasi',
            'diverifikasi_oleh' => auth()->id(),
        ]);

        return redirect()
            ->route('super-admin.verifikasi-aset.index')
            ->with('success', 'Aset berhasil diverifikasi.');
    }

    /**
     * Tolak aset yang diajukan.
     */
    public function reject(string $type, int $id): RedirectResponse
    {
        [$asset] = $this->resolveAsset($type, $id);

        $asset->update([
            'status_verifikasi' => 'Ditolak',
            'diverifikasi_oleh' => auth()->id(),
        ]);

        return redirect()
            ->route('super-admin.verifikasi-aset.index')
            ->with('success', 'Aset berhasil ditolak.');
    }

    /**
     * Gabungkan aset Register dan SMKI menjadi format tabel verifikasi.
     */
    private function filteredAssets(array $filters): Collection
    {
        $assets = collect();

        if ($filters['jenis'] === 'Semua Jenis' || $filters['jenis'] === 'register') {
            $assets = $assets->concat($this->registerAssets($filters));
        }

        if ($filters['jenis'] === 'Semua Jenis' || $filters['jenis'] === 'smki') {
            $assets = $assets->concat($this->smkiAssets($filters));
        }

        return $assets;
    }

    private function registerAssets(array $filters): Collection
    {
        $query = AsetRegister::with(['bidang', 'inputter', 'verifier']);

        if ($filters['status'] !== 'Semua Status') {
            $query->where('status_verifikasi', $filters['status']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get()->map(fn (AsetRegister $asset) => (object) [
            'id' => $asset->id,
            'type' => 'register',
            'type_label' => 'REGISTER',
            'name' => $asset->nama_aset,
            'code' => $asset->kode_aset,
            'category' => $asset->kode_barang,
            'condition' => $asset->kondisi,
            'status_verifikasi' => $asset->status_verifikasi,
            'bidang' => $asset->bidang,
            'inputter' => $asset->inputter,
            'verifier' => $asset->verifier,
            'created_at' => $asset->created_at,
        ]);
    }

    private function smkiAssets(array $filters): Collection
    {
        $query = AsetSmki::with(['bidang', 'inputter', 'verifier']);

        if ($filters['status'] !== 'Semua Status') {
            $query->where('status_verifikasi', $filters['status']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('merk_model', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('jenis_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get()->map(fn (AsetSmki $asset) => (object) [
            'id' => $asset->id,
            'type' => 'smki',
            'type_label' => 'SMKI',
            'name' => $asset->merk_model,
            'code' => $asset->nomor_kode_barang,
            'category' => $asset->jenis_barang,
            'condition' => $asset->keadaan_barang,
            'status_verifikasi' => $asset->status_verifikasi,
            'bidang' => $asset->bidang,
            'inputter' => $asset->inputter,
            'verifier' => $asset->verifier,
            'created_at' => $asset->created_at,
        ]);
    }

    private function countByStatus(string $status): int
    {
        return AsetRegister::where('status_verifikasi', $status)->count()
            + AsetSmki::where('status_verifikasi', $status)->count();
    }

    private function resolveAsset(string $type, int $id): array
    {
        abort_unless(in_array($type, ['register', 'smki'], true), 404);

        if ($type === 'register') {
            $asset = AsetRegister::with(['bidang', 'inputter', 'verifier'])->findOrFail($id);

            return [$asset, (object) [
                'title' => $asset->nama_aset,
                'code' => $asset->kode_aset,
                'type_label' => 'REGISTER',
                'category' => $asset->kode_barang,
                'condition' => $asset->kondisi,
                'location' => $asset->lokasi_aset,
                'owner' => $asset->pemilik_aset,
                'responsible_person' => $asset->pengguna,
                'year' => null,
                'value' => $asset->nilai,
                'status_verifikasi' => $asset->status_verifikasi,
                'description' => $asset->keterangan,
                'detail_rows' => [
                    'Kode Barang' => $asset->kode_barang,
                    'Kode Urut Barang' => $asset->kode_urut_barang,
                    'Kerahasiaan' => $asset->kerahasiaan,
                    'Kritikalitas' => $asset->kritikalitas,
                    'Metode Pemusnahan' => $asset->metode_pemusnahan ?: '-',
                ],
            ]];
        }

        $asset = AsetSmki::with(['bidang', 'inputter', 'verifier'])->findOrFail($id);

        return [$asset, (object) [
            'title' => $asset->merk_model,
            'code' => $asset->nomor_kode_barang,
            'type_label' => 'SMKI',
            'category' => $asset->jenis_barang,
            'condition' => $asset->keadaan_barang,
            'location' => $asset->ruangan,
            'owner' => '-',
            'responsible_person' => $asset->penanggung_jawab,
            'year' => $asset->tahun_pembuatan,
            'value' => null,
            'status_verifikasi' => $asset->status_verifikasi,
            'description' => $asset->keterangan,
            'detail_rows' => [
                'Nomor Seri Pabrik' => $asset->no_ser_model ?: '-',
                'Ukuran' => $asset->ukuran ?: '-',
                'Bahan' => $asset->bahan ?: '-',
                'Jumlah' => $asset->jumlah . ' ' . $asset->satuan,
                'Tahun Pembelian' => $asset->tahun_pembuatan,
            ],
        ]];
    }
}
