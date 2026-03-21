<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P2pOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'crypto_currency',
        'fiat_currency',
        'price',
        'total_amount',
        'remaining_amount',
        'status',
    ];

    /**
     * Get the user that created the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trades for the order.
     */
    public function trades(): HasMany
    {
        return $this->hasMany(P2pTrade::class, 'order_id');
    }
}
