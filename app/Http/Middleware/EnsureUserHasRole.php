<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses route berdasarkan role pengguna.
 *
 * Pemakaian di route (daftar role dipisah koma):
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,staff')
 *
 * Middleware ini juga memeriksa status aktif akun di setiap request, sehingga
 * akun yang dinonaktifkan admin langsung tertendang walau sesinya masih berjalan.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login -> arahkan ke halaman login.
        if (! $user) {
            return redirect()->route('login');
        }

        // Akun dinonaktifkan -> keluarkan paksa dan bersihkan sesi.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        // Role tidak termasuk yang diizinkan -> 403.
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
