<?php

use App\Enums\Supplies;
use App\Enums\TradeSymbols;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('potential_trade_routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('trade_symbol', TradeSymbols::values());
            $table->tinyText('origin');
            $table->tinyText('destination');
            $table->integer('purchase_price')->nullable();
            $table->enum('supply_at_origin', Supplies::values())->nullable();
            $table->integer('trade_volume_at_origin')->nullable();
            $table->integer('sell_price')->nullable();
            $table->enum('supply_at_destination', Supplies::values())->nullable();
            $table->integer('trade_volume_at_destination')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potential_trade_routes');
    }
};
