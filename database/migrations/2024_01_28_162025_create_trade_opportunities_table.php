<?php

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeGoodTypes;
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
        Schema::create('trade_opportunities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', TradeSymbols::values());
            $table->string('waypoint_symbol');
            $table->integer('purchase_price');
            $table->integer('sell_price');
            $table->enum('type', TradeGoodTypes::values());
            $table->integer('trade_volume');
            $table->enum('supply', SupplyLevels::values());
            $table->enum('activity', ActivityLevels::values())->nullable();
            $table->unique(['symbol', 'waypoint_symbol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_opportunities');
    }
};
