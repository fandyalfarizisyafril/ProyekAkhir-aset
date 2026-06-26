<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyusutanAset extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'penyusutan_aset';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'aset_register_id',
        'tahun',
        'umur_manfaat_tahun',
        'nilai_awal_tahun',
        'nilai_residu',
        'beban_penyusutan',
        'nilai_akhir_tahun',
        'metode',
        'dihitung_oleh',
        'tanggal_hitung',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'umur_manfaat_tahun' => 'integer',
            'nilai_awal_tahun' => 'decimal:2',
            'nilai_residu' => 'decimal:2',
            'beban_penyusutan' => 'decimal:2',
            'nilai_akhir_tahun' => 'decimal:2',
            'tanggal_hitung' => 'datetime',
        ];
    }

    /**
     * Dapatkan aset register terkait penyusutan ini.
     */
    public function asetRegister(): BelongsTo
    {
        return $this->belongsTo(AsetRegister::class, 'aset_register_id');
    }

    /**
     * Dapatkan user yang melakukan perhitungan penyusutan.
     */
    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dihitung_oleh');
    }
}
