<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\P2pOrder;
use App\Models\User;

class P2pOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::where('username', 'tester1')->first();
        $user2 = User::where('username', 'tester2')->first();

        // tester1 wants to SELL 0.5 BTC for THB
        P2pOrder::create([
            'user_id' => $user1->id,
            'type' => 'SELL',
            'crypto_currency' => 'BTC',
            'fiat_currency' => 'THB',
            'price' => 1500000,
            'total_amount' => 0.5,
            'remaining_amount' => 0.5,
            'status' => 'OPEN',
        ]);

        // tester2 wants to BUY 2 ETH with USD
        P2pOrder::create([
            'user_id' => $user2->id,
            'type' => 'BUY',
            'crypto_currency' => 'ETH',
            'fiat_currency' => 'USD',
            'price' => 2500,
            'total_amount' => 2,
            'remaining_amount' => 2,
            'status' => 'OPEN',
        ]);
    }
}
