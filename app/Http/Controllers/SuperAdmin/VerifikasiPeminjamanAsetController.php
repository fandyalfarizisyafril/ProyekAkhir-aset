<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PeminjamanAset;
use App\Support\SystemNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiPeminjamanAsetController extends Controller
{
    /**
     * Tampilkan daftar pengajuan peminjaman aset.
     */
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'status' => $request->input('status', 'Menunggu Verifikasi'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];

        $query = PeminjamanAset::with($this->relations())->latest();
        $this->applyFilters($query, $filters);

        return view('pages.super-admin.VerifikasiPeminjaman.index', [
            'peminjaman' => $query->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'pendingCount' => $this->countByStatus('Menunggu Verifikasi'),
            'approvedCount' => $this->countByStatus('Disetujui'),
            'rejectedCount' => $this->countByStatus('Ditolak'),
        ]);
    }

    /**
     * Tampilkan detail pengajuan peminjaman aset.
     */
    public function show(PeminjamanAset $peminjaman_aset): View
    {
        $peminjaman_aset->load($this->relations());

        return view('pages.super-admin.VerifikasiPeminjaman.show', [
            'peminjaman' => $peminjaman_aset,
        ]);
    }

    /**
     * Setujui pengajuan peminjaman dan ubah status aset menjadi Dipinjam.
     */
    public function approve(PeminjamanAset $peminjaman_aset): RedirectResponse
    {
        if ($peminjaman_aset->status !== 'Menunggu Verifikasi') {
            return redirect()
                ->route('super-admin.verifikasi-peminjaman.index')
                ->with('error', 'Pengajuan peminjaman ini sudah diproses sebelumnya.');
        }

        try {
            DB::transaction(function () use ($peminjaman_aset) {
                $asset = $this->resolveAsset($peminjaman_aset);

                if (($asset->status ?? 'Tersedia') === 'Dipinjam') {
                    abort(409, 'Aset sedang berstatus Dipinjam.');
                }

                $asset->update(['status' => 'Dipinjam']);

                $peminjaman_aset->update([
                    'status' => 'Disetujui',
                    'disetujui_oleh' => auth()->id(),
                ]);
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            if ($exception->getStatusCode() === 409) {
                return redirect()
                    ->route('super-admin.verifikasi-peminjaman.index')
                    ->with('error', $exception->getMessage());
            }

            throw $exception;
        }

        $peminjaman_aset->load(['asetRegister', 'asetSmki', 'peminjam']);

        SystemNotifier::notifyUser(
            $peminjaman_aset->peminjam,
            'Peminjaman aset disetujui',
            $this->assetTitle($peminjaman_aset) . ' sudah disetujui oleh Super Admin.',
            route('admin-perbidang.peminjaman-aset.show', $peminjaman_aset->id),
            'success',
            'peminjaman'
        );

        return redirect()
            ->route('super-admin.verifikasi-peminjaman.index')
            ->with('success', 'Peminjaman aset berhasil disetujui dan status aset menjadi Dipinjam.');
    }

    /**
     * Tolak pengajuan peminjaman tanpa mengubah status aset.
     */
    public function reject(PeminjamanAset $peminjaman_aset): RedirectResponse
    {
        if ($peminjaman_aset->status !== 'Menunggu Verifikasi') {
            return redirect()
                ->route('super-admin.verifikasi-peminjaman.index')
                ->with('error', 'Pengajuan peminjaman ini sudah diproses sebelumnya.');
        }

        $peminjaman_aset->update([
            'status' => 'Ditolak',
            'disetujui_oleh' => auth()->id(),
        ]);

        $peminjaman_aset->load(['asetRegister', 'asetSmki', 'peminjam']);

        SystemNotifier::notifyUser(
            $peminjaman_aset->peminjam,
            'Peminjaman aset ditolak',
            $this->assetTitle($peminjaman_aset) . ' ditolak oleh Super Admin.',
            route('admin-perbidang.peminjaman-aset.show', $peminjaman_aset->id),
            'danger',
            'peminjaman'
        );

        return redirect()
            ->route('super-admin.verifikasi-peminjaman.index')
            ->with('success', 'Peminjaman aset berhasil ditolak.');
    }

    private function applyFilters($query, array $filters): void
    {
        if ($filters['jenis'] !== 'Semua Jenis') {
            $query->where('jenis_aset', $filters['jenis']);
        }

        if ($filters['status'] !== 'Semua Status') {
            $query->where('status', $filters['status']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('asetRegister', fn ($assetQuery) => $assetQuery->where('bidang_id', $filters['bidang_id']))
                    ->orWhereHas('asetSmki', fn ($assetQuery) => $assetQuery->where('bidang_id', $filters['bidang_id']))
                    ->orWhereHas('peminjam', fn ($userQuery) => $userQuery->where('bidang_id', $filters['bidang_id']));
            });
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('keperluan', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('nama_peminjam', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('asetRegister', function ($assetQuery) use ($filters) {
                        $assetQuery->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('asetSmki', function ($assetQuery) use ($filters) {
                        $assetQuery->where('merk_model', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('peminjam', function ($userQuery) use ($filters) {
                        $userQuery->where('nama', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('nip', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }
    }

    private function countByStatus(string $status): int
    {
        return PeminjamanAset::where('status', $status)->count();
    }

    private function resolveAsset(PeminjamanAset $peminjaman): AsetRegister|AsetSmki
    {
        if ($peminjaman->jenis_aset === 'register') {
            return AsetRegister::findOrFail($peminjaman->aset_register_id);
        }

        return AsetSmki::findOrFail($peminjaman->aset_smki_id);
    }

    private function relations(): array
    {
        return [
            'asetRegister.bidang',
            'asetSmki.bidang',
            'bidangAsal',
            'peminjam.bidang',
            'penyetuju',
        ];
    }

    private function assetTitle(PeminjamanAset $peminjaman): string
    {
        if ($peminjaman->jenis_aset === 'register') {
            return $peminjaman->asetRegister->nama_aset ?? 'Aset Register';
        }

        return $peminjaman->asetSmki->merk_model ?? 'Aset SMKI';
    }
}
