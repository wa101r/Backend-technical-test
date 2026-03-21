<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\P2pOrder;
use App\Models\P2pTrade;
use App\Models\User;

class P2pTradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = P2pOrder::first(); // Gets the BTC SELL order from tester1
        $buyer = User::where('username', 'tester3')->first();

        // tester3 buys 0.1 BTC from tester1's order
        P2pTrade::create([
            'order_id' => $order->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $order->user_id,
            'crypto_amount' => 0.1,
            'fiat_amount' => 0.1 * $order->price,
            'status' => 'PENDING',
        ]);

        // Reduce remaining amount in order
        $order->remaining_amount -= 0.1;
        $order->save();
    }
}
