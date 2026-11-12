<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {

    // Mengarahkan ke dashboard sesuai role
    Route::get('/dashboard', function () {
        return redirect()->route(match (auth()->user()->role) {
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            default => 'user.dashboard',
        });
    })->name('dashboard');

    // Area User
    Route::middleware('role:user')->prefix('akun')->name('user.')->group(function () {
        Route::get('/', fn () => 'Dashboard User (sementara)')->name('dashboard');
    });

    // Area Staff (Admin mewarisi akses Staff)
    Route::middleware('role:admin,staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/', fn () => 'Dashboard Staff (sementara)')->name('dashboard');
    });

    // Area Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => 'Dashboard Admin (sementara)')->name('dashboard');
    });
});