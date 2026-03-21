<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the wallets for the user.
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get the p2p orders created by the user.
     */
    public function p2pOrders()
    {
        return $this->hasMany(P2pOrder::class);
    }

    /**
     * Get the buy trades for the user.
     */
    public function buyTrades()
    {
        return $this->hasMany(P2pTrade::class, 'buyer_id');
    }

    /**
     * Get the sell trades for the user.
     */
    public function sellTrades()
    {
        return $this->hasMany(P2pTrade::class, 'seller_id');
    }

    /**
     * Get the transactions made by the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
