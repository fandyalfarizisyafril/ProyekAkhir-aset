<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsetRegister extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'aset_register';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kode_barang',
        'kode_urut_barang',
        'bidang_id',
        'status_barang',
        'pemilik_aset',
        'pengguna',
        'lokasi_aset',
        'metode_pemusnahan',
        'kerahasiaan',
        'kritikalitas',
        'nilai',
        'keterangan',
        'kondisi',
        'status',
        'qr_code_path',
        'status_verifikasi',
        'dinput_oleh',
        'diverifikasi_oleh',
    ];

    /**
     * Batasi query ke aset yang masih menjadi inventaris aktif.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('status')
                ->orWhere('status', '!=', 'Dihapus');
        });
    }

    /**
     * Dapatkan bidang dari aset register ini.
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
        return $this->hasMany(MutasiAset::class, 'aset_register_id');
    }

    /**
     * Dapatkan semua peminjaman terkait aset ini.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanAset::class, 'aset_register_id');
    }

    /**
     * Dapatkan semua penyusutan terkait aset ini.
     */
    public function penyusutan(): HasMany
    {
        return $this->hasMany(PenyusutanAset::class, 'aset_register_id');
    }

    /**
     * Dapatkan riwayat penghapusan terkait aset ini.
     */
    public function penghapusan(): HasMany
    {
        return $this->hasMany(PenghapusanAset::class, 'aset_register_id');
    }

    /**
     * Dapatkan semua riwayat kondisi terkait aset ini.
     */
    public function riwayatKondisi(): HasMany
    {
        return $this->hasMany(RiwayatKondisiRegister::class, 'aset_register_id');
    }
}
