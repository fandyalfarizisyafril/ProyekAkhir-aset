<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'Super Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('pengguna');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip,' . $userId],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:Super Admin,Admin Perbidang,Kepala Dinas'],
            'bidang_id' => ['nullable', 'exists:bidang,id'],
            'status' => ['required', 'string', 'in:Aktif,Non-Aktif,Ditangguhkan'],
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
            'nip' => 'NIP',
            'nama' => 'Nama Pegawai',
            'email' => 'Email',
            'password' => 'Kata Sandi',
            'no_hp' => 'Nomor HP',
            'role' => 'Peran (Role)',
            'bidang_id' => 'Bidang',
            'status' => 'Status',
        ];
    }
}
