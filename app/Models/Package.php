<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'discount_percent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'integer',
            'is_active'        => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Package $package) {
            if (empty($package->slug)) {
                $package->slug = Str::slug($package->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------

    /** Alat penyusun paket, lengkap dengan jumlah unit di pivot. */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'package_item')
            ->withPivot('quantity');
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    // ---------------------------------------------------------------
    // Harga (dihitung dari alat penyusun, tidak disimpan di database)
    // ---------------------------------------------------------------

    /** Total tarif harian seluruh alat dalam paket sebelum diskon. */
    protected function normalDailyPrice(): Attribute
    {
        return Attribute::get(fn () => (int) $this->items->sum(
            fn (Item $item) => $item->daily_rate * $item->pivot->quantity
        ));
    }

    /** Tarif harian paket setelah diskon. */
    protected function dailyPrice(): Attribute
    {
        return Attribute::get(fn () => (int) round(
            $this->normal_daily_price * (100 - $this->discount_percent) / 100
        ));
    }
}
