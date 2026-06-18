<?php

namespace App\Http\Requests\AdminPerbidang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMutasiAsetRequest extends FormRequest
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
        $bidangId = auth()->user()->bidang_id;

        return [
            'jenis_aset' => ['required', 'string', 'in:register,smki'],
            'aset_id' => ['required', 'integer'],
            'bidang_tujuan_id' => [
                'required',
                'integer',
                'exists:bidang,id',
                Rule::notIn([$bidangId]),
            ],
            'tanggal_mutasi' => ['required', 'date'],
            'tanggal_rencana_pengembalian' => ['required', 'date', 'after_or_equal:tanggal_mutasi'],
            'alasan' => ['required', 'string', 'min:10'],
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
            'jenis_aset' => 'Jenis Aset',
            'aset_id' => 'Aset',
            'bidang_tujuan_id' => 'Bidang Tujuan',
            'tanggal_mutasi' => 'Tanggal Mutasi',
            'tanggal_rencana_pengembalian' => 'Tanggal Rencana Pengembalian',
            'alasan' => 'Alasan Mutasi',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bidang_tujuan_id.not_in' => 'Bidang tujuan harus berbeda dari bidang asal.',
        ];
    }
}
