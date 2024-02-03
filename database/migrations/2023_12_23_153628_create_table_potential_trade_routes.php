<?php

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
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
            $table->string('origin');
            $table->string('destination');
            $table->integer('purchase_price')->nullable();
            $table->enum('supply_at_origin', SupplyLevels::values())->nullable();
            $table->enum('activity_at_origin', ActivityLevels::values())->nullable();
            $table->integer('trade_volume_at_origin')->nullable();
            $table->integer('sell_price')->nullable();
            $table->enum('supply_at_destination', SupplyLevels::values())->nullable();
            $table->enum('activity_at_destination', ActivityLevels::values())->nullable();
            $table->integer('trade_volume_at_destination')->nullable();
            $table->integer('distance');
            $table->unique(['trade_symbol', 'origin', 'destination']);
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
