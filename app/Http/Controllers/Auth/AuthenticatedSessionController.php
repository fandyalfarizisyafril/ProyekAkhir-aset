<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Tangani pengajuan login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Alihkan pengguna ke dashboard yang sesuai berdasarkan role masing-masing
        switch ($user->role) {
            case 'Super Admin':
                return redirect()->intended('/super-admin/dashboard');
            case 'Admin Perbidang':
                return redirect()->intended('/admin-perbidang/dashboard');
            case 'Kepala Dinas':
                return redirect()->intended('/kepala-dinas/dashboard');
            default:
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(403, 'Role pengguna tidak terdaftar untuk sistem ini.');
        }
    }

    /**
     * Hancurkan sesi autentikasi (Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
