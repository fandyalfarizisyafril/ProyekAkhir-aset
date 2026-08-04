<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanMutasiAset extends Model
{
    use HasFactory;

    protected $table = 'permintaan_mutasi_aset';

    protected $fillable = [
        'jenis_aset',
        'kategori_aset',
        'nama_kebutuhan',
        'lokasi_penggunaan',
        'spesifikasi',
        'alasan',
        'status',
        'tanggal_permintaan',
        'bidang_peminta_id',
        'diminta_oleh',
        'diproses_oleh',
        'mutasi_aset_id',
        'catatan_super_admin',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_permintaan' => 'date',
        ];
    }

    public function bidangPeminta(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_peminta_id');
    }

    public function peminta(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diminta_oleh');
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function mutasiAset(): BelongsTo
    {
        return $this->belongsTo(MutasiAset::class, 'mutasi_aset_id');
    }
}
