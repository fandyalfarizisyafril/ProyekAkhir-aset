<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'bidang';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_bidang',
        'nama_bidang',
        'nama_ruangan',
        'deskripsi',
    ];

    /**
     * Dapatkan semua user yang berasosiasi dengan bidang ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    /**
     * Dapatkan semua aset register di bidang ini.
     */
    public function asetRegisters(): HasMany
    {
        return $this->hasMany(AsetRegister::class, 'bidang_id');
    }

    /**
     * Dapatkan semua aset SMKI di bidang ini.
     */
    public function asetSmkis(): HasMany
    {
        return $this->hasMany(AsetSmki::class, 'bidang_id');
    }

    /**
     * Dapatkan semua mutasi yang berasal dari bidang ini.
     */
    public function mutasiAsal(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'bidang_asal_id');
    }

    /**
     * Dapatkan semua mutasi yang ditujukan ke bidang ini.
     */
    public function mutasiTujuan(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'bidang_tujuan_id');
    }
}
