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
            AccountMode::Online => $this->weekly_online_price,
            AccountMode::OnlineOffline => $this->weekly_online_offline_price,
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


    public function checkOnlineAvailability(): bool
    {
        return $this->accounts()
            ->where('mode', AccountMode::Online)
            ->where('availability', true)
            ->exists();
    }

    public function checkOnlineOfflineAvailability(): bool
    {
        return $this->accounts()
            ->where('mode', AccountMode::OnlineOffline)
            ->where('availability', true)
            ->exists();
    }
}
