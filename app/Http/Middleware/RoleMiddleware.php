<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Tangani permintaan yang masuk.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Cek apakah role user ada di dalam daftar role yang diizinkan
        if (!in_array($user->role, $roles)) {
            abort(403, 'Aksi tidak diizinkan. Anda tidak memiliki role yang sesuai.');
        }

        return $next($request);
    }
}
