<?php

namespace App\Http\Requests\AdminPerbidang;

use Illuminate\Foundation\Http\FormRequest;

class StoreKondisiAsetRequest extends FormRequest
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
        return [
            'tipe_aset' => ['required', 'string', 'in:SMKI,REGISTER'],
            'aset_id' => ['required', 'integer'],
            'keadaan_baru' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'catatan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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
            'tipe_aset' => 'Tipe Aset',
            'aset_id' => 'Aset',
            'keadaan_baru' => 'Kondisi Baru',
            'catatan' => 'Catatan Tambahan',
            'foto' => 'Foto Pendukung',
        ];
    }
}
