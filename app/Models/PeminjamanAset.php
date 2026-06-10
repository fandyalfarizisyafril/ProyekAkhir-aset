<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanAset extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'peminjaman_aset';

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
        'peminjam_id',
        'nama_peminjam',
        'tanggal_pinjam',
        'tanggal_rencana_kembali',
        'tanggal_kembali',
        'keperluan',
        'status',
        'catatan',
        'disetujui_oleh',
    ];

    /**
     * Dapatkan aset register terkait peminjaman ini.
     */
    public function asetRegister(): BelongsTo
    {
        return $this->belongsTo(AsetRegister::class, 'aset_register_id');
    }

    /**
     * Dapatkan aset SMKI terkait peminjaman ini.
     */
    public function asetSmki(): BelongsTo
    {
        return $this->belongsTo(AsetSmki::class, 'aset_smki_id');
    }

    /**
     * Dapatkan bidang asal aset saat peminjaman diajukan.
     */
    public function bidangAsal(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_asal_id');
    }

    /**
     * Dapatkan user yang meminjam aset.
     */
    public function peminjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    /**
     * Dapatkan user yang menyetujui peminjaman (jika ada).
     */
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
