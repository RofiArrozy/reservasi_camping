<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menendang akun yang dinonaktifkan di SEMUA halaman web (publik maupun dashboard).
 * Didaftarkan ke grup middleware "web" di bootstrap/app.php.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Akun Anda dinonaktifkan. Silakan hubungi admin.';

            // Permintaan AJAX/JSON (mis. cek stok di form pesan) tidak perlu dialihkan.
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 401);
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
