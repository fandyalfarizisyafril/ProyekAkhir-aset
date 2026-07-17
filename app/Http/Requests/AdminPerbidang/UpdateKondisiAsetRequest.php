<?php

namespace App\Http\Requests\AdminPerbidang;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKondisiAsetRequest extends FormRequest
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
            'keadaan_baru' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'catatan' => ['required', 'string', 'max:1000'],
            'foto' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'catatan.required' => 'Catatan perubahan kondisi wajib diisi.',
            'foto.required' => 'Foto kondisi terbaru wajib diunggah.',
            'foto.image' => 'File foto kondisi harus berupa gambar.',
            'foto.mimes' => 'Foto kondisi hanya boleh berformat JPEG, JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto kondisi maksimal 2MB.',
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
            'keadaan_baru' => 'Kondisi Baru',
            'catatan' => 'Catatan Tambahan',
            'foto' => 'Foto Pendukung',
        ];
    }
}
