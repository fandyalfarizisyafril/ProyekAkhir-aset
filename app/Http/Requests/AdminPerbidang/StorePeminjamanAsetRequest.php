<?php

namespace App\Http\Requests\AdminPerbidang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeminjamanAsetRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh mengajukan peminjaman aset.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin Perbidang';
    }

    /**
     * Aturan validasi pengajuan peminjaman aset.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'jenis_aset' => ['required', Rule::in(['register', 'smki'])],
            'aset_id' => ['required', 'integer'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_rencana_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan' => ['required', 'string', 'min:10'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    /**
     * Nama atribut yang lebih ramah untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'jenis_aset' => 'Jenis Aset',
            'aset_id' => 'Aset',
            'tanggal_pinjam' => 'Tanggal Pinjam',
            'tanggal_rencana_kembali' => 'Tanggal Rencana Kembali',
            'keperluan' => 'Keperluan',
            'catatan' => 'Catatan',
        ];
    }
}
