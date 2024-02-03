<?php

use App\Enums\WaypointModifierSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Enums\WaypointTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('waypoints', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyText('symbol');
            $table->enum('type', WaypointTypes::values());
            $table->foreignId('faction_id')->constrained();
            $table->smallInteger('x');
            $table->smallInteger('y');
            $table->tinyText('orbits')
                ->nullable();
        });

        Schema::create('waypoint_traits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', WaypointTraitSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
        });

        Schema::create('waypoint_waypoint_trait', function (Blueprint $table) {
            $table->foreignId('waypoint_id')->constrained();
            $table->foreignId('waypoint_trait_id')->constrained();
        });

        Schema::create('waypoint_modifiers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', WaypointModifierSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
        });

        Schema::create('waypoint_waypoint_modifier', function (Blueprint $table) {
            $table->foreignId('waypoint_id')->constrained();
            $table->foreignId('waypoint_modifier_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waypoint_waypoint_modifier');
        Schema::dropIfExists('waypoint_modifiers');
        Schema::dropIfExists('waypoint_waypoint_trait');
        Schema::dropIfExists('waypoint_traits');
        Schema::dropIfExists('waypoints');
    }
};
