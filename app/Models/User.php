<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Konstanta peran agar tidak ada string "ajaib" di seluruh aplikasi. */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_USER  = 'user';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut. 'hashed' memastikan password otomatis di-hash
     * saat di-set (Laravel 11+), sehingga tidak perlu Hash::make manual.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ---------------------------------------------------------------
    // Helper peran
    // ---------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    /** Admin mewarisi seluruh hak akses staff. */
    public function isAdminOrStaff(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_STAFF], true);
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
    }

    /** Pengajuan pembatalan yang diproses (approve/reject) oleh user ini sebagai staff. */
    public function reviewedCancellations(): HasMany
    {
        return $this->hasMany(CancellationRequest::class, 'reviewed_by');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
