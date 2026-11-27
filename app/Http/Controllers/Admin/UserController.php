<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\ActivityLog;
use App\Models\ItemIncident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Manajemen akun (khusus Admin): CRUD Admin, Staff, dan User.
 */
class UserController extends Controller
{
    private const ROLES = [User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_USER];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'role'   => ['nullable', 'in:' . implode(',', self::ROLES)],
            'status' => ['nullable', 'in:aktif,nonaktif'],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, function ($query, $v) {
                $like = '%' . $v . '%';
                $query->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($filters['role'] ?? null, fn ($q, $v) => $q->where('role', $v))
            ->when(($filters['status'] ?? null) === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['role' => User::ROLE_STAFF, 'is_active' => true]),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Password di-hash otomatis oleh cast 'hashed' pada model User.
        $user = new User(Arr::only($data, ['name', 'email', 'phone', 'address', 'role', 'password']));
        $user->is_active = $request->boolean('is_active');
        $user->forceFill(['email_verified_at' => now()])->save();

        // Catatan: password TIDAK pernah dimasukkan ke log.
        ActivityLog::record('user.created', $user, "Membuat akun {$user->email} ({$user->role})", ['role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->email} berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data   = $request->validated();
        $active = $request->boolean('is_active');

        // Cegah admin mengunci dirinya sendiri dari sistem.
        if ($user->is($request->user()) && ($data['role'] !== $user->role || ! $active)) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat mengubah peran atau menonaktifkan akun Anda sendiri.',
            ]);
        }

        $user->fill(Arr::only($data, ['name', 'email', 'phone', 'address', 'role']));
        $user->is_active = $active;

        $passwordChanged = filled($data['password'] ?? null);
        if ($passwordChanged) {
            $user->password = $data['password'];
        }

        $changes = Arr::except($user->getDirty(), ['password', 'updated_at']);
        $before  = Arr::only($user->getOriginal(), array_keys($changes));

        $user->save();

        ActivityLog::record('user.updated', $user, "Mengubah akun {$user->email}", [
            'old' => $before, 'new' => $changes, 'password_reset' => $passwordChanged,
        ]);

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->email} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Akun yang punya riwayat dipertahankan agar data booking dan jejak audit tetap utuh.
        if ($this->hasHistory($user)) {
            return back()->with('error', "Akun {$user->email} memiliki riwayat (booking, aktivitas, atau artikel) sehingga tidak dapat dihapus. Nonaktifkan saja.");
        }

        $user->delete();

        ActivityLog::record('user.deleted', null, "Menghapus akun {$user->email} ({$user->role})", ['email' => $user->email]);

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->email} dihapus.");
    }

    private function hasHistory(User $user): bool
    {
        return $user->bookings()->exists()
            || $user->cancellationRequests()->exists()
            || $user->reviewedCancellations()->exists()
            || $user->articles()->exists()
            || $user->activityLogs()->exists()
            || ItemIncident::where('reported_by', $user->id)->exists();
    }
}
