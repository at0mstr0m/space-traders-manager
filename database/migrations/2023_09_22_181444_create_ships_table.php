<?php

declare(strict_types=1);

use App\Enums\CrewRotations;
use App\Enums\DepositSymbols;
use App\Enums\EngineSymbols;
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
        Schema::create('frames', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', FrameSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
            $table->smallInteger('module_slots');
            $table->smallInteger('mounting_points');
            $table->integer('fuel_capacity');
            $table->smallInteger('required_power');     // requirements.power
            $table->smallInteger('required_crew');      // requirements.crew
        });

        Schema::create('reactors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', ReactorSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
            $table->integer('power_output');
            $table->smallInteger('required_crew');      // requirements.crew
        });

        Schema::create('engines', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', EngineSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
            $table->integer('speed');
            $table->smallInteger('required_power');     // requirements.power
            $table->smallInteger('required_crew');      // requirements.crew
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', ModuleSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
            $table->smallInteger('capacity')->nullable();
            $table->smallInteger('range')->nullable();
            $table->smallInteger('required_power');     // requirements.power
            $table->smallInteger('required_crew');      // requirements.crew
            $table->smallInteger('required_slots');      // requirements.slots
        });

        Schema::create('mounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', MountSymbols::values())
                ->unique();
            $table->tinyText('name');
            $table->text('description');
            $table->smallInteger('strength')->nullable();
            $table->smallInteger('required_power');     // requirements.power
            $table->smallInteger('required_crew');      // requirements.crew
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('symbol', DepositSymbols::values())
                ->unique();
        });

        Schema::create('deposit_mount', function (Blueprint $table) {
            $table->foreignId('mount_id')->constrained()->onDelete('cascade');
            $table->foreignId('deposit_id')->constrained()->onDelete('cascade');
            $table->unique(['mount_id', 'deposit_id']);
        });

        Schema::create('ships', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('agent_id')->constrained();

            // registration
            $table->foreignId('faction_id')->constrained();
            $table->string('symbol')
                ->unique();
            $table->enum('role', ShipRoles::values());          // registration.role

            // nav
            $table->tinyText('waypoint_symbol');                // nav.waypointSymbol
            $table->enum('status', ShipNavStatus::values());    // nav.status
            $table->enum('flight_mode', FlightModes::values()); // nav.flightMode

            // crew
            $table->smallInteger('crew_current');       // crew.current
            $table->smallInteger('crew_capacity');      // crew.capacity
            $table->smallInteger('crew_required');      // crew.required
            $table->enum('crew_rotation', CrewRotations::values()); // crew.rotation
            $table->tinyInteger('crew_morale');         // crew.morale
            $table->integer('crew_wages');              // crew.wages

            // fuel
            $table->integer('fuel_current');            // fuel.current
            $table->integer('fuel_capacity');           // fuel.capacity
            $table->integer('fuel_consumed');           // fuel.consumed
            $table->integer('cooldown');                // cooldown.remainingSeconds
            $table->foreignId('frame_id')->constrained();
            $table->float('frame_condition');           // frame.condition
            $table->float('frame_integrity');           // frame.integrity
            $table->foreignId('reactor_id')->constrained();
            $table->float('reactor_condition');         // reactor.condition
            $table->float('reactor_integrity');         // reactor.integrity
            $table->foreignId('engine_id')->constrained();
            $table->float('engine_condition');          // engine.condition
            $table->float('engine_integrity');          // engine.integrity

            // cargo
            $table->smallInteger('cargo_capacity');     // cargo.capacity
            $table->integer('cargo_units');             // cargo.units
        });

        DB::statement("ALTER TABLE ships ADD CONSTRAINT check_crew_morale_range CHECK (crew_morale >= 0 AND crew_morale <= 100)");

        foreach ([
            'frame_condition',
            'frame_integrity',
            'reactor_condition',
            'reactor_integrity',
            'engine_condition',
            'engine_integrity',
        ] as $column) {
            DB::statement("ALTER TABLE ships ADD CONSTRAINT check_{$column}_range CHECK ({$column} >= 0.0 AND {$column} <= 1.0)");
        }

        Schema::create('module_ship', function (Blueprint $table) {
            $table->foreignId('ship_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('module_id')
                ->constrained()
                ->onDelete('cascade');
            $table->smallInteger('quantity');
            $table->unique(['ship_id', 'module_id']);
        });

        Schema::create('mount_ship', function (Blueprint $table) {
            $table->foreignId('ship_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('mount_id')
                ->constrained()
                ->onDelete('cascade');
            $table->smallInteger('quantity');
            $table->unique(['ship_id', 'mount_id']);
        });

        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyText('symbol');
            $table->tinyText('name');
            $table->text('description');
            $table->foreignId('ship_id')
                ->constrained()
                ->onDelete('cascade');
            $table->integer('units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mount_ship');
        Schema::dropIfExists('module_ship');
        Schema::dropIfExists('cargos');
        Schema::dropIfExists('ships');
        Schema::dropIfExists('deposit_mount');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('mounts');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('engines');
        Schema::dropIfExists('reactors');
        Schema::dropIfExists('frames');
    }
};
