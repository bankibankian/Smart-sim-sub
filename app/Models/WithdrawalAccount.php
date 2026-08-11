<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalAccount extends Model
{
    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
