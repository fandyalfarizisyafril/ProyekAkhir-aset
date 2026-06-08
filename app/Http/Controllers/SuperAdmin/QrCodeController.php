<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QrCodeController extends Controller
{
    /**
     * Tampilkan aset terverifikasi yang bisa dibuatkan QR Code.
     */
    public function index(Request $request): View
    {
        $filters = [
            'jenis' => $request->input('jenis', 'Semua Jenis'),
            'bidang_id' => $request->input('bidang_id', 'Semua Bidang'),
            'status_qr' => $request->input('status_qr', 'Semua QR'),
            'search' => $request->input('search'),
        ];

        $assets = $this->filteredAssets($filters)
            ->sortByDesc('created_at')
            ->values();

        $perPage = 10;
        $page = (int) $request->input('page', 1);
        $paginatedAssets = new LengthAwarePaginator(
            $assets->forPage($page, $perPage)->values(),
            $assets->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.super-admin.QrCode.index', [
            'assets' => $paginatedAssets,
            'bidangs' => Bidang::orderBy('nama_bidang')->get(),
            'filters' => $filters,
            'eligibleCount' => AsetRegister::where('status_verifikasi', 'Terverifikasi')->count()
                + AsetSmki::where('status_verifikasi', 'Terverifikasi')->count(),
            'generatedCount' => AsetRegister::where('status_verifikasi', 'Terverifikasi')->whereNotNull('qr_code_path')->count()
                + AsetSmki::where('status_verifikasi', 'Terverifikasi')->whereNotNull('qr_code_path')->count(),
            'missingCount' => AsetRegister::where('status_verifikasi', 'Terverifikasi')->whereNull('qr_code_path')->count()
                + AsetSmki::where('status_verifikasi', 'Terverifikasi')->whereNull('qr_code_path')->count(),
        ]);
    }

    /**
     * Generate QR Code untuk aset terverifikasi.
     */
    public function generate(string $type, int $id): RedirectResponse
    {
        [$asset] = $this->resolveVerifiedAsset($type, $id);

        $this->generateQrCode($type, $asset);

        return redirect()
            ->route('super-admin.qr-code.index')
            ->with('success', 'QR Code aset berhasil dibuat.');
    }

    /**
     * Tampilkan label QR yang siap dicetak browser.
     */
    public function label(string $type, int $id): View
    {
        [$asset, $assetData] = $this->resolveVerifiedAsset($type, $id);
        $qrPath = $this->ensureQrCode($type, $asset);

        return view('pages.super-admin.QrCode.label', [
            'asset' => $asset,
            'assetData' => $assetData,
            'type' => $type,
            'qrPath' => $qrPath,
            'qrUrl' => Storage::disk('public')->url($qrPath),
            'scanUrl' => route('qr.asset.show', [$type, $asset->id]),
        ]);
    }

    /**
     * Unduh file QR Code SVG.
     */
    public function download(string $type, int $id): BinaryFileResponse
    {
        [$asset, $assetData] = $this->resolveVerifiedAsset($type, $id);
        $qrPath = $this->ensureQrCode($type, $asset);
        $filename = 'qr-' . strtolower($assetData->type_label) . '-' . $assetData->code . '.svg';

        return response()->download(Storage::disk('public')->path($qrPath), $filename);
    }

    private function filteredAssets(array $filters): Collection
    {
        $assets = collect();

        if ($filters['jenis'] === 'Semua Jenis' || $filters['jenis'] === 'register') {
            $assets = $assets->concat($this->registerAssets($filters));
        }

        if ($filters['jenis'] === 'Semua Jenis' || $filters['jenis'] === 'smki') {
            $assets = $assets->concat($this->smkiAssets($filters));
        }

        return $assets;
    }

    private function registerAssets(array $filters): Collection
    {
        $query = AsetRegister::with(['bidang', 'inputter'])
            ->where('status_verifikasi', 'Terverifikasi');

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['status_qr'] === 'Sudah QR') {
            $query->whereNotNull('qr_code_path');
        } elseif ($filters['status_qr'] === 'Belum QR') {
            $query->whereNull('qr_code_path');
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_aset', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get()->map(fn (AsetRegister $asset) => $this->toAssetRow('register', $asset));
    }

    private function smkiAssets(array $filters): Collection
    {
        $query = AsetSmki::with(['bidang', 'inputter'])
            ->where('status_verifikasi', 'Terverifikasi');

        if ($filters['bidang_id'] !== 'Semua Bidang') {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if ($filters['status_qr'] === 'Sudah QR') {
            $query->whereNotNull('qr_code_path');
        } elseif ($filters['status_qr'] === 'Belum QR') {
            $query->whereNull('qr_code_path');
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('merk_model', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('nomor_kode_barang', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('jenis_barang', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get()->map(fn (AsetSmki $asset) => $this->toAssetRow('smki', $asset));
    }

    private function toAssetRow(string $type, AsetRegister|AsetSmki $asset): object
    {
        $assetData = $type === 'register'
            ? $this->registerData($asset)
            : $this->smkiData($asset);

        return (object) [
            'id' => $asset->id,
            'type' => $type,
            'type_label' => $assetData->type_label,
            'name' => $assetData->title,
            'code' => $assetData->code,
            'category' => $assetData->category,
            'condition' => $assetData->condition,
            'bidang' => $asset->bidang,
            'inputter' => $asset->inputter,
            'qr_code_path' => $asset->qr_code_path,
            'created_at' => $asset->created_at,
        ];
    }

    private function ensureQrCode(string $type, AsetRegister|AsetSmki $asset): string
    {
        if ($asset->qr_code_path && Storage::disk('public')->exists($asset->qr_code_path)) {
            return $asset->qr_code_path;
        }

        return $this->generateQrCode($type, $asset);
    }

    private function generateQrCode(string $type, AsetRegister|AsetSmki $asset): string
    {
        abort_unless($asset->status_verifikasi === 'Terverifikasi', 403, 'QR Code hanya dapat dibuat untuk aset terverifikasi.');

        $writer = new Writer(new ImageRenderer(
            new RendererStyle(360),
            new SvgImageBackEnd()
        ));

        $path = 'qrcodes/' . $type . '-' . $asset->id . '.svg';
        $svg = $writer->writeString(route('qr.asset.show', [$type, $asset->id]));

        Storage::disk('public')->put($path, $svg);
        $asset->update(['qr_code_path' => $path]);

        return $path;
    }

    private function resolveVerifiedAsset(string $type, int $id): array
    {
        abort_unless(in_array($type, ['register', 'smki'], true), 404);

        if ($type === 'register') {
            $asset = AsetRegister::with(['bidang', 'inputter'])->findOrFail($id);
            abort_unless($asset->status_verifikasi === 'Terverifikasi', 403, 'Aset belum terverifikasi.');

            return [$asset, $this->registerData($asset)];
        }

        $asset = AsetSmki::with(['bidang', 'inputter'])->findOrFail($id);
        abort_unless($asset->status_verifikasi === 'Terverifikasi', 403, 'Aset belum terverifikasi.');

        return [$asset, $this->smkiData($asset)];
    }

    private function registerData(AsetRegister $asset): object
    {
        return (object) [
            'type_label' => 'REGISTER',
            'title' => $asset->nama_aset,
            'code' => $asset->kode_aset,
            'category' => $asset->kode_barang,
            'condition' => $asset->kondisi,
            'location' => $asset->lokasi_aset,
            'responsible_person' => $asset->pengguna,
        ];
    }

    private function smkiData(AsetSmki $asset): object
    {
        return (object) [
            'type_label' => 'SMKI',
            'title' => $asset->merk_model,
            'code' => $asset->nomor_kode_barang,
            'category' => $asset->jenis_barang,
            'condition' => $asset->keadaan_barang,
            'location' => $asset->ruangan,
            'responsible_person' => $asset->penanggung_jawab,
        ];
    }
}
