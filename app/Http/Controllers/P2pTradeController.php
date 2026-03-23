<?php

namespace App\Http\Controllers;

use App\Models\P2pTrade;
use App\Models\P2pOrder;
use App\Models\Wallet;
use Illuminate\Http\Request;

class P2pTradeController extends Controller
{
    /**
     * Store a newly created P2P trade.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:p2p_orders,id',
            'buyer_id' => 'required|exists:users,id',
            'crypto_amount' => 'required|numeric|min:0.00000001',
        ]);

        $order = P2pOrder::findOrFail($validated['order_id']);

        if ($order->status !== 'OPEN') {
            return response()->json(['message' => 'Order is not open'], 400);
        }

        if ($order->remaining_amount < $validated['crypto_amount']) {
            return response()->json(['message' => 'Insufficient remaining amount in the order'], 400);
        }

        // Validate min/max limits
        if ($order->min_limit && $validated['crypto_amount'] < $order->min_limit) {
            return response()->json(['message' => 'Amount is below minimum limit'], 400);
        }
        if ($order->max_limit && $validated['crypto_amount'] > $order->max_limit) {
            return response()->json(['message' => 'Amount exceeds maximum limit'], 400);
        }

        // Calculate fiat amount based on price
        $fiatAmount = $validated['crypto_amount'] * $order->price;
        $sellerId = $order->user_id;

        // Lock crypto in escrow (seller's wallet)
        $sellerWallet = Wallet::where('user_id', $sellerId)
            ->where('currency_code', $order->crypto_currency)
            ->first();

        if ($sellerWallet && $sellerWallet->balance >= $validated['crypto_amount']) {
            $sellerWallet->balance -= $validated['crypto_amount'];
            $sellerWallet->locked_balance += $validated['crypto_amount'];
            $sellerWallet->save();
        }

        $trade = P2pTrade::create([
            'order_id' => $order->id,
            'buyer_id' => $validated['buyer_id'],
            'seller_id' => $sellerId,
            'crypto_amount' => $validated['crypto_amount'],
            'fiat_amount' => $fiatAmount,
            'escrow_locked' => true,
            'status' => 'PENDING',
        ]);

        return response()->json($trade, 201);
    }

    /**
     * Display the specified P2P trade.
     */
    public function show($id)
    {
        $trade = P2pTrade::with(['order', 'buyer:id,username', 'seller:id,username'])->findOrFail($id);
        
        return response()->json($trade);
    }

    /**
     * Update trade status (e.g. PAID, RELEASED, CANCELLED)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:PAID,RELEASED,CANCELLED',
            'payment_proof' => 'nullable|string',
        ]);

        $trade = P2pTrade::findOrFail($id);

        if ($trade->status === 'RELEASED' || $trade->status === 'CANCELLED') {
            return response()->json(['message' => 'Trade cannot be updated anymore'], 400);
        }

        $trade->status = $validated['status'];

        // Buyer marks as PAID with payment proof
        if ($validated['status'] === 'PAID') {
            $trade->paid_at = now();
            if (isset($validated['payment_proof'])) {
                $trade->payment_proof = $validated['payment_proof'];
            }
        }

        // Seller confirms release — unlock escrow and transfer crypto
        if ($validated['status'] === 'RELEASED') {
            $trade->released_at = now();

            // Release escrow: move locked_balance to buyer's wallet
            $sellerWallet = Wallet::where('user_id', $trade->seller_id)
                ->where('currency_code', $trade->order->crypto_currency)
                ->first();
            $buyerWallet = Wallet::where('user_id', $trade->buyer_id)
                ->where('currency_code', $trade->order->crypto_currency)
                ->first();

            if ($sellerWallet) {
                $sellerWallet->locked_balance -= $trade->crypto_amount;
                $sellerWallet->save();
            }
            if ($buyerWallet) {
                $buyerWallet->balance += $trade->crypto_amount;
                $buyerWallet->save();
            }

            // Update order remaining amount
            $order = P2pOrder::findOrFail($trade->order_id);
            $order->remaining_amount -= $trade->crypto_amount;
            if ($order->remaining_amount <= 0) {
                $order->status = 'COMPLETED';
            }
            $order->save();
        }

        // Cancelled — return escrow to seller
        if ($validated['status'] === 'CANCELLED') {
            $sellerWallet = Wallet::where('user_id', $trade->seller_id)
                ->where('currency_code', $trade->order->crypto_currency)
                ->first();
            if ($sellerWallet) {
                $sellerWallet->locked_balance -= $trade->crypto_amount;
                $sellerWallet->balance += $trade->crypto_amount;
                $sellerWallet->save();
            }
            $trade->escrow_locked = false;
        }

        $trade->save();

        return response()->json($trade);
    }
}
