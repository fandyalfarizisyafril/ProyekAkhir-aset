<?php

namespace App\Http\Controllers;

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\Laporan;
use App\Models\PenghapusanAset;
use App\Support\SystemNotifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanAsetController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $assets = $this->reportAssets($request, $filters);

        return view('pages.laporan-aset.index', [
            'assets' => $this->paginateCollection($assets, $request),
            'filters' => $filters,
            'bidangs' => $this->bidangOptions($request),
            'categoryOptions' => $this->categoryOptions($request, $filters),
            'conditionOptions' => $this->conditionOptions($request, $filters),
            'summary' => $this->summary($assets, $request, $filters),
            'isAdminPerbidang' => $request->user()->role === 'Admin Perbidang',
            'isKepalaDinas' => $request->user()->role === 'Kepala Dinas',
            'uploadedReports' => $this->uploadedReports($request),
        ]);
    }

    public function uploadIndex(Request $request): View
    {
        abort_unless(in_array($request->user()->role, ['Super Admin', 'Admin Perbidang'], true), 403);

        return view('pages.upload-laporan.index', [
            'uploadedReports' => $this->uploadedReports($request),
            'uploadJenisAsetOptions' => $this->uploadJenisAsetOptions(),
            'uploadJenisLaporanOptions' => $this->uploadJenisLaporanOptions(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if($request->user()->role === 'Kepala Dinas', 403);

        $filters = $this->filters($request);
        $assets = $this->reportAssets($request, $filters);
        $filename = 'laporan-aset-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($assets, $request, $filters): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, '<html><head><meta charset="UTF-8"></head><body>');
            fwrite($handle, '<h3>Laporan Aset Diskominfotik Provinsi Riau</h3>');
            fwrite($handle, '<table border="1">');

            $this->writeExcelRow($handle, ['Dibuat Oleh', $request->user()->nama ?? $request->user()->name]);
            $this->writeExcelRow($handle, ['Periode', $this->periodLabel($filters)]);
            $this->writeExcelRow($handle, ['Jenis', $filters['jenis']]);
            $this->writeExcelRow($handle, ['Bidang', $this->selectedBidangLabel($request, $filters)]);
            $this->writeExcelRow($handle, ['Kategori', $filters['kategori']]);
            $this->writeExcelRow($handle, ['Kondisi', $filters['kondisi']]);
            fwrite($handle, '</table><br><table border="1">');

            $this->writeExcelRow($handle, [
                'Jenis',
                'Kode Aset',
                'Nama Aset',
                'Kategori',
                'Bidang',
                'Kondisi',
                'Status Aset',
                'Status Verifikasi',
                'Nilai Register',
                'Lokasi/Ruangan',
                'Penanggung Jawab/Pengguna',
                'Tanggal Input',
            ], 'th');

            foreach ($assets as $asset) {
                $this->writeExcelRow($handle, [
                    $asset->type_label,
                    $asset->code,
                    $asset->name,
                    $asset->category,
                    $asset->bidang?->nama_bidang ?? '-',
                    $asset->condition,
                    $asset->status,
                    $asset->verification_status,
                    $asset->value,
                    $asset->location,
                    $asset->person_in_charge,
                    $asset->created_at?->format('d/m/Y H:i'),
                ]);
            }

            fwrite($handle, '</table></body></html>');
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function print(Request $request): View
    {
        abort_if($request->user()->role === 'Kepala Dinas', 403);

        $filters = $this->filters($request);
        $assets = $this->reportAssets($request, $filters);

        return view('pages.laporan-aset.print', [
            'assets' => $assets,
            'filters' => $filters,
            'summary' => $this->summary($assets, $request, $filters),
            'periodLabel' => $this->periodLabel($filters),
            'bidangLabel' => $this->selectedBidangLabel($request, $filters),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['Super Admin', 'Admin Perbidang'], true), 403);

        $validated = $request->validate([
            'jenis_aset' => ['required', 'string', Rule::in(array_keys($this->uploadJenisAsetOptions()))],
            'jenis_laporan' => ['required', 'string', Rule::in(array_keys($this->uploadJenisLaporanOptions()))],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,xls,xlsx,doc,docx'],
        ], [], [
            'jenis_aset' => 'Jenis Aset',
            'jenis_laporan' => 'Jenis Laporan',
            'keterangan' => 'Keterangan',
            'file' => 'File Laporan',
        ]);

        $file = $request->file('file');
        $path = $file->store('laporan-aset');

        $laporan = Laporan::create([
            'jenis_aset' => $validated['jenis_aset'],
            'jenis_laporan' => $validated['jenis_laporan'],
            'dibuat_oleh' => $request->user()->id,
            'keterangan' => $validated['keterangan'] ?? null,
            'file_path' => $path,
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $creatorName = $request->user()->nama ?? $request->user()->name ?? 'Pengguna';

        SystemNotifier::notifyRoles(
            'Kepala Dinas',
            'Laporan aset baru diupload',
            "{$validated['jenis_laporan']} dari {$creatorName} sudah tersedia untuk dilihat.",
            route('laporan-aset.view', $laporan->id),
            'info',
            'laporan'
        );

        return redirect()
            ->route('upload-laporan.index')
            ->with('success', 'Laporan berhasil diupload dan tersedia untuk Kepala Dinas.');
    }

    public function view(Request $request, Laporan $laporan): BinaryFileResponse
    {
        $this->authorizeReportAccess($request, $laporan);
        abort_unless(Storage::exists($laporan->file_path), 404);

        return response()->file(Storage::path($laporan->file_path), [
            'Content-Type' => $laporan->file_mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($this->reportFilename($laporan)) . '"',
        ]);
    }

    public function download(Request $request, Laporan $laporan): BinaryFileResponse
    {
        $this->authorizeReportAccess($request, $laporan);
        abort_unless(Storage::exists($laporan->file_path), 404);

        return response()->download(Storage::path($laporan->file_path), $this->reportFilename($laporan), [
            'Content-Type' => $laporan->file_mime_type ?: 'application/octet-stream',
        ]);
    }

    private function filters(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'bidang_id' => $request->user()->role === 'Admin Perbidang'
                ? (string) $request->user()->bidang_id
                : $request->input('bidang_id', 'Semua Bidang'),
            'kategori' => $request->input('kategori', 'Semua Kategori'),
            'kondisi' => $request->input('kondisi', 'Semua Kondisi'),
        ];
    }

    private function reportAssets(Request $request, array $filters): Collection
    {
        $assets = collect();

        if (in_array($filters['jenis'], ['Semua Jenis', 'register'], true)) {
            $assets = $assets->merge($this->registerAssets($request, $filters));
        }

        if (in_array($filters['jenis'], ['Semua Jenis', 'smki'], true)) {
            $assets = $assets->merge($this->smkiAssets($request, $filters));
        }

        return $assets
            ->sortByDesc(fn (object $asset) => $asset->created_at?->timestamp ?? 0)
            ->values();
    }

    private function registerAssets(Request $request, array $filters): Collection
    {
        return $this->applyReportFilters($this->verifiedRegisterQuery($request), 'register', $filters)
            ->with('bidang')
            ->latest()
            ->get()
            ->map(fn (AsetRegister $asset) => (object) [
                'type' => 'register',
                'type_label' => 'Register',
                'code' => $asset->kode_aset,
                'name' => $asset->nama_aset,
                'category' => $asset->kode_barang,
                'bidang' => $asset->bidang,
                'condition' => $asset->kondisi ?? $asset->status_barang,
                'status' => $this->displayAssetStatus($asset->status),
                'verification_status' => $asset->status_verifikasi,
                'value' => (float) $asset->nilai,
                'location' => $asset->lokasi_aset,
                'person_in_charge' => $asset->pengguna,
                'created_at' => $asset->created_at,
            ]);
    }

    private function smkiAssets(Request $request, array $filters): Collection
    {
        return $this->applyReportFilters($this->verifiedSmkiQuery($request), 'smki', $filters)
            ->with('bidang')
            ->latest()
            ->get()
            ->map(fn (AsetSmki $asset) => (object) [
                'type' => 'smki',
                'type_label' => 'SMKI',
                'code' => $asset->nomor_kode_barang,
                'name' => $asset->merk_model,
                'category' => $asset->jenis_barang,
                'bidang' => $asset->bidang,
                'condition' => $asset->keadaan_barang,
                'status' => $this->displayAssetStatus($asset->status),
                'verification_status' => $asset->status_verifikasi,
                'value' => null,
                'location' => $asset->ruangan,
                'person_in_charge' => $asset->penanggung_jawab,
                'created_at' => $asset->created_at,
            ]);
    }

    private function verifiedRegisterQuery(Request $request): Builder
    {
        $query = AsetRegister::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi');

        return $this->applyRoleScope($query, $request);
    }

    private function verifiedSmkiQuery(Request $request): Builder
    {
        $query = AsetSmki::notDeleted()
            ->where('status_verifikasi', 'Terverifikasi');

        return $this->applyRoleScope($query, $request);
    }

    private function applyRoleScope(Builder $query, Request $request): Builder
    {
        if ($request->user()->role === 'Admin Perbidang') {
            $query->where('bidang_id', $request->user()->bidang_id);
        }

        return $query;
    }

    private function applyReportFilters(Builder $query, string $type, array $filters): Builder
    {
        if ($filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if ($filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['kategori'] !== 'Semua Kategori') {
            $query->where($type === 'register' ? 'kode_barang' : 'jenis_barang', $filters['kategori']);
        }

        if ($filters['kondisi'] !== 'Semua Kondisi') {
            $query->where($type === 'register' ? 'kondisi' : 'keadaan_barang', $filters['kondisi']);
        }

        return $query;
    }

    private function categoryOptions(Request $request, array $filters): Collection
    {
        $register = $this->baseOptionQuery($this->verifiedRegisterQuery($request), $filters)
            ->whereNotNull('kode_barang')
            ->distinct()
            ->pluck('kode_barang');
        $smki = $this->baseOptionQuery($this->verifiedSmkiQuery($request), $filters)
            ->whereNotNull('jenis_barang')
            ->distinct()
            ->pluck('jenis_barang');

        return $register->merge($smki)->filter()->unique()->sort()->values();
    }

    private function conditionOptions(Request $request, array $filters): Collection
    {
        $register = $this->baseOptionQuery($this->verifiedRegisterQuery($request), $filters)
            ->whereNotNull('kondisi')
            ->distinct()
            ->pluck('kondisi');
        $smki = $this->baseOptionQuery($this->verifiedSmkiQuery($request), $filters)
            ->whereNotNull('keadaan_barang')
            ->distinct()
            ->pluck('keadaan_barang');

        return $register->merge($smki)->filter()->unique()->sort()->values();
    }

    private function baseOptionQuery(Builder $query, array $filters): Builder
    {
        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        return $query;
    }

    private function bidangOptions(Request $request): Collection
    {
        if ($request->user()->role === 'Admin Perbidang') {
            return Bidang::whereKey($request->user()->bidang_id)->get();
        }

        return Bidang::orderBy('nama_bidang')->get();
    }

    private function summary(Collection $assets, Request $request, array $filters): array
    {
        return [
            'total' => $assets->count(),
            'register' => $assets->where('type', 'register')->count(),
            'smki' => $assets->where('type', 'smki')->count(),
            'good' => $assets->where('condition', 'Baik')->count(),
            'lightDamage' => $assets->where('condition', 'Rusak Ringan')->count(),
            'heavyDamage' => $assets->where('condition', 'Rusak Berat')->count(),
            'registerValue' => (float) $assets->where('type', 'register')->sum('value'),
            'deleted' => $this->deletedCount($request, $filters),
        ];
    }

    private function deletedCount(Request $request, array $filters): int
    {
        $query = PenghapusanAset::query();

        if ($request->user()->role === 'Admin Perbidang') {
            $query->where('bidang_id', $request->user()->bidang_id);
        } elseif ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['start_date']) {
            $query->whereDate('tanggal_penghapusan', '>=', $filters['start_date']);
        }

        if ($filters['end_date']) {
            $query->whereDate('tanggal_penghapusan', '<=', $filters['end_date']);
        }

        return $query->count();
    }

    private function paginateCollection(Collection $assets, Request $request): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();
        $perPage = 10;

        return new Paginator(
            $assets->forPage($page, $perPage)->values(),
            $assets->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function uploadedReports(Request $request): LengthAwarePaginator
    {
        $query = Laporan::with(['creator.bidang'])
            ->latest();

        if ($request->user()->role === 'Admin Perbidang') {
            $query->where('dibuat_oleh', $request->user()->id);
        }

        return $query->paginate(10)->withQueryString();
    }

    private function uploadJenisAsetOptions(): array
    {
        return [
            'Semua Aset' => 'Semua Aset',
            'Register' => 'Register',
            'SMKI' => 'SMKI',
        ];
    }

    private function uploadJenisLaporanOptions(): array
    {
        return [
            'Rekap Aset' => 'Rekap Aset',
            'Laporan Bulanan' => 'Laporan Bulanan',
            'Laporan Tahunan' => 'Laporan Tahunan',
            'Laporan Penghapusan' => 'Laporan Penghapusan',
            'Laporan Penyusutan' => 'Laporan Penyusutan',
        ];
    }

    private function authorizeReportAccess(Request $request, Laporan $laporan): void
    {
        if ($request->user()->role === 'Admin Perbidang') {
            abort_unless($laporan->dibuat_oleh === $request->user()->id, 403);
        }
    }

    private function reportFilename(Laporan $laporan): string
    {
        return $laporan->file_original_name ?: basename($laporan->file_path);
    }

    private function selectedBidangLabel(Request $request, array $filters): string
    {
        if ($request->user()->role === 'Admin Perbidang') {
            return $request->user()->bidang->nama_bidang ?? '-';
        }

        if ($filters['bidang_id'] === 'Semua Bidang') {
            return 'Semua Bidang';
        }

        return Bidang::whereKey($filters['bidang_id'])->value('nama_bidang') ?? '-';
    }

    private function periodLabel(array $filters): string
    {
        $start = $filters['start_date'] ? Carbon::parse($filters['start_date'])->format('d M Y') : 'Awal data';
        $end = $filters['end_date'] ? Carbon::parse($filters['end_date'])->format('d M Y') : 'Sekarang';

        return $start . ' - ' . $end;
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
