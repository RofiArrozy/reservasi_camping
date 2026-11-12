<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    // Status booking
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PICKED_UP = 'picked_up';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Status yang masih "menahan" stok alat. Booking berstatus
     * completed/cancelled otomatis melepas stok.
     */
    public const STOCK_HOLDING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PICKED_UP,
    ];

    // Status pembayaran (dummy / sandbox)
    public const PAYMENT_UNPAID   = 'unpaid';
    public const PAYMENT_PAID     = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'booking_code',
        'qr_token',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_date',
        'end_date',
        'total_days',
        'subtotal',
        'deposit_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            // Format 'Y-m-d' agar tersimpan tanpa jam (penting untuk query rentang tanggal di SQLite)
            'start_date'     => 'date:Y-m-d',
            'end_date'       => 'date:Y-m-d',
            'total_days'     => 'integer',
            'subtotal'       => 'integer',
            'deposit_amount' => 'integer',
            'total_amount'   => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Isi kode booking & token QR otomatis saat booking dibuat.
        static::creating(function (Booking $booking) {
            $booking->booking_code ??= static::generateBookingCode();
            $booking->qr_token ??= Str::random(48);
        });
    }

    /** Contoh hasil: CMP-20261110-X7K2 (17 karakter). */
    public static function generateBookingCode(): string
    {
        do {
            $code = 'CMP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (static::where('booking_code', $code)->exists());

        return $code;
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Detail alat yang disewa. */
    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(ItemIncident::class);
    }

    // ---------------------------------------------------------------
    // Scope
    // ---------------------------------------------------------------

    /** Booking yang masih menahan stok. */
    public function scopeHoldingStock(Builder $query): Builder
    {
        return $query->whereIn('status', self::STOCK_HOLDING_STATUSES);
    }

    /**
     * Pelacakan booking oleh pengunjung: kode booking + (email ATAU telepon).
     */
    public function scopeTrack(Builder $query, string $code, string $contact): Builder
    {
        return $query->where('booking_code', strtoupper(trim($code)))
            ->where(function (Builder $q) use ($contact) {
                $q->where('customer_email', trim($contact))
                  ->orWhere('customer_phone', trim($contact));
            });
    }

    // ---------------------------------------------------------------
    // Helper & accessor
    // ---------------------------------------------------------------

    /**
     * Booking boleh diajukan pembatalan jika masih pending/confirmed
     * dan belum ada pengajuan pembatalan yang menunggu keputusan.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)
            && ! $this->cancellationRequests()
                ->where('status', CancellationRequest::STATUS_PENDING)
                ->exists();
    }

    /** Label status dalam Bahasa Indonesia untuk tampilan. */
    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            self::STATUS_PENDING   => 'Menunggu Konfirmasi',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_PICKED_UP => 'Sedang Disewa',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default                => ucfirst((string) $this->status),
        });
    }
}
