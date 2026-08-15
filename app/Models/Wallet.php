<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'balance',
    'bonus',
    'hold_amount',
    'total_credited',
    'total_debited',
    'wallet_number',
    'currency',
    'daily_limit',
    'monthly_limit',
    'status',
    'is_locked',
    'pnd_at',
    'pnd_reason',
    'pnd_by',
    'last_activity',
])]
class Wallet extends Model
{
    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create the wallet for a user, generating wallet_number the
     * same way registration does. `wallet_number` has no DB default, so a
     * plain `Wallet::firstOrCreate(['user_id' => ...], ['balance' => ...])`
     * 500s the moment it needs to actually create a row — this happened
     * identically in four separate controllers before being centralized
     * here.
     */
    public static function firstOrCreateForUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'balance' => 0.00,
                'status' => 'active',
                'wallet_number' => 'WLT' . str_pad((string) $userId, 7, '0', STR_PAD_LEFT),
            ]
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance'        => 'decimal:2',
            'bonus'          => 'decimal:2',
            'hold_amount'    => 'decimal:2',
            'total_credited' => 'decimal:2',
            'total_debited'  => 'decimal:2',
            'daily_limit'    => 'decimal:2',
            'monthly_limit'  => 'decimal:2',
            'is_locked'      => 'boolean',
            'pnd_at'         => 'datetime',
            'last_activity'  => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Status Helpers
    // -------------------------------------------------------------------------

    /** Is the wallet available for transactions? */
    public function isAvailable(): bool
    {
        return $this->status === 'active' && !$this->is_locked;
    }

    /** Place a Post No Debit restriction — blocks cashout only, not spending. */
    public function placePnd(string $reason = '', ?int $by = null): void
    {
        $this->forceFill([
            'is_locked'  => true,
            'pnd_at'     => now(),
            'pnd_reason' => $reason,
            'pnd_by'     => $by,
        ])->save();
    }

    /** Lift a Post No Debit restriction. */
    public function liftPnd(): void
    {
        $this->forceFill([
            'is_locked'  => false,
            'pnd_at'     => null,
            'pnd_reason' => null,
            'pnd_by'     => null,
        ])->save();
    }

    // -------------------------------------------------------------------------
    // Balance Helpers
    // -------------------------------------------------------------------------

    /** Total spendable balance (balance + bonus). */
    public function spendable(): float
    {
        return (float) $this->balance + (float) $this->bonus;
    }

    /**
     * Debit wallet and update totals atomically.
     * Throws \RuntimeException if balance is insufficient or wallet is locked.
     */
    public function debit(float $amount): void
    {
        if ($this->is_locked) {
            throw new \RuntimeException('Wallet is locked.');
        }
        if ($this->balance < $amount) {
            throw new \RuntimeException('Insufficient wallet balance.');
        }

        $this->forceFill([
            'balance'       => $this->balance - $amount,
            'hold_amount'   => max(0, $this->hold_amount - $amount),
            'total_debited' => $this->total_debited + $amount,
            'last_activity' => now(),
        ])->save();
    }

    /**
     * Credit wallet and update totals.
     */
    public function credit(float $amount): void
    {
        $this->forceFill([
            'balance'        => $this->balance + $amount,
            'total_credited' => $this->total_credited + $amount,
            'last_activity'  => now(),
        ])->save();
    }
}

