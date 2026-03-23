<?php

namespace App\Http\Controllers;

use App\Models\P2pOrder;
use Illuminate\Http\Request;

class P2pOrderController extends Controller
{
    /**
     * Display a listing of P2P orders.
     */
    public function index(Request $request)
    {
        $query = P2pOrder::where('status', 'OPEN')->with('user:id,username');

        // Optional filtering by type or currency
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('crypto_currency')) {
            $query->where('crypto_currency', $request->crypto_currency);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($orders);
    }

    /**
     * Store a newly created P2P order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:BUY,SELL',
            'crypto_currency' => 'required|in:BTC,ETH,XRP,DOGE',
            'fiat_currency' => 'required|in:THB,USD',
            'price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'min_limit' => 'nullable|numeric|min:0',
            'max_limit' => 'nullable|numeric|min:0',
        ]);

        $validated['remaining_amount'] = $validated['total_amount'];
        $validated['status'] = 'OPEN';

        $order = P2pOrder::create($validated);

        return response()->json($order, 201);
    }

    /**
     * Display the specified P2P order with its trades.
     */
    public function show($id)
    {
        $order = P2pOrder::with(['trades.buyer', 'trades.seller', 'user'])->findOrFail($id);
        
        return response()->json($order);
    }
}
