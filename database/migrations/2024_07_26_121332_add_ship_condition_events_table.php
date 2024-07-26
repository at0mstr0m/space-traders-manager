<?php

use App\Enums\ShipConditionEventComponents;
use App\Enums\ShipConditionEvents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ship_condition_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('ship_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('symbol', ShipConditionEvents::values());
            $table->enum('component', ShipConditionEventComponents::values());
            $table->string('name');
            $table->string('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_condition_events');
    }
};
