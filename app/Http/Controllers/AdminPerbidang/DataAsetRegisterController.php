<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StoreAsetRegisterRequest;
use App\Http\Requests\AdminPerbidang\UpdateAsetRegisterRequest;
use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\KategoriAset;
use Illuminate\Http\Request;

class DataAsetRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        // Base Query scoped to the admin's bidang
        $query = AsetRegister::with(['bidang', 'inputter'])
            ->where('bidang_id', $bidangId);

        // Filters
        $search = $request->input('search');
        $status = $request->input('status');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_aset', 'like', '%' . $search . '%')
                  ->orWhere('kode_aset', 'like', '%' . $search . '%')
                  ->orWhere('pengguna', 'like', '%' . $search . '%')
                  ->orWhere('lokasi_aset', 'like', '%' . $search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $search . '%');
            });
        }

        if ($status && $status !== 'Semua Status') {
            $query->where('status_verifikasi', $status);
        }

        // Paginate (10 per page)
        $assets = $query->paginate(10)->withQueryString();

        // Calculate Statistics (scoped to the admin's bidang)
        $totalAset = AsetRegister::where('bidang_id', $bidangId)->count();
        $pendingCount = AsetRegister::where('bidang_id', $bidangId)->where('status_verifikasi', 'Perlu Verifikasi')->count();
        $verifiedCount = AsetRegister::where('bidang_id', $bidangId)->where('status_verifikasi', 'Terverifikasi')->count();
        $rejectedCount = AsetRegister::where('bidang_id', $bidangId)->where('status_verifikasi', 'Ditolak')->count();

        return view('pages.admin-perbidang.DataAsetRegister.index', compact(
            'assets',
            'totalAset',
            'pendingCount',
            'verifiedCount',
            'rejectedCount',
            'search',
            'status'
        ));
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

        // Dynamically assign fields
        $validated['bidang_id'] = auth()->user()->bidang_id;
        $validated['dinput_oleh'] = auth()->id();
        $validated['kondisi'] = $validated['status_barang'];
        
        // Map status based on status_barang selection
        $statusMap = [
            'Baik' => 'Aktif',
            'Rusak Ringan' => 'Maintenance',
            'Rusak Berat' => 'Rusak',
        ];
        $validated['status'] = $statusMap[$validated['status_barang']] ?? 'Aktif';
        $validated['status_verifikasi'] = 'Perlu Verifikasi';

        AsetRegister::create($validated);

        return redirect()->route('admin-perbidang.data-aset-register.index')
            ->with('success', 'Aset register baru berhasil ditambahkan dan menunggu verifikasi Super Admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AsetRegister $data_aset_register)
    {
        return redirect()->route('admin-perbidang.data-aset-register.edit', $data_aset_register->id);
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
        
        $validated['kondisi'] = $validated['status_barang'];
        
        // Map status based on status_barang selection
        $statusMap = [
            'Baik' => 'Aktif',
            'Rusak Ringan' => 'Maintenance',
            'Rusak Berat' => 'Rusak',
        ];
        $validated['status'] = $statusMap[$validated['status_barang']] ?? 'Aktif';

        $data_aset_register->update($validated);

        return redirect()->route('admin-perbidang.data-aset-register.index')
            ->with('success', 'Data Aset register berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AsetRegister $data_aset_register)
    {
        // Authorization check
        if ($data_aset_register->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data aset ini.');
        }

        $data_aset_register->delete();

        return redirect()->route('admin-perbidang.data-aset-register.index')
            ->with('success', 'Aset register berhasil dihapus.');
    }

    private function registerCategories()
    {
        return KategoriAset::where('tipe', 'Register')
            ->orderBy('nama_kategori')
            ->pluck('nama_kategori');
    }
}
