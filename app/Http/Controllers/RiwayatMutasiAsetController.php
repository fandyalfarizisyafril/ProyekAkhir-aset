<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatMutasiAsetController extends Controller
{
    /**
     * Tampilkan riwayat perpindahan aset sesuai cakupan akses aktor.
     */
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'status' => $request->input('status', 'Semua Status'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_selesai' => $request->input('tanggal_selesai'),
            'search' => $request->input('search'),
        ];

        $query = $this->visibleQuery($request->user())
            ->with($this->relations())
            ->latest('tanggal_mutasi')
            ->latest();

        $this->applyFilters($query, $filters);

        return view('pages.riwayat-mutasi.index', [
            'mutasi' => $query->paginate(10)->withQueryString(),
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'totalCount' => (clone $this->visibleQuery($request->user()))->count(),
            'approvedCount' => (clone $this->visibleQuery($request->user()))->where('status', 'Disetujui')->count(),
            'pendingCount' => (clone $this->visibleQuery($request->user()))->where('status', 'Menunggu Verifikasi')->count(),
            'rejectedCount' => (clone $this->visibleQuery($request->user()))->where('status', 'Ditolak')->count(),
            'isKepalaDinas' => $request->user()->role === 'Kepala Dinas',
        ]);
    }

    /**
     * Tampilkan detail histori mutasi.
     */
    public function show(MutasiAset $mutasi_aset): View
    {
        abort_unless($this->canView(auth()->user(), $mutasi_aset), 403);

        $mutasi_aset->load($this->relations());

        return view('pages.riwayat-mutasi.show', [
            'mutasi' => $mutasi_aset,
        ]);
    }

    private function visibleQuery(User $user): Builder
    {
        $query = MutasiAset::query();

        if (in_array($user->role, ['Super Admin', 'Kepala Dinas'], true)) {
            return $query;
        }

        if ($user->role === 'Admin Perbidang') {
            return $query->where(function ($q) use ($user) {
                $q->where('bidang_asal_id', $user->bidang_id)
                    ->orWhere('bidang_tujuan_id', $user->bidang_id)
                    ->orWhere('diajukan_oleh', $user->id);
            });
        }

        abort(403, 'Role pengguna tidak terdaftar untuk sistem ini.');
    }

    private function canView(User $user, MutasiAset $mutasi): bool
    {
        if (in_array($user->role, ['Super Admin', 'Kepala Dinas'], true)) {
            return true;
        }

        if ($user->role === 'Admin Perbidang') {
            return $mutasi->bidang_asal_id === $user->bidang_id
                || $mutasi->bidang_tujuan_id === $user->bidang_id
                || $mutasi->diajukan_oleh === $user->id;
        }

        return false;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
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

        if ($filters['tanggal_mulai']) {
            $query->whereDate('tanggal_mutasi', '>=', $filters['tanggal_mulai']);
        }

        if ($filters['tanggal_selesai']) {
            $query->whereDate('tanggal_mutasi', '<=', $filters['tanggal_selesai']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('alasan', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('asetRegister', function ($assetQuery) use ($filters) {
                        $assetQuery->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('asetSmki', function ($assetQuery) use ($filters) {
                        $assetQuery->where('merk_model', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('bidangAsal', function ($bidangQuery) use ($filters) {
                        $bidangQuery->where('nama_bidang', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('bidangTujuan', function ($bidangQuery) use ($filters) {
                        $bidangQuery->where('nama_bidang', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('pemohon', function ($userQuery) use ($filters) {
                        $userQuery->where('nama', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('nip', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }
    }

    private function relations(): array
    {
        return [
            'asetRegister',
            'asetSmki',
            'bidangAsal',
            'bidangTujuan',
            'pemohon.bidang',
            'penyetuju',
            'permintaanMutasi.bidangPeminta',
            'permintaanMutasi.peminta',
        ];
    }
}
