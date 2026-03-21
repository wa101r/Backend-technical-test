<?php

namespace App\Http\Controllers;

use App\Models\P2pTrade;
use App\Models\P2pOrder;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Calculate fiat amount based on price
        $fiatAmount = $validated['crypto_amount'] * $order->price;

        $sellerId = $order->user_id;

        // Note: In real app, we need DB transaction and wallet locking here
        $trade = P2pTrade::create([
            'order_id' => $order->id,
            'buyer_id' => $validated['buyer_id'],
            'seller_id' => $sellerId,
            'crypto_amount' => $validated['crypto_amount'],
            'fiat_amount' => $fiatAmount,
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
        ]);

        $trade = P2pTrade::findOrFail($id);

        if ($trade->status === 'RELEASED' || $trade->status === 'CANCELLED') {
            return response()->json(['message' => 'Trade cannot be updated anymore'], 400);
        }

        $trade->status = $validated['status'];
        $trade->save();

        // In a complete system, releasing would trigger wallet updates
        if ($validated['status'] === 'RELEASED') {
            $order = P2pOrder::findOrFail($trade->order_id);
            $order->remaining_amount -= $trade->crypto_amount;
            if ($order->remaining_amount <= 0) {
                $order->status = 'COMPLETED';
            }
            $order->save();
        }

        return response()->json($trade);
    }
}
