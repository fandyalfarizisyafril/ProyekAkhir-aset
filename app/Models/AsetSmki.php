<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsetSmki extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'aset_smki';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nomor_kode_barang',
        'jenis_barang',
        'merk_model',
        'no_ser_model',
        'ukuran',
        'bahan',
        'tahun_pembuatan',
        'jumlah',
        'satuan',
        'keadaan_barang',
        'keterangan',
        'bidang_id',
        'ruangan',
        'penanggung_jawab',
        'qr_code_path',
        'status_verifikasi',
        'dinput_oleh',
        'diverifikasi_oleh',
    ];

    /**
     * Dapatkan bidang dari aset SMKI ini.
     */
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    /**
     * Dapatkan user yang menginput aset ini.
     */
    public function inputter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinput_oleh');
    }

    /**
     * Dapatkan user yang memverifikasi aset ini.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Dapatkan semua mutasi terkait aset ini.
     */
    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'aset_smki_id');
    }

    /**
     * Dapatkan semua peminjaman terkait aset ini.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanAset::class, 'aset_smki_id');
    }

    /**
     * Dapatkan semua riwayat kondisi terkait aset ini.
     */
    public function riwayatKondisi(): HasMany
    {
        return $this->hasMany(RiwayatKondisiSmki::class, 'aset_smki_id');
    }
}
