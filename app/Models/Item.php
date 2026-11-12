<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image_path',
        'total_stock',
        'daily_rate',
        'replacement_cost',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_stock'      => 'integer',
            'daily_rate'       => 'integer',
            'replacement_cost' => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Item $item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------------------------------------------------------------
    // Scope
    // ---------------------------------------------------------------

    /** Hanya alat yang aktif ditampilkan ke pengunjung. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_item')
            ->withPivot('quantity');
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(ItemIncident::class);
    }

    // ---------------------------------------------------------------
    // Logika stok
    // ---------------------------------------------------------------

    /**
     * Hitung stok yang masih tersedia pada rentang tanggal tertentu.
     *
     * Stok terpakai = jumlah unit pada booking berstatus "menahan stok"
     * (lihat Booking::STOCK_HOLDING_STATUSES) yang periodenya beririsan:
     *   start_date <= $end  DAN  end_date >= $start
     *
     * @param  CarbonInterface|string  $start            Tanggal mulai sewa
     * @param  CarbonInterface|string  $end              Tanggal selesai sewa
     * @param  int|null                $excludeBookingId Abaikan booking ini (berguna saat edit booking)
     */
    public function availableStock(
        CarbonInterface|string $start,
        CarbonInterface|string $end,
        ?int $excludeBookingId = null
    ): int {
        // Seragamkan ke format Y-m-d. Perbandingan memakai whereDate() agar
        // akurat apa pun format penyimpanannya (dengan/tanpa jam) di SQLite.
        $startDate = Carbon::parse($start)->toDateString();
        $endDate   = Carbon::parse($end)->toDateString();

        $reserved = $this->bookingItems()
            ->whereHas('booking', function (Builder $query) use ($startDate, $endDate, $excludeBookingId) {
                $query->whereIn('status', Booking::STOCK_HOLDING_STATUSES)
                    ->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);

                if ($excludeBookingId !== null) {
                    $query->where('bookings.id', '!=', $excludeBookingId);
                }
            })
            ->sum('quantity');

        return max(0, $this->total_stock - (int) $reserved);
    }
}
