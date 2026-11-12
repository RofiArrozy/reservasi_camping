<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemIncident extends Model
{
    use HasFactory;

    public const TYPE_DAMAGED = 'damaged';
    public const TYPE_LOST    = 'lost';

    public const STATUS_REPORTED = 'reported';
    public const STATUS_BILLED   = 'billed';
    public const STATUS_PAID     = 'paid';
    public const STATUS_WAIVED   = 'waived';

    protected $fillable = [
        'booking_id',
        'item_id',
        'reported_by',
        'type',
        'quantity',
        'description',
        'replacement_cost',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity'         => 'integer',
            'replacement_cost' => 'integer',
            'resolved_at'      => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    /** Staff yang melaporkan kerusakan/kehilangan. */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
