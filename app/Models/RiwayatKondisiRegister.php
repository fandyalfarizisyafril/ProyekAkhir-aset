<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKondisiRegister extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'riwayat_kondisi_register';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aset_register_id',
        'keadaan_lama',
        'keadaan_baru',
        'catatan',
        'foto_path',
        'diupdate_oleh',
    ];

    /**
     * Dapatkan aset register terkait riwayat kondisi ini.
     */
    public function asetRegister(): BelongsTo
    {
        return $this->belongsTo(AsetRegister::class, 'aset_register_id');
    }

    /**
     * Dapatkan user yang mengupdate kondisi ini.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diupdate_oleh');
    }
}
