<?php

namespace App\Http\Requests\AdminPerbidang;

use App\Http\Requests\Concerns\NormalizesCurrencyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAsetRegisterRequest extends FormRequest
{
    use NormalizesCurrencyInput;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Admin Perbidang';
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nilai')) {
            $this->merge([
                'nilai' => $this->normalizeCurrencyInput($this->input('nilai')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $asetRegister = $this->route('data_aset_register');
        $asetRegisterId = is_object($asetRegister) ? $asetRegister->id : $asetRegister;

        return [
            'kode_aset' => [
                'required',
                'string',
                'max:100',
                Rule::unique('aset_register', 'kode_aset')->ignore($asetRegisterId),
            ],
            'nama_aset' => ['required', 'string', 'max:255'],
            'kode_barang' => ['required', 'string', 'max:255'],
            'kode_urut_barang' => ['required', 'string', 'max:255'],
            'status_barang' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'pemilik_aset' => ['required', 'string', 'max:255'],
            'pengguna' => ['required', 'string', 'max:255'],
            'lokasi_aset' => ['required', 'string', 'max:255'],
            'metode_pemusnahan' => ['nullable', 'string', 'max:255'],
            'kerahasiaan' => ['required', 'string', 'in:Umum,Terbatas,Rahasia'],
            'kritikalitas' => ['required', 'string', 'in:RENDAH,SEDANG,TINGGI'],
            'nilai' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
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
            'kode_aset' => 'Kode Aset',
            'nama_aset' => 'Nama Aset',
            'kode_barang' => 'Kode Barang',
            'kode_urut_barang' => 'Kode Urut Barang',
            'status_barang' => 'Status Barang',
            'pemilik_aset' => 'Pemilik Aset',
            'pengguna' => 'Pengguna (Personel)',
            'lokasi_aset' => 'Lokasi Aset',
            'metode_pemusnahan' => 'Metode Pemusnahan',
            'kerahasiaan' => 'Kerahasiaan',
            'kritikalitas' => 'Kritikalitas',
            'nilai' => 'Nilai Perolehan (RP)',
            'keterangan' => 'Keterangan Tambahan',
        ];
    }
}
