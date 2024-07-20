<?php

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
        Schema::create('market_goods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('waypoint_symbol');
            $table->enum('type', TradeGoodTypes::values());
            $table->enum('trade_symbol', TradeSymbols::values());
            $table->unique(['waypoint_symbol', 'type', 'trade_symbol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_goods');
    }
};
