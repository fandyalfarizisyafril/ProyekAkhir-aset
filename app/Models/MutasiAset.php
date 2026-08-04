<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MutasiAset extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'mutasi_aset';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'jenis_aset',
        'aset_register_id',
        'aset_smki_id',
        'bidang_asal_id',
        'bidang_tujuan_id',
        'alasan',
        'status',
        'diajukan_oleh',
        'disetujui_oleh',
        'tanggal_mutasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mutasi' => 'date',
        ];
    }

    /**
     * Dapatkan aset register terkait mutasi ini (jika jenis_aset = register).
     */
    public function asetRegister(): BelongsTo
    {
        return $this->belongsTo(AsetRegister::class, 'aset_register_id');
    }

    /**
     * Dapatkan aset SMKI terkait mutasi ini (jika jenis_aset = smki).
     */
    public function asetSmki(): BelongsTo
    {
        return $this->belongsTo(AsetSmki::class, 'aset_smki_id');
    }

    /**
     * Dapatkan bidang asal aset.
     */
    public function bidangAsal(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_asal_id');
    }

    /**
     * Dapatkan bidang tujuan mutasi aset.
     */
    public function bidangTujuan(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_tujuan_id');
    }

    /**
     * Dapatkan user yang mengajukan mutasi.
     */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    /**
     * Dapatkan user yang menyetujui mutasi (jika ada).
     */
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Dapatkan permintaan mutasi yang menjadi sumber mutasi ini, jika ada.
     */
    public function permintaanMutasi(): HasOne
    {
        return $this->hasOne(PermintaanMutasiAset::class, 'mutasi_aset_id');
    }
}
