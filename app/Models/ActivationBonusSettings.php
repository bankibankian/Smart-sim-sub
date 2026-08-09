<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationBonusSettings extends Model
{
    /**
     * Every SIM provider the platform sells (kept in sync with the
     * `provider` values used on `sims` / admin/sim-plan).
     */
    public const PROVIDERS = ['mtn', 'airtel', 'glo', '9mobile'];

    protected $fillable = [
        'provider',
        'is_active',
        'sme_data_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * One row per provider, created on first access with safe defaults.
     */
    public static function forProvider(string $provider): self
    {
        return static::firstOrCreate(
            ['provider' => strtolower($provider)],
            ['is_active' => false]
        );
    }

    /**
     * All provider rows, keyed by provider, creating any missing ones so
     * the admin UI always has a full set of rows to configure.
     */
    public static function allProviders(): \Illuminate\Support\Collection
    {
        foreach (self::PROVIDERS as $provider) {
            self::forProvider($provider);
        }

        return static::whereIn('provider', self::PROVIDERS)->get()->keyBy('provider');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SmeData::class, 'sme_data_id');
    }
}
