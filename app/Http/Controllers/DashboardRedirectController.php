<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Mengarahkan pengguna yang sudah login ke dashboard sesuai role-nya.
 */
class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route(auth()->user()->dashboardRoute());
    }
}
