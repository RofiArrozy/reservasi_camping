<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $roles = User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        $stats = [
            'admin'    => (int) ($roles[User::ROLE_ADMIN] ?? 0),
            'staff'    => (int) ($roles[User::ROLE_STAFF] ?? 0),
            'user'     => (int) ($roles[User::ROLE_USER] ?? 0),
            'inactive' => User::where('is_active', false)->count(),
            'bookings' => Booking::count(),
            'income'   => (int) Booking::where('payment_status', Booking::PAYMENT_PAID)->sum('total_amount'),
            'logs24'   => ActivityLog::where('created_at', '>=', now()->subDay())->count(),
        ];

        $recentLogs = ActivityLog::with('user')->latest('id')->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }
}
