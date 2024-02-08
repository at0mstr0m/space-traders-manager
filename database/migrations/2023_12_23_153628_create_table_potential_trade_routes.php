<?php

use App\Enums\ActivityLevels;
use App\Enums\SupplyLevels;
use App\Enums\TradeSymbols;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    private string $distanceFormula = 'SQRT(POW(origin_x - destination_x, 2) + POW(origin_y - destination_y, 2))';

    private string $profitFormula = 'IF(COALESCE(purchase_price, 0) = 0, 0, (sell_price - purchase_price) / purchase_price)';

    private string $profitPerFlightFormula = '(COALESCE(sell_price,0)-COALESCE(purchase_price,0))*IF(COALESCE(trade_volume_at_origin,0)<COALESCE(trade_volume_at_destination,0),COALESCE(trade_volume_at_origin,0),COALESCE(trade_volume_at_destination,0))';

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
            $table->unique(['trade_symbol', 'origin', 'destination']);
            $table->integer('purchase_price')->nullable();
            $table->enum('supply_at_origin', SupplyLevels::values())->nullable();
            $table->enum('activity_at_origin', ActivityLevels::values())->nullable();
            $table->integer('trade_volume_at_origin')->nullable();
            $table->integer('sell_price')->nullable();
            $table->enum('supply_at_destination', SupplyLevels::values())->nullable();
            $table->enum('activity_at_destination', ActivityLevels::values())->nullable();
            $table->integer('trade_volume_at_destination')->nullable();
            $table->smallInteger('origin_x');
            $table->smallInteger('origin_y');
            $table->smallInteger('destination_x');
            $table->smallInteger('destination_y');
            $table->integer('distance')
                ->virtualAs($this->distanceFormula);
            $table->float('profit')
                ->virtualAs($this->profitFormula);
            $table->integer('profit_per_flight')
                ->virtualAs($this->profitPerFlightFormula);
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
