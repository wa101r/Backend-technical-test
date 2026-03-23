<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P2pTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'buyer_id',
        'seller_id',
        'crypto_amount',
        'fiat_amount',
        'payment_proof',
        'escrow_locked',
        'status',
        'paid_at',
        'released_at',
    ];

    protected $casts = [
        'escrow_locked' => 'boolean',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    /**
     * Get the order that this trade belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(P2pOrder::class, 'order_id');
    }

    /**
     * Get the buyer user.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller user.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
