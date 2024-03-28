<?php

use App\Enums\TradeSymbols;
use App\Enums\TransactionTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('agent_symbol', 32);
            $table->string('ship_symbol', 32);
            $table->string('waypoint_symbol', 32);
            $table->timestamp('timestamp');
            $table->enum('type', TransactionTypes::values())
                ->default(TransactionTypes::PURCHASE);
            $table->enum('trade_symbol', TradeSymbols::values())
                ->nullable();
            $table->integer('units')
                ->default(1);
            $table->integer('price_per_unit');
            $table->integer('total_price');
            $table->unique([
                'ship_symbol',
                'waypoint_symbol',
                'timestamp',
                'trade_symbol',
                'total_price',
            ], 'unique_transactions');
        });

        DB::statement('ALTER TABLE transactions ADD CONSTRAINT check_total_price CHECK (total_price = units * price_per_unit)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
