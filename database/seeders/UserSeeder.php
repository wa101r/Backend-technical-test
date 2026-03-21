<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = ['THB', 'USD', 'BTC', 'ETH', 'XRP', 'DOGE'];

        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'username' => "tester{$i}",
                'email' => "tester{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);

            // Create 6 wallets for each user
            foreach ($currencies as $currency) {
                $balance = 0;
                if ($currency === 'THB') $balance = 100000;
                else if ($currency === 'USD') $balance = 5000;
                else if ($currency === 'BTC') $balance = 1.5;
                else if ($currency === 'ETH') $balance = 10;
                
                Wallet::create([
                    'user_id' => $user->id,
                    'currency_code' => $currency,
                    'balance' => $balance,
                ]);
            }
        }
    }
}
