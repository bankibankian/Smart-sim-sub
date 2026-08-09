<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardTier extends Model
{
    protected $fillable = [
        'activation_target',
        'bonus_amount',
        'status',
    ];

    protected $casts = [
        'activation_target' => 'integer',
        'bonus_amount' => 'decimal:2',
    ];

    /**
     * The highest active tier reached by this activation count, or null if
     * the count doesn't reach even the lowest tier.
     */
    public static function resolveForCount(int $count): ?self
    {
        return static::where('status', 'active')
            ->where('activation_target', '<=', $count)
            ->orderByDesc('activation_target')
            ->first();
    }

    /**
     * The next active tier above this activation count, or null if the
     * count has already reached (or exceeds) every configured tier.
     */
    public static function nextTier(int $count): ?self
    {
        return static::where('status', 'active')
            ->where('activation_target', '>', $count)
            ->orderBy('activation_target')
            ->first();
    }
}
