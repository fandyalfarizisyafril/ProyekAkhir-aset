<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenghapusanAset extends Model
{
    use HasFactory;

    protected $table = 'penghapusan_aset';

    protected $fillable = [
        'aset_register_id',
        'aset_smki_id',
        'jenis_aset',
        'kode_aset',
        'nama_aset',
        'bidang_id',
        'nilai_perolehan',
        'beban_penyusutan',
        'tahun_penyusutan',
        'nilai_buku',
        'tanggal_penghapusan',
        'metode_penghapusan',
        'alasan',
        'status_sebelum',
        'dihapus_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nilai_perolehan' => 'decimal:2',
            'beban_penyusutan' => 'decimal:2',
            'tahun_penyusutan' => 'integer',
            'nilai_buku' => 'decimal:2',
            'tanggal_penghapusan' => 'date',
        ];
    }

    public function asetRegister(): BelongsTo
    {
        return $this->belongsTo(AsetRegister::class, 'aset_register_id');
    }

    public function asetSmki(): BelongsTo
    {
        return $this->belongsTo(AsetSmki::class, 'aset_smki_id');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function remover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dihapus_oleh');
    }
}
