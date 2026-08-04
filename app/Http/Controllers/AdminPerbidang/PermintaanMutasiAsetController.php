<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Models\PermintaanMutasiAset;
use App\Support\SystemNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermintaanMutasiAsetController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = [
            'status' => $request->input('status', 'Semua Status'),
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'search' => $request->input('search'),
        ];

        $query = PermintaanMutasiAset::with(['bidangPeminta', 'peminta', 'pemroses', 'mutasiAset'])
            ->where('bidang_peminta_id', $user->bidang_id)
            ->latest();

        if ($filters['status'] !== 'Semua Status') {
            $query->where('status', $filters['status']);
        }

        if ($filters['jenis'] !== 'Semua Jenis') {
            $query->where('jenis_aset', $filters['jenis']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_kebutuhan', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kategori_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('lokasi_penggunaan', 'like', '%' . $filters['search'] . '%');
            });
        }

        return view('pages.admin-perbidang.PermintaanMutasi.index', [
            'permintaan' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'pendingCount' => $this->countByStatus($user->bidang_id, 'Menunggu Verifikasi'),
            'fulfilledCount' => $this->countByStatus($user->bidang_id, 'Dipenuhi'),
            'rejectedCount' => $this->countByStatus($user->bidang_id, 'Ditolak'),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin-perbidang.PermintaanMutasi.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_aset' => ['required', 'string', Rule::in(['register', 'smki'])],
            'kategori_aset' => ['required', 'string', 'max:255'],
            'nama_kebutuhan' => ['required', 'string', 'max:255'],
            'lokasi_penggunaan' => ['required', 'string', 'max:255'],
            'tanggal_permintaan' => ['required', 'date'],
            'spesifikasi' => ['nullable', 'string', 'max:2000'],
            'alasan' => ['required', 'string', 'min:10', 'max:3000'],
        ], [], [
            'jenis_aset' => 'Jenis Aset',
            'kategori_aset' => 'Kategori Aset',
            'nama_kebutuhan' => 'Nama Kebutuhan',
            'lokasi_penggunaan' => 'Lokasi Penggunaan',
            'tanggal_permintaan' => 'Tanggal Permintaan',
            'spesifikasi' => 'Spesifikasi',
            'alasan' => 'Alasan Permintaan',
        ]);

        $user = $request->user();

        $permintaan = PermintaanMutasiAset::create([
            ...$validated,
            'status' => 'Menunggu Verifikasi',
            'bidang_peminta_id' => $user->bidang_id,
            'diminta_oleh' => $user->id,
        ]);

        $bidangName = $user->bidang->nama_bidang ?? 'Admin Perbidang';

        SystemNotifier::notifyRoles(
            'Super Admin',
            'Permintaan mutasi aset baru',
            "{$bidangName} meminta {$permintaan->nama_kebutuhan} untuk dimutasikan ke bidangnya.",
            route('super-admin.permintaan-mutasi.show', $permintaan->id),
            'warning',
            'mutasi'
        );

        return redirect()
            ->route('admin-perbidang.permintaan-mutasi.index')
            ->with('success', 'Permintaan mutasi aset berhasil dikirim ke Super Admin.');
    }

    public function show(PermintaanMutasiAset $permintaan_mutasi): View
    {
        abort_unless((int) $permintaan_mutasi->bidang_peminta_id === (int) auth()->user()->bidang_id, 403);

        $permintaan_mutasi->load([
            'bidangPeminta',
            'peminta',
            'pemroses',
            'mutasiAset.asetRegister',
            'mutasiAset.asetSmki',
            'mutasiAset.bidangAsal',
            'mutasiAset.bidangTujuan',
        ]);

        return view('pages.admin-perbidang.PermintaanMutasi.show', [
            'permintaan' => $permintaan_mutasi,
        ]);
    }

    private function countByStatus(?int $bidangId, string $status): int
    {
        return PermintaanMutasiAset::where('bidang_peminta_id', $bidangId)
            ->where('status', $status)
            ->count();
    }
}
