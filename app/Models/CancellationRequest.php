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
     *  - deposit dicatat dikembalikan penuh jika sudah dibayar (pencatatan dummy)
     *  - status pembayaran menjadi refunded jika sebelumnya sudah dibayar
     *
     * @throws LogicException  Jika pengajuan sudah diproses atau booking sudah berjalan/selesai
     */
    public function approve(User $reviewer, ?string $note = null): void
    {
        DB::transaction(function () use ($reviewer, $note) {
            $booking = $this->booking;

            // Periksa status pengajuan terlebih dulu (halaman staf bisa saja sudah kedaluwarsa).
            if (static::whereKey($this->id)->value('status') !== self::STATUS_PENDING) {
                throw new LogicException('Pengajuan pembatalan ini sudah diproses.');
            }

            if (! in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED], true)) {
                throw new LogicException('Booking sudah berjalan, selesai, atau dibatalkan sehingga tidak dapat dibatalkan lagi. Silakan tolak pengajuan ini.');
            }

            // Deposit hanya dicatat "dikembalikan" jika memang sudah dibayar.
            $depositPaid = $booking->payment_status === Booking::PAYMENT_PAID;

            // Update bersyarat (atomik): hanya berhasil jika pengajuan MASIH pending.
            // Mencegah dua staf memproses pengajuan yang sama secara bersamaan.
            $affected = static::whereKey($this->id)
                ->where('status', self::STATUS_PENDING)
                ->update([
                    'status'                => self::STATUS_APPROVED,
                    'reviewed_by'           => $reviewer->id,
                    'reviewed_at'           => now(),
                    'staff_note'            => $note,
                    'deposit_refund_amount' => $depositPaid ? $booking->deposit_amount : 0,
                    'deposit_refund_status' => $depositPaid ? self::REFUND_REFUNDED : self::REFUND_NONE,
                ]);

            if ($affected === 0) {
                throw new LogicException('Pengajuan pembatalan ini sudah diproses.');
            }

            $booking->update([
                'status'         => Booking::STATUS_CANCELLED,
                'payment_status' => $depositPaid ? Booking::PAYMENT_REFUNDED : $booking->payment_status,
            ]);
        });

        $this->refresh();
    }

    /**
     * Tolak pembatalan; booking tidak berubah.
     *
     * @throws LogicException  Jika pengajuan sudah diproses
     */
    public function reject(User $reviewer, ?string $note = null): void
    {
        $affected = static::whereKey($this->id)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status'      => self::STATUS_REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'staff_note'  => $note,
            ]);

        if ($affected === 0) {
            throw new LogicException('Pengajuan pembatalan ini sudah diproses.');
        }

        $this->refresh();
    }
}
