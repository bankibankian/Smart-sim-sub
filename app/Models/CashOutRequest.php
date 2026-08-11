<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashOutRequest extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_ref',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
        'amount',
        'fee',
        'tax',
        'total_charge',
        'status',
        'admin_notes',
        'approved_by',
        'resolved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_charge' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
