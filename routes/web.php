<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\CancellationController as StaffCancellationController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\TrackerController;
use App\Http\Controllers\User\AnalyticsController as UserAnalyticsController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\CancellationController as UserCancellationController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Katalog
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/alat/{item}', [CatalogController::class, 'showItem'])->name('catalog.items.show');
Route::get('/katalog/paket/{package}', [CatalogController::class, 'showPackage'])->name('catalog.packages.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{article}', [BlogController::class, 'show'])->name('blog.show');

// Pemesanan & pembayaran (simulasi)
Route::get('/pesan', [BookingController::class, 'create'])->name('booking.create');
Route::get('/pesan/ketersediaan', [BookingController::class, 'availability'])
    ->middleware('throttle:60,1')->name('booking.availability');
Route::post('/pesan', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')->name('booking.store');
Route::get('/booking/{booking:qr_token}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{booking:qr_token}/bayar', [BookingController::class, 'pay'])
    ->middleware('throttle:10,1')->name('booking.pay');

// Pelacakan booking
Route::get('/lacak', [TrackerController::class, 'index'])->name('tracker.index');
Route::post('/lacak', [TrackerController::class, 'search'])
    ->middleware('throttle:10,1')->name('tracker.search');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Area terproteksi (wajib login, dibatasi per role)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Mengarahkan ke dashboard sesuai role
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    // Area User
    Route::middleware('role:user')->prefix('akun')->name('user.')->group(function () {
        Route::get('/', UserDashboardController::class)->name('dashboard');

        Route::get('/riwayat', [UserBookingController::class, 'index'])->name('bookings.index');
        Route::get('/riwayat/{booking}', [UserBookingController::class, 'show'])->name('bookings.show');
        Route::post('/riwayat/{booking}/pembatalan', [UserCancellationController::class, 'store'])
            ->name('cancellations.store');

        Route::get('/profil', [UserProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [UserProfileController::class, 'update'])->name('profile.update');
        Route::put('/profil/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/analitik', UserAnalyticsController::class)->name('analytics');
    });

    // Area Staff (Admin mewarisi akses Staff)
    Route::middleware('role:admin,staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/', StaffDashboardController::class)->name('dashboard');

        Route::get('/pembatalan', [StaffCancellationController::class, 'index'])->name('cancellations.index');
        Route::post('/pembatalan/{cancellation}/setujui', [StaffCancellationController::class, 'approve'])->name('cancellations.approve');
        Route::post('/pembatalan/{cancellation}/tolak', [StaffCancellationController::class, 'reject'])->name('cancellations.reject');
    });

    // Area Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
    });
});
