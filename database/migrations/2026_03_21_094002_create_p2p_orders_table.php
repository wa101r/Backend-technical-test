<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('p2p_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['BUY', 'SELL']);
            $table->enum('crypto_currency', ['BTC', 'ETH', 'XRP', 'DOGE']);
            $table->enum('fiat_currency', ['THB', 'USD']);
            $table->decimal('price', 18, 8);
            $table->decimal('total_amount', 18, 8);
            $table->decimal('remaining_amount', 18, 8);
            $table->enum('status', ['OPEN', 'COMPLETED', 'CANCELLED'])->default('OPEN');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2p_orders');
    }
};
