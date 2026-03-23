<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions for a specific user.
     */
    public function index($userId)
    {
        $transactions = Transaction::where('user_id', $userId)
            ->with(['wallet', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Store a newly created transaction (Deposit, Withdraw, Internal Transfer).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'wallet_id' => 'required|exists:wallets,id',
            'type' => 'required|in:DEPOSIT,WITHDRAW,TRANSFER_INTERNAL',
            'amount' => 'required|numeric|min:0.00000001',
            'to_user_id' => 'required_if:type,TRANSFER_INTERNAL|nullable|exists:users,id',
            'to_address' => 'nullable|string',
        ]);

        $wallet = Wallet::where('id', $validated['wallet_id'])
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        if ($validated['type'] !== 'DEPOSIT' && $wallet->balance < $validated['amount']) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // Update balance
        if ($validated['type'] === 'DEPOSIT') {
            $wallet->balance += $validated['amount'];
        } else {
            $wallet->balance -= $validated['amount'];
        }
        $wallet->save();

        // Internal transfer — credit target user
        if ($validated['type'] === 'TRANSFER_INTERNAL' && isset($validated['to_user_id'])) {
            $targetWallet = Wallet::where('user_id', $validated['to_user_id'])
                ->where('currency_code', $wallet->currency_code)
                ->first();

            if ($targetWallet) {
                $targetWallet->balance += $validated['amount'];
                $targetWallet->save();

                // Create receiving transaction for the target user
                Transaction::create([
                    'user_id' => $validated['to_user_id'],
                    'wallet_id' => $targetWallet->id,
                    'type' => 'DEPOSIT',
                    'currency_code' => $wallet->currency_code,
                    'amount' => $validated['amount'],
                    'reference_type' => 'TRANSFER',
                    'status' => 'COMPLETED',
                ]);
            }
        }

        $transaction = Transaction::create([
            'user_id' => $validated['user_id'],
            'wallet_id' => $wallet->id,
            'type' => $validated['type'],
            'currency_code' => $wallet->currency_code,
            'amount' => $validated['amount'],
            'to_user_id' => $validated['to_user_id'] ?? null,
            'to_address' => $validated['to_address'] ?? null,
            'reference_type' => $validated['type'],
            'status' => 'COMPLETED',
        ]);

        return response()->json($transaction, 201);
    }
}
