<?php

namespace App\Http\Controllers\AdminPerbidang;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPerbidang\StorePeminjamanAsetRequest;
use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\PeminjamanAset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeminjamanAsetController extends Controller
{
    /**
     * Tampilkan daftar pengajuan peminjaman user.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $search = $request->input('search');
        $status = $request->input('status', 'Semua Status');

        $query = PeminjamanAset::with($this->relations())
            ->where('peminjam_id', $user->id)
            ->latest();

        if ($status !== 'Semua Status') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keperluan', 'like', '%' . $search . '%')
                    ->orWhereHas('asetRegister', function ($assetQuery) use ($search) {
                        $assetQuery->where('nama_aset', 'like', '%' . $search . '%')
                            ->orWhere('kode_aset', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('asetSmki', function ($assetQuery) use ($search) {
                        $assetQuery->where('merk_model', 'like', '%' . $search . '%')
                            ->orWhere('nomor_kode_barang', 'like', '%' . $search . '%');
                    });
            });
        }

        return view('pages.admin-perbidang.PeminjamanAset.index', [
            'peminjaman' => $query->paginate(10)->withQueryString(),
            'search' => $search,
            'status' => $status,
            'pendingCount' => PeminjamanAset::where('peminjam_id', $user->id)->where('status', 'Menunggu Verifikasi')->count(),
            'approvedCount' => PeminjamanAset::where('peminjam_id', $user->id)->where('status', 'Disetujui')->count(),
            'rejectedCount' => PeminjamanAset::where('peminjam_id', $user->id)->where('status', 'Ditolak')->count(),
            'returnedCount' => PeminjamanAset::where('peminjam_id', $user->id)->where('status', 'Dikembalikan')->count(),
        ]);
    }

    /**
     * Tampilkan form pengajuan peminjaman aset.
     */
    public function create(): View
    {
        return view('pages.admin-perbidang.PeminjamanAset.create', [
            'assets' => $this->availableAssets(),
        ]);
    }

    /**
     * Simpan pengajuan peminjaman aset.
     */
    public function store(StorePeminjamanAsetRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $asset = $this->resolveAvailableAsset($validated['jenis_aset'], (int) $validated['aset_id']);

        PeminjamanAset::create([
            'jenis_aset' => $validated['jenis_aset'],
            'aset_register_id' => $validated['jenis_aset'] === 'register' ? $asset->id : null,
            'aset_smki_id' => $validated['jenis_aset'] === 'smki' ? $asset->id : null,
            'peminjam_id' => auth()->id(),
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'tanggal_rencana_kembali' => $validated['tanggal_rencana_kembali'],
            'keperluan' => $validated['keperluan'],
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'Menunggu Verifikasi',
        ]);

        return redirect()
            ->route('admin-perbidang.peminjaman-aset.index')
            ->with('success', 'Pengajuan peminjaman aset berhasil dikirim ke Super Admin.');
    }

    /**
     * Tampilkan detail pengajuan peminjaman aset.
     */
    public function show(PeminjamanAset $peminjaman_aset): View
    {
        abort_unless($peminjaman_aset->peminjam_id === auth()->id(), 403);

        $peminjaman_aset->load($this->relations());

        return view('pages.admin-perbidang.PeminjamanAset.show', [
            'peminjaman' => $peminjaman_aset,
        ]);
    }

    /**
     * Catat pengembalian peminjaman aktif dan ubah aset menjadi tersedia.
     */
    public function returnAsset(Request $request, PeminjamanAset $peminjaman_aset): RedirectResponse
    {
        abort_unless($peminjaman_aset->peminjam_id === auth()->id(), 403);

        if ($peminjaman_aset->status !== 'Disetujui' || $peminjaman_aset->tanggal_kembali !== null) {
            return redirect()
                ->route('admin-perbidang.peminjaman-aset.show', $peminjaman_aset->id)
                ->with('error', 'Peminjaman ini tidak dapat dicatat sebagai pengembalian.');
        }

        $validated = $request->validate([
            'tanggal_kembali' => ['required', 'date', 'after_or_equal:' . $peminjaman_aset->tanggal_pinjam],
            'catatan_pengembalian' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($peminjaman_aset, $validated) {
            $asset = $this->resolveAsset($peminjaman_aset);
            $asset->update(['status' => 'Tersedia']);

            $peminjaman_aset->update([
                'tanggal_kembali' => $validated['tanggal_kembali'],
                'status' => 'Dikembalikan',
                'catatan' => $this->mergeReturnNote(
                    $peminjaman_aset->catatan,
                    $validated['tanggal_kembali'],
                    $validated['catatan_pengembalian'] ?? null
                ),
            ]);
        });

        return redirect()
            ->route('admin-perbidang.peminjaman-aset.show', $peminjaman_aset->id)
            ->with('success', 'Pengembalian aset berhasil dicatat dan status aset menjadi Tersedia.');
    }

    private function availableAssets(): Collection
    {
        $registerAssets = AsetRegister::with('bidang')
            ->where('status_verifikasi', 'Terverifikasi')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'Dipinjam');
            })
            ->get()
            ->reject(fn (AsetRegister $asset) => $this->hasActiveLoan('register', $asset->id))
            ->map(fn (AsetRegister $asset) => (object) [
                'id' => $asset->id,
                'type' => 'register',
                'label' => 'REGISTER - ' . $asset->kode_aset . ' - ' . $asset->nama_aset . ' (' . ($asset->bidang->nama_bidang ?? '-') . ')',
            ]);

        $smkiAssets = AsetSmki::with('bidang')
            ->where('status_verifikasi', 'Terverifikasi')
            ->get()
            ->reject(fn (AsetSmki $asset) => $this->hasActiveLoan('smki', $asset->id))
            ->map(fn (AsetSmki $asset) => (object) [
                'id' => $asset->id,
                'type' => 'smki',
                'label' => 'SMKI - ' . $asset->nomor_kode_barang . ' - ' . $asset->merk_model . ' (' . ($asset->bidang->nama_bidang ?? '-') . ')',
            ]);

        return $registerAssets->concat($smkiAssets)->values();
    }

    private function resolveAvailableAsset(string $type, int $id): AsetRegister|AsetSmki
    {
        abort_unless(in_array($type, ['register', 'smki'], true), 404);
        abort_if($this->hasActiveLoan($type, $id), 422, 'Aset sedang memiliki pengajuan/peminjaman aktif.');

        if ($type === 'register') {
            return AsetRegister::where('status_verifikasi', 'Terverifikasi')
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'Dipinjam');
                })
                ->findOrFail($id);
        }

        return AsetSmki::where('status_verifikasi', 'Terverifikasi')->findOrFail($id);
    }

    private function resolveAsset(PeminjamanAset $peminjaman): AsetRegister|AsetSmki
    {
        if ($peminjaman->jenis_aset === 'register') {
            return AsetRegister::findOrFail($peminjaman->aset_register_id);
        }

        return AsetSmki::findOrFail($peminjaman->aset_smki_id);
    }

    private function mergeReturnNote(?string $existingNote, string $returnDate, ?string $returnNote): string
    {
        $history = 'Pengembalian dicatat pada ' . $returnDate;

        if ($returnNote) {
            $history .= ': ' . $returnNote;
        }

        return trim($existingNote ? $existingNote . "\n\n" . $history : $history);
    }

    private function hasActiveLoan(string $type, int $assetId): bool
    {
        return PeminjamanAset::where('jenis_aset', $type)
            ->when(
                $type === 'register',
                fn ($query) => $query->where('aset_register_id', $assetId),
                fn ($query) => $query->where('aset_smki_id', $assetId)
            )
            ->whereIn('status', ['Menunggu Verifikasi', 'Disetujui'])
            ->whereNull('tanggal_kembali')
            ->exists();
    }

    private function relations(): array
    {
        return [
            'asetRegister.bidang',
            'asetSmki.bidang',
            'peminjam.bidang',
            'penyetuju',
        ];
    }
}
