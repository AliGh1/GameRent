<?php

namespace App\Models;

use App\Enums\AccountMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'slug',
        'weekly_online_price',
        'weekly_online_offline_price',
        'release_date',
        'age_rating',
    ];

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'game_platform');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function calculatePrice(int $duration, AccountMode $accountMode): float
    {
        $basePrice = match ($accountMode) {
            AccountMode::ONLINE => $this->weekly_online_price,
            AccountMode::ONLINE_OFFLINE => $this->weekly_online_offline_price,
        };

        $discount = match ($duration) {
            2 => 0.10, // 10% discount for two weeks
            3 => 0.15, // 15% discount for three weeks
            4 => 0.20, // 20% discount for one month
            default => 0.00,
        };

        $totalPrice = $basePrice * $duration * (1 - $discount);

        return round($totalPrice);
    }

    /**
     * Check the availability of accounts based on mode.
     *
     * @param AccountMode $mode
     * @return bool
     */
    public function checkAvailability(AccountMode $mode): bool
    {
        return $this->accounts()
            ->where('mode', $mode)
            ->where('availability', true)
            ->exists();
    }

    /**
     * Get an available account for this game based on the specified mode.
     *
     * @param AccountMode $mode
     * @return Account|null
     */
    public function getAvailableAccountByMode(AccountMode $mode): ?Account
    {
        return $this->accounts()
            ->where('mode', $mode)
            ->where('availability', true)
            ->first();
    }
}
