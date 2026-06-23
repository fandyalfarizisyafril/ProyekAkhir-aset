<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StoreAsetSmkiRequest;
use App\Http\Requests\AdminPerbidang\UpdateAsetSmkiRequest;
use App\Models\AsetSmki;
use App\Models\KategoriAset;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataAsetSMKIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $bidangId = $user->bidang_id;

        // Fetch categories dynamically for filters
        $kategoris = $this->smkiCategories()
            ->merge(AsetSmki::notDeleted()
            ->where('bidang_id', $bidangId)
            ->distinct()
            ->pluck('jenis_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Filters
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $status = $request->input('status');

        $query = $this->filteredSmkiQuery($request, $bidangId);

        // Paginate (10 per page)
        $assets = $query->paginate(10)->withQueryString();

        // Calculate Statistics (scoped to the admin's bidang)
        $totalAset = AsetSmki::notDeleted()->where('bidang_id', $bidangId)->count();
        $aktifCount = AsetSmki::notDeleted()->where('bidang_id', $bidangId)->where('keadaan_barang', 'Baik')->count();
        $maintenanceCount = AsetSmki::notDeleted()->where('bidang_id', $bidangId)->where('keadaan_barang', 'Rusak Ringan')->count();

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

    public function export(Request $request): StreamedResponse
    {
        $bidangId = (int) $request->user()->bidang_id;
        $assets = $this->filteredSmkiQuery($request, $bidangId)
            ->orderBy('nomor_kode_barang')
            ->get();

        $filename = 'data-aset-smki-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($assets): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, '<html><head><meta charset="UTF-8"></head><body><table border="1">');

            $this->writeExcelRow($handle, [
                'Nomor Kode Barang',
                'Jenis Barang',
                'Merk/Model',
                'No Serial/Model',
                'Ukuran',
                'Bahan',
                'Tahun Pembuatan',
                'Jumlah',
                'Satuan',
                'Keadaan Barang',
                'Bidang',
                'Ruangan',
                'Penanggung Jawab',
                'Status Aset',
                'Status Verifikasi',
                'Keterangan',
                'Diinput Oleh',
                'Tanggal Input',
            ], 'th');

            foreach ($assets as $asset) {
                $this->writeExcelRow($handle, [
                    $asset->nomor_kode_barang,
                    $asset->jenis_barang,
                    $asset->merk_model,
                    $asset->no_ser_model,
                    $asset->ukuran,
                    $asset->bahan,
                    $asset->tahun_pembuatan,
                    $asset->jumlah,
                    $asset->satuan,
                    $asset->keadaan_barang,
                    $asset->bidang->nama_bidang ?? '-',
                    $asset->ruangan,
                    $asset->penanggung_jawab,
                    $this->displayAssetStatus($asset->status),
                    $asset->status_verifikasi,
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
        return view('pages.admin-perbidang.DataAserSmki.create', [
            'categories' => $this->smkiCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAsetSmkiRequest $request)
    {
        $validated = $request->validated();
        $validated['jenis_barang'] = trim($validated['jenis_barang']);

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
        if ($data_aset_smki->bidang_id !== auth()->user()->bidang_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data aset ini.');
        }

        $data_aset_smki->load([
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
            'penghapusan.remover',
        ]);

        return view('pages.admin-perbidang.DataAserSmki.show', [
            'asset' => $data_aset_smki,
        ]);
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
            'asset' => $data_aset_smki,
            'categories' => $this->smkiCategories(),
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
        $validated['jenis_barang'] = trim($validated['jenis_barang']);
        
        $data_aset_smki->update($validated);

        return redirect()->route('admin-perbidang.data-aset-smki.index')
            ->with('success', 'Data Aset SMKI berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AsetSmki $data_aset_smki)
    {
        abort(403, 'Penghapusan aset hanya dapat dilakukan oleh Super Admin melalui menu Penghapusan Aset.');
    }

    private function smkiCategories()
    {
        return KategoriAset::where('tipe', 'SMKI')
            ->pluck('nama_kategori')
            ->merge(AsetSmki::notDeleted()->whereNotNull('jenis_barang')->distinct()->pluck('jenis_barang'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function filteredSmkiQuery(Request $request, int $bidangId)
    {
        $query = AsetSmki::notDeleted()
            ->with(['bidang', 'inputter'])
            ->where('bidang_id', $bidangId);

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
