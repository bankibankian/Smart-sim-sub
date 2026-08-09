<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionEarning extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'sim_id',
        'amount',
        'period_week',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_week' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sim(): BelongsTo
    {
        return $this->belongsTo(Sim::class);
    }
}
