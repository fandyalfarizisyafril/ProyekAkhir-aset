<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StoreAsetRegisterRequest;
use App\Http\Requests\AdminPerbidang\UpdateAsetRegisterRequest;
use App\Models\AsetRegister;
use App\Models\KategoriAset;
use App\Support\SystemNotifier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataAsetRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        $kategoris = $this->registerCategories()
            ->merge(AsetRegister::notDeleted()->where('bidang_id', $bidangId)->whereNotNull('kode_barang')->distinct()->pluck('kode_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Filters
        $search = $request->input('search');
        $kategori = $request->input('kategori', 'Semua Kategori');
        $status = $request->input('status', 'Semua Status');

        $query = $this->filteredRegisterQuery($request, $bidangId);

        // Paginate (10 per page)
        $assets = $query->paginate(10)->withQueryString();

        // Calculate Statistics (scoped to the admin's bidang)
        $totalAset = AsetRegister::notDeleted()->where('bidang_id', $bidangId)->count();
        $pendingCount = AsetRegister::notDeleted()->where('bidang_id', $bidangId)->where('status_verifikasi', 'Perlu Verifikasi')->count();
        $verifiedCount = AsetRegister::notDeleted()->where('bidang_id', $bidangId)->where('status_verifikasi', 'Terverifikasi')->count();
        $rejectedCount = AsetRegister::notDeleted()->where('bidang_id', $bidangId)->where('status_verifikasi', 'Ditolak')->count();

        return view('pages.admin-perbidang.DataAsetRegister.index', compact(
            'assets',
            'totalAset',
            'pendingCount',
            'verifiedCount',
            'rejectedCount',
            'kategoris',
            'search',
            'kategori',
            'status'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $bidangId = (int) $request->user()->bidang_id;
        $assets = $this->filteredRegisterQuery($request, $bidangId)
            ->orderBy('kode_aset')
            ->get();

        $filename = 'data-aset-register-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($assets): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, '<html><head><meta charset="UTF-8"></head><body><table border="1">');

            $this->writeExcelRow($handle, [
                'Kode Aset',
                'Nama Aset',
                'Kategori',
                'Kode Urut Barang',
                'Bidang',
                'Kondisi',
                'Status Aset',
                'Status Verifikasi',
                'Pengguna',
                'Lokasi Aset',
                'Pemilik Aset',
                'Kerahasiaan',
                'Kritikalitas',
                'Nilai Perolehan',
                'Tanggal Perolehan',
                'Keterangan',
                'Diinput Oleh',
                'Tanggal Input',
            ], 'th');

            foreach ($assets as $asset) {
                $this->writeExcelRow($handle, [
                    $asset->kode_aset,
                    $asset->nama_aset,
                    $asset->kode_barang,
                    $asset->kode_urut_barang,
                    $asset->bidang->nama_bidang ?? '-',
                    $asset->kondisi ?? $asset->status_barang,
                    $this->displayAssetStatus($asset->status),
                    $asset->status_verifikasi,
                    $asset->pengguna,
                    $asset->lokasi_aset,
                    $asset->pemilik_aset,
                    $asset->kerahasiaan,
                    $asset->kritikalitas,
                    $asset->nilai,
                    $asset->tanggal_perolehan?->format('d/m/Y'),
                    $asset->keterangan,
                    $asset->inputter->name ?? '-',
                    $asset->created_at?->format('d/m/Y H:i'),
                ]);
            }

            fwrite($handle, '</table></body></html>');
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin-perbidang.DataAsetRegister.create', [
            'categories' => $this->registerCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAsetRegisterRequest $request)
    {
        $validated = $request->validated();
        $validated['kode_barang'] = trim($validated['kode_barang']);

        // Dynamically assign fields
        $validated['bidang_id'] = auth()->user()->bidang_id;
        $validated['dinput_oleh'] = auth()->id();
        $validated['kondisi'] = $validated['status_barang'];
        
        // Map status based on status_barang selection
        $statusMap = [
            'Baik' => 'Tersedia',
            'Rusak Ringan' => 'Maintenance',
            'Rusak Berat' => 'Rusak',
        ];
        $validated['status'] = $statusMap[$validated['status_barang']] ?? 'Tersedia';
        $validated['status_verifikasi'] = 'Perlu Verifikasi';

        $asset = AsetRegister::create($validated);
        $bidangName = auth()->user()->bidang->nama_bidang ?? 'Admin Perbidang';

        SystemNotifier::notifyRoles(
            'Super Admin',
            'Aset Register menunggu verifikasi',
            "{$asset->nama_aset} dari {$bidangName} perlu ditinjau.",
            route('super-admin.verifikasi-aset.show', ['register', $asset->id]),
            'warning',
            'aset'
        );

        return redirect()->route('admin-perbidang.data-aset-register.index')
            ->with('success', 'Aset register baru berhasil ditambahkan dan menunggu verifikasi Super Admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AsetRegister $data_aset_register)
    {
        if ($data_aset_register->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data aset ini.');
        }

        $data_aset_register->load([
            'bidang',
            'inputter',
            'verifier',
            'riwayatKondisi.updater',
            'mutasi.bidangAsal',
            'mutasi.bidangTujuan',
            'mutasi.pemohon',
            'mutasi.penyetuju',
            'peminjaman.bidangAsal',
            'peminjaman.peminjam',
            'peminjaman.penyetuju',
            'penyusutan',
            'penghapusan.remover',
        ]);

        return view('pages.admin-perbidang.DataAsetRegister.show', [
            'asset' => $data_aset_register,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AsetRegister $data_aset_register)
    {
        // Authorization check to ensure admin only edits their own bidang's assets
        if ($data_aset_register->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
        }

        return view('pages.admin-perbidang.DataAsetRegister.edit', [
            'asset' => $data_aset_register,
            'categories' => $this->registerCategories(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAsetRegisterRequest $request, AsetRegister $data_aset_register)
    {
        // Authorization check
        if ($data_aset_register->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
        }

        $validated = $request->validated();
        $validated['kode_barang'] = trim($validated['kode_barang']);
        
        $validated['kondisi'] = $validated['status_barang'];
        
        // Map status based on status_barang selection
        $statusMap = [
            'Baik' => 'Tersedia',
            'Rusak Ringan' => 'Maintenance',
            'Rusak Berat' => 'Rusak',
        ];
        $validated['status'] = $statusMap[$validated['status_barang']] ?? 'Tersedia';

        $data_aset_register->update($validated);

        return redirect()->route('admin-perbidang.data-aset-register.index')
            ->with('success', 'Data Aset register berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AsetRegister $data_aset_register)
    {
        abort(403, 'Penghapusan aset hanya dapat dilakukan oleh Super Admin melalui menu Penghapusan Aset.');
    }

    private function registerCategories()
    {
        return KategoriAset::where('tipe', 'Register')
            ->pluck('nama_kategori')
            ->merge(AsetRegister::notDeleted()->whereNotNull('kode_barang')->distinct()->pluck('kode_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function filteredRegisterQuery(Request $request, int $bidangId)
    {
        $query = AsetRegister::notDeleted()
            ->with(['bidang', 'inputter'])
            ->where('bidang_id', $bidangId);

        $search = $request->input('search');
        $kategori = $request->input('kategori', 'Semua Kategori');
        $status = $request->input('status', 'Semua Status');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_aset', 'like', '%' . $search . '%')
                  ->orWhere('kode_aset', 'like', '%' . $search . '%')
                  ->orWhere('pengguna', 'like', '%' . $search . '%')
                  ->orWhere('lokasi_aset', 'like', '%' . $search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $search . '%');
            });
        }

        if ($kategori && $kategori !== 'Semua Kategori') {
            $query->where('kode_barang', $kategori);
        }

        if ($status && $status !== 'Semua Status') {
            $query->where('status_verifikasi', $status);
        }

        return $query;
    }

    private function displayAssetStatus(?string $status): string
    {
        return match ($status) {
            null, 'Aktif' => 'Tersedia',
            default => $status,
        };
    }

    private function writeExcelRow($handle, array $values, string $cellTag = 'td'): void
    {
        fwrite($handle, '<tr>');

        foreach ($values as $value) {
            fwrite($handle, sprintf(
                '<%1$s>%2$s</%1$s>',
                $cellTag,
                htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES, 'UTF-8')
            ));
        }

        fwrite($handle, '</tr>');
    }
}
