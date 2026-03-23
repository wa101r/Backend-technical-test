<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::where('username', 'tester1')->first();
        $thbWallet = Wallet::where('user_id', $user1->id)->where('currency_code', 'THB')->first();

        // Add a demo DEPOSIT transaction
        Transaction::create([
            'user_id' => $user1->id,
            'wallet_id' => $thbWallet->id,
            'type' => 'DEPOSIT',
            'currency_code' => 'THB',
            'amount' => 50000,
            'reference_type' => 'DEPOSIT',
            'status' => 'COMPLETED',
        ]);
    }
}
