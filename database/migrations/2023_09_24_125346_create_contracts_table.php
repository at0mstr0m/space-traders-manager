<?php

use App\Enums\ContractTypes;
use App\Enums\FactionSymbols;
use App\Enums\TradeSymbols;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('agent_id')->constrained();
            $table->tinyText('identification');
            $table->enum('faction_symbol', FactionSymbols::values());
            $table->enum('type', ContractTypes::values());
            $table->boolean('fulfilled');
            $table->dateTime('deadline');
            $table->dateTime('deadline_to_accept');
            $table->integer('payment_on_accepted');
            $table->integer('payment_on_fulfilled');
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('contract_id')->constrained();
            $table->enum('trade_symbol', TradeSymbols::values());
            $table->tinyText('destination_symbol');
            $table->integer('units_required');
            $table->integer('units_fulfilled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('contracts');
    }
};
