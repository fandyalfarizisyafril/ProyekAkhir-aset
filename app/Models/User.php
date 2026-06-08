<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'nama',
        'email',
        'password',
        'no_hp',
        'role',
        'bidang_id',
        'status',
    ];

    /**
     * Atribut yang harus disembunyikan untuk serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Dapatkan atribut yang harus di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor untuk properti 'name' demi kompatibilitas dengan Laravel Breeze default.
     */
    public function getNameAttribute(): string
    {
        return $this->nama;
    }

    /**
     * Dapatkan bidang dari user ini.
     */
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    /**
     * Dapatkan aset register yang diinput oleh user ini.
     */
    public function inputAsetRegisters(): HasMany
    {
        return $this->hasMany(AsetRegister::class, 'dinput_oleh');
    }

    /**
     * Dapatkan aset register yang diverifikasi oleh user ini.
     */
    public function verifikasiAsetRegisters(): HasMany
    {
        return $this->hasMany(AsetRegister::class, 'diverifikasi_oleh');
    }

    /**
     * Dapatkan aset SMKI yang diinput oleh user ini.
     */
    public function inputAsetSmkis(): HasMany
    {
        return $this->hasMany(AsetSmki::class, 'dinput_oleh');
    }

    /**
     * Dapatkan mutasi aset yang diajukan oleh user ini.
     */
    public function pengajuanMutasi(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'diajukan_oleh');
    }

    /**
     * Dapatkan mutasi aset yang disetujui oleh user ini.
     */
    public function persetujuanMutasi(): HasMany
    {
        return $this->hasMany(MutasiAset::class, 'disetujui_oleh');
    }

    /**
     * Dapatkan riwayat peminjaman aset oleh user ini.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanAset::class, 'peminjam_id');
    }

    /**
     * Dapatkan peminjaman aset yang disetujui oleh user ini.
     */
    public function persetujuanPeminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanAset::class, 'disetujui_oleh');
    }

    /**
     * Dapatkan riwayat kondisi aset register yang diupdate oleh user ini.
     */
    public function updateRiwayatKondisiRegister(): HasMany
    {
        return $this->hasMany(RiwayatKondisiRegister::class, 'diupdate_oleh');
    }

    /**
     * Dapatkan riwayat kondisi aset SMKI yang diupdate oleh user ini.
     */
    public function updateRiwayatKondisiSmki(): HasMany
    {
        return $this->hasMany(RiwayatKondisiSmki::class, 'diupdate_oleh');
    }

    /**
     * Dapatkan semua laporan yang dibuat oleh user ini.
     */
    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'dibuat_oleh');
    }
}
