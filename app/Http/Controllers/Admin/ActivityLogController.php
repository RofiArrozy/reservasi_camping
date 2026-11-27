<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Log aktivitas staf & admin (read-only, khusus Admin).
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'user'   => ['nullable', 'integer'],
            'action' => ['nullable', 'string', 'max:60'],
            'q'      => ['nullable', 'string', 'max:100'],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = ActivityLog::with('user')
            ->when($filters['user'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('description', 'like', '%' . $v . '%'))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs'    => $logs,
            'filters' => $filters,
            'actors'  => User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->orderBy('name')->get(['id', 'name', 'role']),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
