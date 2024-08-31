<?php

namespace App\Models;

use App\Enums\AccountMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'mode',
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'availability' => 'boolean',
            'mode' => AccountMode::class,
        ];
    }

    public function Game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
