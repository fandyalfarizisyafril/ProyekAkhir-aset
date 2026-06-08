<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StoreAsetSmkiRequest;
use App\Http\Requests\AdminPerbidang\UpdateAsetSmkiRequest;
use App\Models\AsetSmki;
use App\Models\Bidang;
use Illuminate\Http\Request;

class DataAsetSMKIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        // Base Query scoped to the admin's bidang
        $query = AsetSmki::with(['bidang', 'inputter'])
            ->where('bidang_id', $bidangId);

        // Fetch categories dynamically for filters
        $kategoris = AsetSmki::where('bidang_id', $bidangId)
            ->distinct()
            ->pluck('jenis_barang');

        // Filters
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $status = $request->input('status');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('merk_model', 'like', '%' . $search . '%')
                  ->orWhere('nomor_kode_barang', 'like', '%' . $search . '%')
                  ->orWhere('penanggung_jawab', 'like', '%' . $search . '%')
                  ->orWhere('ruangan', 'like', '%' . $search . '%');
            });
        }

        if ($kategori && $kategori !== 'Semua Kategori') {
            $query->where('jenis_barang', $kategori);
        }

        if ($status && $status !== 'Semua Status') {
            $query->where('status_verifikasi', $status);
        }

        // Paginate (10 per page)
        $assets = $query->paginate(10)->withQueryString();

        // Calculate Statistics (scoped to the admin's bidang)
        $totalAset = AsetSmki::where('bidang_id', $bidangId)->count();
        $aktifCount = AsetSmki::where('bidang_id', $bidangId)->where('keadaan_barang', 'Baik')->count();
        $maintenanceCount = AsetSmki::where('bidang_id', $bidangId)->where('keadaan_barang', 'Rusak Ringan')->count();

        return view('pages.admin-perbidang.DataAserSmki.index', compact(
            'assets',
            'kategoris',
            'totalAset',
            'aktifCount',
            'maintenanceCount',
            'search',
            'kategori',
            'status'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin-perbidang.DataAserSmki.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAsetSmkiRequest $request)
    {
        $validated = $request->validated();

        // Dynamically assign fields
        $validated['bidang_id'] = auth()->user()->bidang_id;
        $validated['dinput_oleh'] = auth()->id();
        $validated['status_verifikasi'] = 'Perlu Verifikasi';

        AsetSmki::create($validated);

        return redirect()->route('admin-perbidang.data-aset-smki.index')
            ->with('success', 'Aset SMKI baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AsetSmki $data_aset_smki)
    {
        // Redirect to edit/index or show a read-only detail view
        return redirect()->route('admin-perbidang.data-aset-smki.edit', $data_aset_smki->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AsetSmki $data_aset_smki)
    {
        // Authorization check to ensure admin only edits their own bidang's assets
        if ($data_aset_smki->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
        }

        return view('pages.admin-perbidang.DataAserSmki.edit', [
            'asset' => $data_aset_smki
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAsetSmkiRequest $request, AsetSmki $data_aset_smki)
    {
        // Authorization check
        if ($data_aset_smki->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
        }

        $validated = $request->validated();
        
        $data_aset_smki->update($validated);

        return redirect()->route('admin-perbidang.data-aset-smki.index')
            ->with('success', 'Data Aset SMKI berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AsetSmki $data_aset_smki)
    {
        // Authorization check
        if ($data_aset_smki->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data aset ini.');
        }

        $data_aset_smki->delete();

        return redirect()->route('admin-perbidang.data-aset-smki.index')
            ->with('success', 'Aset SMKI berhasil dihapus.');
    }
}
