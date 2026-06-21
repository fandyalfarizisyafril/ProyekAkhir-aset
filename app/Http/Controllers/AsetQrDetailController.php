<?php

namespace App\Http\Controllers;

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use Illuminate\View\View;

class AsetQrDetailController extends Controller
{
    /**
     * Tampilkan detail aset dari hasil scan QR Code.
     */
    public function show(string $type, int $id): View
    {
        abort_unless(in_array($type, ['register', 'smki'], true), 404);

        if ($type === 'register') {
            $asset = AsetRegister::with(['bidang', 'inputter', 'verifier'])->findOrFail($id);
            $assetData = (object) [
                'type_label' => 'REGISTER',
                'title' => $asset->nama_aset,
                'code' => $asset->kode_aset,
                'category' => $asset->kode_barang,
                'condition' => $asset->kondisi,
                'location' => $asset->lokasi_aset,
                'responsible_person' => $asset->pengguna,
                'description' => $asset->keterangan,
                'detail_rows' => [
                    'Pemilik Aset' => $asset->pemilik_aset,
                    'Bidang' => $asset->bidang->nama_bidang ?? '-',
                    'Kritikalitas' => $asset->kritikalitas,
                    'Status Inventaris' => $this->displayAssetStatus($asset->status),
                    'Status Verifikasi' => $asset->status_verifikasi,
                ],
            ];
        } else {
            $asset = AsetSmki::with(['bidang', 'inputter', 'verifier'])->findOrFail($id);
            $assetData = (object) [
                'type_label' => 'SMKI',
                'title' => $asset->merk_model,
                'code' => $asset->nomor_kode_barang,
                'category' => $asset->jenis_barang,
                'condition' => $asset->keadaan_barang,
                'location' => $asset->ruangan,
                'responsible_person' => $asset->penanggung_jawab,
                'description' => $asset->keterangan,
                'detail_rows' => [
                    'Bidang' => $asset->bidang->nama_bidang ?? '-',
                    'Nomor Seri' => $asset->no_ser_model ?: '-',
                    'Jumlah' => $asset->jumlah . ' ' . $asset->satuan,
                    'Tahun Pembelian' => $asset->tahun_pembuatan,
                    'Status Verifikasi' => $asset->status_verifikasi,
                ],
            ];
        }

        return view('pages.qr-detail', compact('asset', 'assetData', 'type'));
    }

    private function displayAssetStatus(?string $status): string
    {
        return match ($status) {
            null, 'Aktif' => 'Tersedia',
            default => $status,
        };
    }
}
