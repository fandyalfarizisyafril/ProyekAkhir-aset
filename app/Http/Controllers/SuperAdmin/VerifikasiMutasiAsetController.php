<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Support\SystemNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiMutasiAsetController extends Controller
{
    /**
     * Tampilkan daftar pengajuan mutasi aset untuk diverifikasi.
     */
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'status' => $request->input('status', 'Menunggu Verifikasi'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'search' => $request->input('search'),
        ];

        $query = MutasiAset::with([
            'asetRegister',
            'asetSmki',
            'bidangAsal',
            'bidangTujuan',
            'pemohon.bidang',
            'penyetuju',
        ])->latest();

        if ($filters['jenis'] !== 'Semua Jenis') {
            $query->where('jenis_aset', $filters['jenis']);
        }

        if ($filters['status'] !== 'Semua Status') {
            $query->where('status', $filters['status']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where(function ($q) use ($filters) {
                $q->where('bidang_asal_id', $filters['bidang_id'])
                    ->orWhere('bidang_tujuan_id', $filters['bidang_id']);
            });
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('asetRegister', function ($assetQuery) use ($filters) {
                    $assetQuery->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%');
                })->orWhereHas('asetSmki', function ($assetQuery) use ($filters) {
                    $assetQuery->where('merk_model', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%');
                })->orWhereHas('pemohon', function ($userQuery) use ($filters) {
                    $userQuery->where('nama', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('nip', 'like', '%' . $filters['search'] . '%');
                });
            });
        }

        return view('pages.super-admin.VerifikasiMutasi.index', [
            'mutasi' => $query->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'pendingCount' => $this->countByStatus('Menunggu Verifikasi'),
            'approvedCount' => $this->countByStatus('Disetujui'),
            'rejectedCount' => $this->countByStatus('Ditolak'),
        ]);
    }

    /**
     * Tampilkan detail pengajuan mutasi aset.
     */
    public function show(MutasiAset $mutasi_aset): View
    {
        $mutasi_aset->load([
            'asetRegister',
            'asetSmki',
            'bidangAsal',
            'bidangTujuan',
            'pemohon.bidang',
            'penyetuju',
        ]);

        return view('pages.super-admin.VerifikasiMutasi.show', [
            'mutasi' => $mutasi_aset,
        ]);
    }

    /**
     * Setujui mutasi dan pindahkan bidang/lokasi aset secara otomatis.
     */
    public function approve(MutasiAset $mutasi_aset): RedirectResponse
    {
        if ($mutasi_aset->status !== 'Menunggu Verifikasi') {
            return redirect()
                ->route('super-admin.verifikasi-mutasi.index')
                ->with('error', 'Pengajuan mutasi ini sudah diproses sebelumnya.');
        }

        $mutasi_aset->load(['bidangTujuan']);

        try {
            DB::transaction(function () use ($mutasi_aset) {
                $asset = $this->resolveAsset($mutasi_aset);

                if ((int) $asset->bidang_id !== (int) $mutasi_aset->bidang_asal_id) {
                    abort(409, 'Bidang aset saat ini tidak lagi sama dengan bidang asal pengajuan.');
                }

                $location = $this->destinationLocation($mutasi_aset);

                if ($mutasi_aset->jenis_aset === 'register') {
                    $asset->update([
                        'bidang_id' => $mutasi_aset->bidang_tujuan_id,
                        'lokasi_aset' => $location,
                    ]);
                } else {
                    $asset->update([
                        'bidang_id' => $mutasi_aset->bidang_tujuan_id,
                        'ruangan' => $location,
                    ]);
                }

                $mutasi_aset->update([
                    'status' => 'Disetujui',
                    'disetujui_oleh' => auth()->id(),
                ]);
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            if ($exception->getStatusCode() === 409) {
                return redirect()
                    ->route('super-admin.verifikasi-mutasi.index')
                    ->with('error', $exception->getMessage());
            }

            throw $exception;
        }

        $mutasi_aset->load(['asetRegister', 'asetSmki', 'pemohon']);

        SystemNotifier::notifyUser(
            $mutasi_aset->pemohon,
            'Mutasi aset disetujui',
            $this->assetTitle($mutasi_aset) . ' sudah disetujui dan lokasi aset diperbarui.',
            route('admin-perbidang.mutasi-aset.show', $mutasi_aset->id),
            'success',
            'mutasi'
        );

        return redirect()
            ->route('super-admin.verifikasi-mutasi.index')
            ->with('success', 'Mutasi aset berhasil disetujui dan lokasi aset sudah diperbarui.');
    }

    /**
     * Tolak pengajuan mutasi tanpa memindahkan aset.
     */
    public function reject(MutasiAset $mutasi_aset): RedirectResponse
    {
        if ($mutasi_aset->status !== 'Menunggu Verifikasi') {
            return redirect()
                ->route('super-admin.verifikasi-mutasi.index')
                ->with('error', 'Pengajuan mutasi ini sudah diproses sebelumnya.');
        }

        $mutasi_aset->update([
            'status' => 'Ditolak',
            'disetujui_oleh' => auth()->id(),
        ]);

        $mutasi_aset->load(['asetRegister', 'asetSmki', 'pemohon']);

        SystemNotifier::notifyUser(
            $mutasi_aset->pemohon,
            'Mutasi aset ditolak',
            $this->assetTitle($mutasi_aset) . ' ditolak oleh Super Admin.',
            route('admin-perbidang.mutasi-aset.show', $mutasi_aset->id),
            'danger',
            'mutasi'
        );

        return redirect()
            ->route('super-admin.verifikasi-mutasi.index')
            ->with('success', 'Mutasi aset berhasil ditolak. Lokasi aset tidak berubah.');
    }

    private function countByStatus(string $status): int
    {
        return MutasiAset::where('status', $status)->count();
    }

    private function resolveAsset(MutasiAset $mutasi): AsetRegister|AsetSmki
    {
        if ($mutasi->jenis_aset === 'register') {
            return AsetRegister::findOrFail($mutasi->aset_register_id);
        }

        return AsetSmki::findOrFail($mutasi->aset_smki_id);
    }

    private function destinationLocation(MutasiAset $mutasi): string
    {
        return $mutasi->bidangTujuan->nama_ruangan
            ?: $mutasi->bidangTujuan->nama_bidang
            ?: 'Lokasi belum ditentukan';
    }

    private function assetTitle(MutasiAset $mutasi): string
    {
        if ($mutasi->jenis_aset === 'register') {
            return $mutasi->asetRegister->nama_aset ?? 'Aset Register';
        }

        return $mutasi->asetSmki->merk_model ?? 'Aset SMKI';
    }
}
