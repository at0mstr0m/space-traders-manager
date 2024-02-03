<?php

declare(strict_types=1);

use App\Enums\CrewRotations;
use App\Enums\DepositSymbols;
use App\Enums\EngineSymbols;
use App\Enums\FactionSymbols;
use App\Enums\FactionTraits;
use App\Enums\FlightModes;
use App\Enums\FrameSymbols;
use App\Enums\ModuleSymbols;
use App\Enums\MountSymbols;
use App\Enums\ReactorSymbols;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', FactionSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
            $table->tinyText('headquarters'); // nav.waypointSymbol
            $table->boolean('is_recruiting');
        });

        Schema::create('faction_traits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', FactionTraits::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
        });

        Schema::create('faction_faction_trait', function (Blueprint $table) {
            $table->foreignId('faction_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('faction_trait_id')
                ->constrained()
                ->onDelete('cascade');
            $table->unique(['faction_id', 'faction_trait_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faction_faction_trait');
        Schema::dropIfExists('faction_traits');
        Schema::dropIfExists('factions');
    }
};
