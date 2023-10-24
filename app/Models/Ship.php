<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\UpdateShipAction;
use App\Enums\CrewRotations;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Enums\TradeSymbols;
use App\Helpers\SpaceTraders;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Ship extends Model
{
    use FindableBySymbol;

    protected $with = [
        'modules',
    ];

    protected $fillable = [
        'symbol',
        'role',
        'waypoint_symbol',
        'status',
        'flight_mode',
        'crew_current',
        'crew_capacity',
        'crew_required',
        'crew_rotation',
        'crew_morale',
        'crew_wages',
        'fuel_current',
        'fuel_capacity',
        'fuel_consumed',
        'cooldown',
        'frame_condition',
        'reactor_condition',
        'engine_condition',
        'cargo_capacity',
        'cargo_units',
        'faction_id',   // consider removal
        'frame_id',     // consider removal
        'reactor_id',   // consider removal
        'engine_id',    // consider removal
    ];

    protected $casts = [
        'symbol' => 'string',
        'role' => ShipRoles::class,
        'waypoint_symbol' => 'string',
        'status' => ShipNavStatus::class,
        'flight_mode' => FlightModes::class,
        'crew_current' => 'integer',
        'crew_capacity' => 'integer',
        'crew_required' => 'integer',
        'crew_rotation' => CrewRotations::class,
        'crew_morale' => 'integer',
        'crew_wages' => 'integer',
        'fuel_current' => 'integer',
        'fuel_capacity' => 'integer',
        'fuel_consumed' => 'integer',
        'cooldown' => 'integer',
        'frame_condition' => 'integer',
        'reactor_condition' => 'integer',
        'engine_condition' => 'integer',
        'cargo_capacity' => 'integer',
        'cargo_units' => 'integer',
    ];

    public function getIsDockedAttribute(): bool
    {
        return $this->status === ShipNavStatus::DOCKED;
    }

    public function getIsInOrbitAttribute(): bool
    {
        return $this->status === ShipNavStatus::IN_ORBIT;
    }

    public function getIsInTransitAttribute(): bool
    {
        return $this->status === ShipNavStatus::IN_TRANSIT;
    }

    public function getIsFullyLoadedAttribute(): bool
    {
        return $this->cargo_capacity === $this->cargo_units;
    }

    public function isLoadedWith(TradeSymbols $tradeSymbol): bool
    {
        return $this->cargos()
            ->where('symbol', $tradeSymbol->value)
            ->exists();
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class);
    }

    public function reactor(): BelongsTo
    {
        return $this->belongsTo(Reactor::class);
    }

    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->using(ShipModule::class)
            ->withPivot(['quantity']);
    }

    public function mounts(): BelongsToMany
    {
        return $this->belongsToMany(Mount::class)
            ->using(ShipMount::class)
            ->withPivot(['quantity']);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function refetch(): self
    {
        return UpdateShipAction::run(
            $this->useApi()->getShip($this->symbol),
            $this->agent
        );
    }

    public function moveIntoOrbit(): self
    {
        $this->useApi()
            ->orbitShip($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function navigateTo(string $waypointSymbol): self
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->navigateShip($this->symbol, $waypointSymbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function dock(): self
    {
        $this->useApi()
            ->dockShip($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function refuel(): self
    {
        $this->dock()
            ->useApi()
            ->refuelShip($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function extractResources(): self
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->extractResources($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function purchaseCargo(TradeSymbols $tradeSymbol, int $units): self
    {
        $this->dock()
            ->useApi()
            ->purchaseCargo($this->symbol, $tradeSymbol, $units)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function sellCargo(TradeSymbols $tradeSymbol, int $units = 0): self
    {
        $units = $units ?: $this->cargos()->firstWhere('symbol', $tradeSymbol)->units;

        $this->dock()
            ->useApi()
            ->sellCargo($this->symbol, $tradeSymbol, $units)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function jettisonCargo(TradeSymbols $tradeSymbol, int $units = 0): self
    {
        // jettison all cargo of this type if no units specified
        $units = $units ?: $this->cargos()->firstWhere('symbol', $tradeSymbol)->units;

        $this->useApi()
            ->jettisonCargo($this->symbol, $tradeSymbol, $units)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function fetchCargo(): self
    {
        $this->useApi()
            ->getShipCargo($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function deliverCargoToContract(string $contractId, TradeSymbols $tradeSymbol, int $units): self
    {
        $this->useApi()
            ->deliverCargoToContract(
                $contractId,
                $this->symbol,
                $tradeSymbol,
                $units
            )->updateShip($this)
            ->save();

        return $this;
    }

    public function getMarketplacesForCargos(): Collection
    {
        return $this->useApi()
            ->listMarketplacesInShipForShipCargos($this);
    }

    private function useApi(): SpaceTraders
    {
        return app(SpaceTraders::class);
    }
}
