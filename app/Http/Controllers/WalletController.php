<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display wallets for a specific user.
     */
    public function index($userId)
    {
        $wallets = Wallet::where('user_id', $userId)->get();
        return response()->json($wallets);
    }

    /**
     * Display a specific wallet with its transactions.
     */
    public function show($id)
    {
        $wallet = Wallet::with(['transactions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->findOrFail($id);
        
        return response()->json($wallet);
    }
}
