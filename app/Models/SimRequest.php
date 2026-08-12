<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sim_id',
        'number',
        'category',
        'provider',
        'quantity',
        'request_type',
        'status',
        'amount',
        'admin_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get the user who made the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the SIM associated with this request.
     */
    public function sim(): BelongsTo
    {
        return $this->belongsTo(Sim::class, 'sim_id');
    }
}
