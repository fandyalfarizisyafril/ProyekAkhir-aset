<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StoreKondisiAsetRequest;
use App\Http\Requests\AdminPerbidang\UpdateKondisiAsetRequest;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\RiwayatKondisiRegister;
use App\Models\RiwayatKondisiSmki;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class KondisiAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        // Retrieve query parameters
        $search = $request->input('search');
        $category = $request->input('kategori'); // SMKI, REGISTER, or null/Semua
        $condition = $request->input('kondisi'); // Baik, Rusak Ringan, Rusak Berat, or null/Semua

        // Calculate card statistics (always scoped to user's bidang)
        $totalSmki = AsetSmki::where('bidang_id', $bidangId)->count();
        $totalRegister = AsetRegister::where('bidang_id', $bidangId)->count();
        $totalAset = $totalSmki + $totalRegister;

        $baikSmki = AsetSmki::where('bidang_id', $bidangId)->where('keadaan_barang', 'Baik')->count();
        $baikRegister = AsetRegister::where('bidang_id', $bidangId)->where('kondisi', 'Baik')->count();
        $baikCount = $baikSmki + $baikRegister;

        $rusakRinganSmki = AsetSmki::where('bidang_id', $bidangId)->where('keadaan_barang', 'Rusak Ringan')->count();
        $rusakRinganRegister = AsetRegister::where('bidang_id', $bidangId)->where('kondisi', 'Rusak Ringan')->count();
        $rusakRinganCount = $rusakRinganSmki + $rusakRinganRegister;

        $rusakBeratSmki = AsetSmki::where('bidang_id', $bidangId)->where('keadaan_barang', 'Rusak Berat')->count();
        $rusakBeratRegister = AsetRegister::where('bidang_id', $bidangId)->where('kondisi', 'Rusak Berat')->count();
        $rusakBeratCount = $rusakBeratSmki + $rusakBeratRegister;

        $persenBaik = $totalAset > 0 ? ($baikCount / $totalAset) * 100 : 0;
        $persenRusakRingan = $totalAset > 0 ? ($rusakRinganCount / $totalAset) * 100 : 0;
        $persenRusakBerat = $totalAset > 0 ? ($rusakBeratCount / $totalAset) * 100 : 0;

        $assets = collect();

        // Query SMKI assets if filter matches or is all
        if (!$category || $category === 'Semua Kategori' || $category === 'SMKI') {
            $smkiQuery = AsetSmki::with(['riwayatKondisi' => function ($q) {
                $q->latest();
            }])->where('bidang_id', $bidangId);

            if ($condition && $condition !== 'Semua Kondisi') {
                $smkiQuery->where('keadaan_barang', $condition);
            }

            if ($search) {
                $smkiQuery->where(function ($q) use ($search) {
                    $q->where('merk_model', 'like', '%' . $search . '%')
                      ->orWhere('nomor_kode_barang', 'like', '%' . $search . '%')
                      ->orWhere('ruangan', 'like', '%' . $search . '%');
                });
            }

            $smkiAssets = $smkiQuery->get()->map(function ($asset) {
                $latestHistory = $asset->riwayatKondisi->first();
                return (object)[
                    'id' => $asset->id,
                    'name' => $asset->merk_model,
                    'code' => $asset->nomor_kode_barang,
                    'category' => 'SMKI',
                    'location' => $asset->ruangan ?? '-',
                    'condition' => $asset->keadaan_barang,
                    'last_update' => $latestHistory ? $latestHistory->created_at : $asset->updated_at,
                ];
            });
            $assets = $assets->concat($smkiAssets);
        }

        // Query Register assets if filter matches or is all
        if (!$category || $category === 'Semua Kategori' || $category === 'REGISTER') {
            $registerQuery = AsetRegister::with(['riwayatKondisi' => function ($q) {
                $q->latest();
            }])->where('bidang_id', $bidangId);

            if ($condition && $condition !== 'Semua Kondisi') {
                $registerQuery->where('kondisi', $condition);
            }

            if ($search) {
                $registerQuery->where(function ($q) use ($search) {
                    $q->where('nama_aset', 'like', '%' . $search . '%')
                      ->orWhere('kode_aset', 'like', '%' . $search . '%')
                      ->orWhere('lokasi_aset', 'like', '%' . $search . '%');
                });
            }

            $registerAssets = $registerQuery->get()->map(function ($asset) {
                $latestHistory = $asset->riwayatKondisi->first();
                return (object)[
                    'id' => $asset->id,
                    'name' => $asset->nama_aset,
                    'code' => $asset->kode_aset,
                    'category' => 'REGISTER',
                    'location' => $asset->lokasi_aset ?? '-',
                    'condition' => $asset->kondisi,
                    'last_update' => $latestHistory ? $latestHistory->created_at : $asset->updated_at,
                ];
            });
            $assets = $assets->concat($registerAssets);
        }

        // Sort by last update date descending
        $assets = $assets->sortByDesc('last_update');

        // Paginate the collection manually
        $perPage = 10;
        $page = request()->get('page', 1);
        $paginatedAssets = new LengthAwarePaginator(
            $assets->forPage($page, $perPage)->values(),
            $assets->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('pages.admin-perbidang.KondisiAset.index', [
            'assets' => $paginatedAssets,
            'totalAset' => $totalAset,
            'baikCount' => $baikCount,
            'rusakRinganCount' => $rusakRinganCount,
            'rusakBeratCount' => $rusakBeratCount,
            'persenBaik' => $persenBaik,
            'persenRusakRingan' => $persenRusakRingan,
            'persenRusakBerat' => $persenRusakBerat,
            'search' => $search,
            'kategori' => $category ?? 'Semua Kategori',
            'kondisi' => $condition ?? 'Semua Kondisi'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        // Fetch all assets under user's bidang for dropdown selection
        $smkiAssets = AsetSmki::where('bidang_id', $bidangId)->get(['id', 'merk_model', 'nomor_kode_barang', 'keadaan_barang']);
        $registerAssets = AsetRegister::where('bidang_id', $bidangId)->get(['id', 'nama_aset', 'kode_aset', 'kondisi']);

        // Check for query parameters if updating from a row
        $selectedType = $request->query('type');
        $selectedId = $request->query('id');

        return view('pages.admin-perbidang.KondisiAset.create', compact('smkiAssets', 'registerAssets', 'selectedType', 'selectedId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKondisiAsetRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        $tipeAset = $validated['tipe_aset'];
        $asetId = $validated['aset_id'];
        $keadaanBaru = $validated['keadaan_baru'];
        $catatan = $validated['catatan'] ?? null;

        // Find the asset and verify authorization
        if ($tipeAset === 'SMKI') {
            $asset = AsetSmki::findOrFail($asetId);
            if ($asset->bidang_id !== $bidangId) {
                abort(403, 'Anda tidak memiliki hak akses untuk aset ini.');
            }
            $keadaanLama = $asset->keadaan_barang;
        } else {
            $asset = AsetRegister::findOrFail($asetId);
            if ($asset->bidang_id !== $bidangId) {
                abort(403, 'Anda tidak memiliki hak akses untuk aset ini.');
            }
            $keadaanLama = $asset->kondisi;
        }

        // Handle photo upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto_kondisi', 'public');
        }

        // Map condition to status
        $statusMap = [
            'Baik' => 'Aktif',
            'Rusak Ringan' => 'Maintenance',
            'Rusak Berat' => 'Rusak',
        ];
        $newStatus = $statusMap[$keadaanBaru] ?? 'Aktif';

        DB::transaction(function () use ($tipeAset, $asetId, $keadaanLama, $keadaanBaru, $catatan, $fotoPath, $user, $asset, $newStatus) {
            if ($tipeAset === 'SMKI') {
                // Log history
                RiwayatKondisiSmki::create([
                    'aset_smki_id' => $asetId,
                    'keadaan_lama' => $keadaanLama,
                    'keadaan_baru' => $keadaanBaru,
                    'catatan' => $catatan,
                    'foto_path' => $fotoPath,
                    'diupdate_oleh' => $user->id,
                ]);

                // Update asset current state
                $asset->update([
                    'keadaan_barang' => $keadaanBaru,
                    'status_verifikasi' => $newStatus,
                ]);
            } else {
                // Log history
                RiwayatKondisiRegister::create([
                    'aset_register_id' => $asetId,
                    'keadaan_lama' => $keadaanLama,
                    'keadaan_baru' => $keadaanBaru,
                    'catatan' => $catatan,
                    'foto_path' => $fotoPath,
                    'diupdate_oleh' => $user->id,
                ]);

                // Update asset current state
                $asset->update([
                    'kondisi' => $keadaanBaru,
                    'status' => $newStatus,
                ]);
            }
        });

        return redirect()->route('admin-perbidang.kondisi-aset.index')
            ->with('success', 'Riwayat kondisi aset baru berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('admin-perbidang.kondisi-aset.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;
        $type = $request->query('type', 'REGISTER');

        if ($type === 'SMKI') {
            $asset = AsetSmki::findOrFail($id);
            if ($asset->bidang_id !== $bidangId) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
            }
            $assetData = (object)[
                'id' => $asset->id,
                'name' => $asset->merk_model,
                'code' => $asset->nomor_kode_barang,
                'category' => 'SMKI',
                'condition' => $asset->keadaan_barang,
            ];
        } else {
            $asset = AsetRegister::findOrFail($id);
            if ($asset->bidang_id !== $bidangId) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
            }
            $assetData = (object)[
                'id' => $asset->id,
                'name' => $asset->nama_aset,
                'code' => $asset->kode_aset,
                'category' => 'REGISTER',
                'condition' => $asset->kondisi,
            ];
        }

        return view('pages.admin-perbidang.KondisiAset.edit', compact('assetData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKondisiAsetRequest $request, $id)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        $tipeAset = $validated['tipe_aset'];
        $keadaanBaru = $validated['keadaan_baru'];
        $catatan = $validated['catatan'] ?? null;

        if ($tipeAset === 'SMKI') {
            $asset = AsetSmki::findOrFail($id);
            if ($asset->bidang_id !== $bidangId) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
            }
            $keadaanLama = $asset->keadaan_barang;
        } else {
            $asset = AsetRegister::findOrFail($id);
            if ($asset->bidang_id !== $bidangId) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah data aset ini.');
            }
            $keadaanLama = $asset->kondisi;
        }

        // Handle photo upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto_kondisi', 'public');
        }

        // Map condition to status
        $statusMap = [
            'Baik' => 'Aktif',
            'Rusak Ringan' => 'Maintenance',
            'Rusak Berat' => 'Rusak',
        ];
        $newStatus = $statusMap[$keadaanBaru] ?? 'Aktif';

        DB::transaction(function () use ($tipeAset, $id, $keadaanLama, $keadaanBaru, $catatan, $fotoPath, $user, $asset, $newStatus) {
            if ($tipeAset === 'SMKI') {
                // Log history
                RiwayatKondisiSmki::create([
                    'aset_smki_id' => $id,
                    'keadaan_lama' => $keadaanLama,
                    'keadaan_baru' => $keadaanBaru,
                    'catatan' => $catatan,
                    'foto_path' => $fotoPath,
                    'diupdate_oleh' => $user->id,
                ]);

                // Update asset current state
                $asset->update([
                    'keadaan_barang' => $keadaanBaru,
                    'status_verifikasi' => $newStatus,
                ]);
            } else {
                // Log history
                RiwayatKondisiRegister::create([
                    'aset_register_id' => $id,
                    'keadaan_lama' => $keadaanLama,
                    'keadaan_baru' => $keadaanBaru,
                    'catatan' => $catatan,
                    'foto_path' => $fotoPath,
                    'diupdate_oleh' => $user->id,
                ]);

                // Update asset current state
                $asset->update([
                    'kondisi' => $keadaanBaru,
                    'status' => $newStatus,
                ]);
            }
        });

        return redirect()->route('admin-perbidang.kondisi-aset.index')
            ->with('success', 'Kondisi aset berhasil diperbarui dan dicatat dalam riwayat.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // For simplicity and safety, we do not support hard delete of condition history from this controller.
        return redirect()->route('admin-perbidang.kondisi-aset.index')
            ->with('error', 'Penghapusan riwayat kondisi tidak diizinkan.');
    }
}
