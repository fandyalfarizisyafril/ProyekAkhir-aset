<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\PermintaanMutasiAset;
use App\Support\SystemNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermintaanMutasiAsetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'status' => $request->input('status', 'Menunggu Verifikasi'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];

        $query = PermintaanMutasiAset::with(['bidangPeminta', 'peminta', 'pemroses', 'mutasiAset'])
            ->latest();

        if ($filters['jenis'] !== 'Semua Jenis') {
            $query->where('jenis_aset', $filters['jenis']);
        }

        if ($filters['status'] !== 'Semua Status') {
            $query->where('status', $filters['status']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_peminta_id', $filters['bidang_id']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_kebutuhan', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kategori_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('peminta', function ($userQuery) use ($filters) {
                        $userQuery->where('nama', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('nip', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        return view('pages.super-admin.PermintaanMutasi.index', [
            'permintaan' => $query->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'pendingCount' => $this->countByStatus('Menunggu Verifikasi'),
            'fulfilledCount' => $this->countByStatus('Dipenuhi'),
            'rejectedCount' => $this->countByStatus('Ditolak'),
        ]);
    }

    public function show(Request $request, PermintaanMutasiAset $permintaan_mutasi): View
    {
        $permintaan_mutasi->load([
            'bidangPeminta',
            'peminta',
            'pemroses',
            'mutasiAset.asetRegister',
            'mutasiAset.asetSmki',
            'mutasiAset.bidangAsal',
            'mutasiAset.bidangTujuan',
            'mutasiAset.pemohon',
            'mutasiAset.penyetuju',
        ]);

        return view('pages.super-admin.PermintaanMutasi.show', [
            'permintaan' => $permintaan_mutasi,
            'candidateAssets' => $this->candidateAssets($permintaan_mutasi, $request->input('asset_search')),
            'assetSearch' => $request->input('asset_search'),
        ]);
    }

    public function fulfill(Request $request, PermintaanMutasiAset $permintaan_mutasi): RedirectResponse
    {
        if ($permintaan_mutasi->status !== 'Menunggu Verifikasi') {
            return redirect()
                ->route('super-admin.permintaan-mutasi.index')
                ->with('error', 'Permintaan mutasi ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'asset_choice' => ['required', 'string'],
            'catatan_super_admin' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'asset_choice' => 'Aset Yang Dipilih',
            'catatan_super_admin' => 'Catatan Super Admin',
        ]);

        [$type, $assetId] = $this->parseAssetChoice($validated['asset_choice']);

        if ($type !== $permintaan_mutasi->jenis_aset) {
            return back()
                ->withErrors(['asset_choice' => 'Jenis aset yang dipilih tidak sesuai dengan permintaan.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($permintaan_mutasi, $type, $assetId, $validated): void {
                $asset = $this->resolveCandidateAsset($type, $assetId, $permintaan_mutasi->bidang_peminta_id);
                $bidangAsalId = $asset->bidang_id;

                $mutasi = MutasiAset::create([
                    'jenis_aset' => $type,
                    'aset_register_id' => $type === 'register' ? $asset->id : null,
                    'aset_smki_id' => $type === 'smki' ? $asset->id : null,
                    'bidang_asal_id' => $bidangAsalId,
                    'bidang_tujuan_id' => $permintaan_mutasi->bidang_peminta_id,
                    'alasan' => 'Pemenuhan permintaan mutasi: ' . $permintaan_mutasi->alasan,
                    'status' => 'Disetujui',
                    'diajukan_oleh' => $permintaan_mutasi->diminta_oleh,
                    'disetujui_oleh' => auth()->id(),
                    'tanggal_mutasi' => now()->toDateString(),
                ]);

                if ($type === 'register') {
                    $asset->update([
                        'bidang_id' => $permintaan_mutasi->bidang_peminta_id,
                        'lokasi_aset' => $permintaan_mutasi->lokasi_penggunaan,
                        'status' => 'Tersedia',
                    ]);
                } else {
                    $asset->update([
                        'bidang_id' => $permintaan_mutasi->bidang_peminta_id,
                        'ruangan' => $permintaan_mutasi->lokasi_penggunaan,
                        'status' => 'Tersedia',
                    ]);
                }

                $permintaan_mutasi->update([
                    'status' => 'Dipenuhi',
                    'diproses_oleh' => auth()->id(),
                    'mutasi_aset_id' => $mutasi->id,
                    'catatan_super_admin' => $validated['catatan_super_admin'] ?? null,
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()
                ->withErrors(['asset_choice' => 'Aset yang dipilih tidak tersedia atau sudah berpindah.'])
                ->withInput();
        }

        $permintaan_mutasi->load(['peminta', 'mutasiAset.asetRegister', 'mutasiAset.asetSmki']);

        SystemNotifier::notifyUser(
            $permintaan_mutasi->peminta,
            'Permintaan mutasi dipenuhi',
            $this->assetTitle($permintaan_mutasi->mutasiAset) . ' sudah dimutasikan ke bidang Anda.',
            route('admin-perbidang.permintaan-mutasi.show', $permintaan_mutasi->id),
            'success',
            'mutasi'
        );

        return redirect()
            ->route('super-admin.permintaan-mutasi.index')
            ->with('success', 'Permintaan mutasi berhasil dipenuhi dan aset sudah dipindahkan.');
    }

    public function reject(Request $request, PermintaanMutasiAset $permintaan_mutasi): RedirectResponse
    {
        if ($permintaan_mutasi->status !== 'Menunggu Verifikasi') {
            return redirect()
                ->route('super-admin.permintaan-mutasi.index')
                ->with('error', 'Permintaan mutasi ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_super_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $permintaan_mutasi->update([
            'status' => 'Ditolak',
            'diproses_oleh' => auth()->id(),
            'catatan_super_admin' => $validated['catatan_super_admin'] ?? null,
        ]);

        $permintaan_mutasi->load('peminta');

        SystemNotifier::notifyUser(
            $permintaan_mutasi->peminta,
            'Permintaan mutasi ditolak',
            $permintaan_mutasi->nama_kebutuhan . ' belum dapat dipenuhi oleh Super Admin.',
            route('admin-perbidang.permintaan-mutasi.show', $permintaan_mutasi->id),
            'danger',
            'mutasi'
        );

        return redirect()
            ->route('super-admin.permintaan-mutasi.index')
            ->with('success', 'Permintaan mutasi berhasil ditolak.');
    }

    private function countByStatus(string $status): int
    {
        return PermintaanMutasiAset::where('status', $status)->count();
    }

    private function candidateAssets(PermintaanMutasiAset $request, ?string $search)
    {
        $query = $request->jenis_aset === 'register'
            ? $this->registerCandidateQuery($request)
            : $this->smkiCandidateQuery($request);

        if ($search) {
            $query->where(function ($q) use ($request, $search) {
                if ($request->jenis_aset === 'register') {
                    $q->where('nama_aset', 'like', '%' . $search . '%')
                        ->orWhere('kode_aset', 'like', '%' . $search . '%')
                        ->orWhere('kode_barang', 'like', '%' . $search . '%');
                } else {
                    $q->where('merk_model', 'like', '%' . $search . '%')
                        ->orWhere('nomor_kode_barang', 'like', '%' . $search . '%')
                        ->orWhere('jenis_barang', 'like', '%' . $search . '%');
                }
            });
        }

        return $query
            ->orderByRaw($request->jenis_aset === 'register'
                ? 'CASE WHEN kode_barang = ? THEN 0 ELSE 1 END'
                : 'CASE WHEN jenis_barang = ? THEN 0 ELSE 1 END', [$request->kategori_aset])
            ->latest()
            ->limit(25)
            ->get();
    }

    private function registerCandidateQuery(PermintaanMutasiAset $request): Builder
    {
        return AsetRegister::notDeleted()
            ->with('bidang')
            ->where('status_verifikasi', 'Terverifikasi')
            ->where('status', 'Bisa dimutasi')
            ->where('bidang_id', '!=', $request->bidang_peminta_id);
    }

    private function smkiCandidateQuery(PermintaanMutasiAset $request): Builder
    {
        return AsetSmki::notDeleted()
            ->with('bidang')
            ->where('status_verifikasi', 'Terverifikasi')
            ->where('status', 'Bisa dimutasi')
            ->where('bidang_id', '!=', $request->bidang_peminta_id);
    }

    private function parseAssetChoice(string $assetChoice): array
    {
        if (! str_contains($assetChoice, ':')) {
            abort(422, 'Format aset tidak valid.');
        }

        [$type, $id] = explode(':', $assetChoice, 2);

        if (! in_array($type, ['register', 'smki'], true) || ! ctype_digit($id)) {
            abort(422, 'Aset yang dipilih tidak valid.');
        }

        return [$type, (int) $id];
    }

    private function resolveCandidateAsset(string $type, int $assetId, int $requesterBidangId): AsetRegister|AsetSmki
    {
        if ($type === 'register') {
            return AsetRegister::notDeleted()
                ->where('status_verifikasi', 'Terverifikasi')
                ->where('status', 'Bisa dimutasi')
                ->where('bidang_id', '!=', $requesterBidangId)
                ->findOrFail($assetId);
        }

        return AsetSmki::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi')
            ->where('status', 'Bisa dimutasi')
            ->where('bidang_id', '!=', $requesterBidangId)
            ->findOrFail($assetId);
    }

    private function assetTitle(?MutasiAset $mutasi): string
    {
        if (! $mutasi) {
            return 'Aset';
        }

        if ($mutasi->jenis_aset === 'register') {
            return $mutasi->asetRegister->nama_aset ?? 'Aset Register';
        }

        return $mutasi->asetSmki->merk_model ?? 'Aset SMKI';
    }
}
