<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StoreMutasiAsetRequest;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MutasiAsetController extends Controller
{
    /**
     * Tampilkan riwayat dan daftar pengajuan mutasi bidang pengguna.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $search = $request->input('search');
        $status = $request->input('status', 'Semua Status');

        $query = MutasiAset::with([
            'asetRegister',
            'asetSmki',
            'bidangAsal',
            'bidangTujuan',
            'pemohon',
            'penyetuju',
        ])->where('diajukan_oleh', $user->id);

        if ($status !== 'Semua Status') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('asetRegister', function ($assetQuery) use ($search) {
                    $assetQuery->where('nama_aset', 'like', '%' . $search . '%')
                        ->orWhere('kode_aset', 'like', '%' . $search . '%');
                })->orWhereHas('asetSmki', function ($assetQuery) use ($search) {
                    $assetQuery->where('merk_model', 'like', '%' . $search . '%')
                        ->orWhere('nomor_kode_barang', 'like', '%' . $search . '%');
                });
            });
        }

        $mutasi = $query->latest()->paginate(10)->withQueryString();

        return view('pages.admin-perbidang.MutasiAset.index', [
            'mutasi' => $mutasi,
            'search' => $search,
            'status' => $status,
            'pendingCount' => MutasiAset::where('diajukan_oleh', $user->id)->where('status', 'Menunggu Verifikasi')->count(),
            'approvedCount' => MutasiAset::where('diajukan_oleh', $user->id)->where('status', 'Disetujui')->count(),
            'rejectedCount' => MutasiAset::where('diajukan_oleh', $user->id)->where('status', 'Ditolak')->count(),
        ]);
    }

    /**
     * Tampilkan form pengajuan mutasi.
     */
    public function create(): View
    {
        $bidangId = auth()->user()->bidang_id;

        return view('pages.admin-perbidang.MutasiAset.create', [
            'assets' => $this->availableAssets($bidangId),
            'bidangs' => Bidang::where('id', '!=', $bidangId)->orderBy('nama_bidang')->get(),
        ]);
    }

    /**
     * Simpan pengajuan mutasi baru.
     */
    public function store(StoreMutasiAsetRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();
        $asset = $this->resolveAsset($validated['jenis_aset'], (int) $validated['aset_id'], $user->bidang_id);

        MutasiAset::create([
            'jenis_aset' => $validated['jenis_aset'],
            'aset_register_id' => $validated['jenis_aset'] === 'register' ? $asset->id : null,
            'aset_smki_id' => $validated['jenis_aset'] === 'smki' ? $asset->id : null,
            'bidang_asal_id' => $user->bidang_id,
            'bidang_tujuan_id' => $validated['bidang_tujuan_id'],
            'alasan' => $validated['alasan'],
            'status' => 'Menunggu Verifikasi',
            'diajukan_oleh' => $user->id,
            'tanggal_mutasi' => $validated['tanggal_mutasi'],
        ]);

        return redirect()
            ->route('admin-perbidang.mutasi-aset.index')
            ->with('success', 'Pengajuan mutasi aset berhasil dikirim ke Super Admin.');
    }

    /**
     * Tampilkan detail pengajuan mutasi.
     */
    public function show(MutasiAset $mutasi_aset): View
    {
        abort_unless($mutasi_aset->diajukan_oleh === auth()->id(), 403);

        $mutasi_aset->load([
            'asetRegister',
            'asetSmki',
            'bidangAsal',
            'bidangTujuan',
            'pemohon',
            'penyetuju',
        ]);

        return view('pages.admin-perbidang.MutasiAset.show', [
            'mutasi' => $mutasi_aset,
        ]);
    }

    private function availableAssets(?int $bidangId): Collection
    {
        $registerAssets = AsetRegister::notDeleted()
            ->where('bidang_id', $bidangId)
            ->where('status_verifikasi', 'Terverifikasi')
            ->get()
            ->map(fn (AsetRegister $asset) => (object) [
                'id' => $asset->id,
                'type' => 'register',
                'label' => 'REGISTER - ' . $asset->kode_aset . ' - ' . $asset->nama_aset,
            ]);

        $smkiAssets = AsetSmki::notDeleted()
            ->where('bidang_id', $bidangId)
            ->where('status_verifikasi', 'Terverifikasi')
            ->get()
            ->map(fn (AsetSmki $asset) => (object) [
                'id' => $asset->id,
                'type' => 'smki',
                'label' => 'SMKI - ' . $asset->nomor_kode_barang . ' - ' . $asset->merk_model,
            ]);

        return $registerAssets->concat($smkiAssets)->values();
    }

    private function resolveAsset(string $type, int $id, ?int $bidangId): AsetRegister|AsetSmki
    {
        if ($type === 'register') {
            return AsetRegister::notDeleted()
                ->where('bidang_id', $bidangId)
                ->where('status_verifikasi', 'Terverifikasi')
                ->findOrFail($id);
        }

        return AsetSmki::notDeleted()
            ->where('bidang_id', $bidangId)
            ->where('status_verifikasi', 'Terverifikasi')
            ->findOrFail($id);
    }
}
