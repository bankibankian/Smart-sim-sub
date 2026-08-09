<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationBonusSettings extends Model
{
    protected $fillable = [
        'is_active',
        'sme_data_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The single settings row, created on first access with safe defaults.
     */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'is_active' => false,
        ]);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SmeData::class, 'sme_data_id');
    }
}
