<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use LogicException;

class CancellationRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const REFUND_NONE     = 'none';
    public const REFUND_REFUNDED = 'refunded';

    protected $fillable = [
        'booking_id',
        'user_id',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'staff_note',
        'deposit_refund_amount',
        'deposit_refund_status',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at'           => 'datetime',
            'deposit_refund_amount' => 'integer',
        ];
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Pemohon pembatalan. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Staff/admin yang memutuskan. */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // ---------------------------------------------------------------
    // Aksi staff
    // ---------------------------------------------------------------

    /**
     * Setujui pembatalan (atomik):
     *  - booking menjadi cancelled  -> stok otomatis terlepas
     *  - deposit dicatat dikembalikan penuh (pencatatan dummy)
     *  - status pembayaran menjadi refunded jika sebelumnya sudah dibayar
     */
    public function approve(User $reviewer, ?string $note = null): void
    {
        $this->ensurePending();

        DB::transaction(function () use ($reviewer, $note) {
            $booking = $this->booking;

            $this->update([
                'status'                => self::STATUS_APPROVED,
                'reviewed_by'           => $reviewer->id,
                'reviewed_at'           => now(),
                'staff_note'            => $note,
                'deposit_refund_amount' => $booking->deposit_amount,
                'deposit_refund_status' => self::REFUND_REFUNDED,
            ]);

            $booking->update([
                'status'         => Booking::STATUS_CANCELLED,
                'payment_status' => $booking->payment_status === Booking::PAYMENT_PAID
                    ? Booking::PAYMENT_REFUNDED
                    : $booking->payment_status,
            ]);
        });
    }

    /** Tolak pembatalan; booking tidak berubah. */
    public function reject(User $reviewer, ?string $note = null): void
    {
        $this->ensurePending();

        $this->update([
            'status'      => self::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'staff_note'  => $note,
        ]);
    }

    private function ensurePending(): void
    {
        if (! $this->isPending()) {
            throw new LogicException('Pengajuan pembatalan ini sudah diproses.');
        }
    }
}
