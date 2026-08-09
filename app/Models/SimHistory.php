<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimHistory extends Model
{
    protected $fillable = [
        'sim_id',
        'from_status',
        'to_status',
        'actor_id',
        'note',
    ];

    public function sim(): BelongsTo
    {
        return $this->belongsTo(Sim::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
