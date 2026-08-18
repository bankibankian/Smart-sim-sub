<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SmeDataProviderSetting extends Model
{
    public const PROVIDERS = ['legacy', 'smeplug', '9psb', 'amsub'];

    protected $fillable = [
        'provider',
        'base_url',
        'api_key',
        'secret_key',
        'auth_base_url',
        'debit_account',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_key' => 'encrypted',
        'secret_key' => 'encrypted',
    ];

    /**
     * One row per vendor, created on first access. The 'legacy' vendor's
     * base_url/api_key are seeded from its existing .env-backed config so
     * nothing breaks before an admin fills in the DB row, and it starts
     * out active (it's the only vendor that existed before this feature).
     */
    public static function forProvider(string $provider): self
    {
        return static::firstOrCreate(
            ['provider' => $provider],
            match ($provider) {
                'legacy' => [
                    'is_active' => true,
                    'base_url' => config('services.smedata.base_url'),
                    'api_key' => config('services.smedata.api_key'),
                ],
                '9psb' => [
                    'is_active' => false,
                    'base_url' => config('services.ninepsb.base_url'),
                    'auth_base_url' => config('services.ninepsb.auth_base_url'),
                    'api_key' => config('services.ninepsb.api_key'),
                    'secret_key' => config('services.ninepsb.secret_key'),
                    'debit_account' => config('services.ninepsb.debit_account'),
                ],
                'amsub' => [
                    'is_active' => false,
                    'base_url' => config('services.amsub.base_url'),
                    'api_key' => config('services.amsub.api_key'),
                ],
                default => [
                    'is_active' => false,
                    'base_url' => config('services.smeplug.base_url'),
                    'api_key' => config('services.smeplug.api_key'),
                ],
            }
        );
    }

    /**
     * All vendor rows, keyed by provider, creating any missing ones so the
     * admin UI always has a full set of rows to configure.
     */
    public static function allProviders(): Collection
    {
        foreach (self::PROVIDERS as $provider) {
            self::forProvider($provider);
        }

        return static::whereIn('provider', self::PROVIDERS)->get()->keyBy('provider');
    }

    public static function activeProvider(): string
    {
        return static::where('is_active', true)->value('provider') ?? 'legacy';
    }

    /**
     * Atomically flips the active flag so exactly one row is ever active.
     */
    public static function activate(string $provider): void
    {
        DB::transaction(function () use ($provider) {
            static::query()->update(['is_active' => false]);
            self::forProvider($provider)->update(['is_active' => true]);
        });
    }
}
