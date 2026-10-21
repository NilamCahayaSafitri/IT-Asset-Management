<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memutus sesi user yang dinonaktifkan saat sedang login.
 * Tanpa ini, user nonaktif tetap bisa memakai aplikasi sampai sesinya habis.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status !== 'aktif') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi administrator.']);
        }

        return $next($request);
    }
}
