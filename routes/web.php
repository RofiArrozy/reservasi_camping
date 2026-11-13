<?php

use Illuminate\Support\Facades\Route;

   use App\Http\Controllers\CatalogController;
   use App\Http\Controllers\HomeController;

   Route::get('/', [HomeController::class, 'index'])->name('home');
   Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
   Route::get('/katalog/alat/{item}', [CatalogController::class, 'showItem'])->name('catalog.items.show');
   Route::get('/katalog/paket/{package}', [CatalogController::class, 'showPackage'])->name('catalog.packages.show');

use App\Http\Controllers\Auth\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

Route::middleware('auth')->group(function () {

    // Mengarahkan ke dashboard sesuai role
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    // Area User
    Route::middleware('role:user')->prefix('akun')->name('user.')->group(function () {
        Route::get('/', UserDashboardController::class)->name('dashboard');
    });

    // Area Staff (Admin mewarisi akses Staff)
    Route::middleware('role:admin,staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/', StaffDashboardController::class)->name('dashboard');
    });

    // Area Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
    });
});