<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman registrasi.
     */
    public function create(): View
    {
        $bidangs = Bidang::all();
        return view('auth.register', compact('bidangs'));
    }

    /**
     * Tangani pengajuan registrasi.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nip' => ['required', 'string', 'max:255'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:Super Admin,Admin Perbidang,Kepala Dinas,User'],
            'bidang_id' => ['nullable', 'exists:bidang,id'],
        ]);

        $user = User::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_hp' => $request->no_hp,
            'role' => $request->role,
            'bidang_id' => $request->bidang_id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect($this->redirectPath($user->role));
    }

    /**
     * Dapatkan path redirect berdasarkan role user.
     */
    protected function redirectPath(string $role): string
    {
        switch ($role) {
            case 'Super Admin':
                return '/super-admin/dashboard';
            case 'Admin Perbidang':
                return '/admin-perbidang/dashboard';
            case 'Kepala Dinas':
                return '/kepala-dinas/dashboard';
            default:
                return '/user/dashboard';
        }
    }
}
