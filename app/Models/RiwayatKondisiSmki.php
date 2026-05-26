<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKondisiSmki extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'riwayat_kondisi_smki';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aset_smki_id',
        'keadaan_lama',
        'keadaan_baru',
        'catatan',
        'foto_path',
        'diupdate_oleh',
    ];

    /**
     * Dapatkan aset SMKI terkait riwayat kondisi ini.
     */
    public function asetSmki(): BelongsTo
    {
        return $this->belongsTo(AsetSmki::class, 'aset_smki_id');
    }

    /**
     * Dapatkan user yang mengupdate kondisi ini.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diupdate_oleh');
    }
}
