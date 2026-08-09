<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LeaderboardSettings extends Model
{
    protected $fillable = [
        'is_active',
        'period_type',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * The single settings row, created on first access with safe defaults.
     */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'is_active' => false,
            'period_type' => 'weekly',
        ]);
    }

    /**
     * The currently active counting window.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function periodBounds(): array
    {
        if ($this->period_type === 'custom' && $this->period_start && $this->period_end) {
            return [
                'start' => $this->period_start->copy()->startOfDay(),
                'end' => $this->period_end->copy()->endOfDay(),
            ];
        }

        return [
            'start' => Carbon::now()->startOfWeek(Carbon::MONDAY),
            'end' => Carbon::now()->endOfWeek(Carbon::SUNDAY),
        ];
    }
}
