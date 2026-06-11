<?php

namespace App\Http\Requests\AdminPerbidang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAsetSmkiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin Perbidang';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Retrieve the asset ID from the route.
        // It could be an object if implicit model binding is used, or a string/integer ID.
        $asetSmki = $this->route('data_aset_smki');
        $asetSmkiId = is_object($asetSmki) ? $asetSmki->id : $asetSmki;

        return [
            'nomor_kode_barang' => [
                'required',
                'string',
                'max:100',
                Rule::unique('aset_smki', 'nomor_kode_barang')->ignore($asetSmkiId),
            ],
            'jenis_barang' => [
                'required',
                'string',
                'max:255',
                Rule::exists('kategori_aset', 'nama_kategori')->where('tipe', 'SMKI'),
            ],
            'merk_model' => ['required', 'string', 'max:255'],
            'no_ser_model' => ['nullable', 'string', 'max:255'],
            'ukuran' => ['nullable', 'string', 'max:255'],
            'bahan' => ['nullable', 'string', 'max:255'],
            'tahun_pembuatan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'jumlah' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:50'],
            'keadaan_barang' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'keterangan' => ['nullable', 'string'],
            'ruangan' => ['nullable', 'string', 'max:255'],
            'penanggung_jawab' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nomor_kode_barang' => 'Nomor Kode Barang',
            'jenis_barang' => 'Jenis Barang / Nama',
            'merk_model' => 'Merk / Model',
            'no_ser_model' => 'Nomor Seri Pabrik',
            'ukuran' => 'Ukuran',
            'bahan' => 'Bahan',
            'tahun_pembuatan' => 'Tahun Pembelian',
            'jumlah' => 'Jumlah Barang',
            'satuan' => 'Satuan',
            'keadaan_barang' => 'Keadaan Barang',
            'keterangan' => 'Keterangan',
            'ruangan' => 'Ruangan',
            'penanggung_jawab' => 'Penanggung Jawab',
        ];
    }
}
