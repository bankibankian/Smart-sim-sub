<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimSwapRequest extends Model
{
    protected $fillable = [
        'sim_id',
        'requested_by',
        'from_holder_id',
        'to_holder_id',
        'holder_role',
        'status',
        'admin_notes',
        'approved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function sim(): BelongsTo
    {
        return $this->belongsTo(Sim::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function fromHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_holder_id');
    }

    public function toHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_holder_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
