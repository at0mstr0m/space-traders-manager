<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\UpdateShipAction;
use App\Actions\UpdateSurveyAction;
use App\Data\SurveyData;
use App\Enums\CrewRotations;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Enums\TradeSymbols;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Traits\FindableBySymbol;
use Illuminate\Contracts\Container\BindingResolutionException;
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function getCargoIsEmptyAttribute(): bool
    {
        return $this->cargo_units === 0;
    }

    public function getAvailableCargoCapacityAttribute(): int
    {
        return $this->cargo_capacity - $this->cargo_units;
    }

    public function isLoadedWith(TradeSymbols|string $tradeSymbol): bool
    {
        return $this->cargos()
            ->where('symbol', TradeSymbols::fromName($tradeSymbol))
            ->exists();
    }

    public function hasEnoughCargoCapacityFor(Cargo|int $units): bool
    {
        $units = is_int($units) ? $units : $units->units;

        return $this->available_cargo_capacity >= $units;
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

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
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

    public function refetch(): static
    {
        return UpdateShipAction::run(
            $this->useApi()->getShip($this->symbol),
            $this->agent
        );
    }

    public function moveIntoOrbit(): static
    {
        $this->useApi()
            ->orbitShip($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function navigateTo(string $waypointSymbol): static
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->navigateShip($this->symbol, $waypointSymbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function dock(): static
    {
        $this->useApi()
            ->dockShip($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function survey(): static
    {
        $createSurveyData = $this->moveIntoOrbit()
            ->useApi()
            ->createSurvey($this->symbol);
        $createSurveyData->updateShip($this)->save();
        $createSurveyData->surveys
            ->each(fn (SurveyData $surveyData) => UpdateSurveyAction::run($surveyData, $this->agent));

        return $this;
    }

    public function extractResourcesWithSurvey(Survey $survey): static
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->extractResourcesWithSurvey(
                $this->symbol,
                $survey->toRequestableObject()
            );

        return $this;
    }

    public function refuel(): static
    {
        $this->dock()
            ->useApi()
            ->refuelShip($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function extractResources(): static
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->extractResources($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function purchaseCargo(string|TradeSymbols $tradeSymbol, int $units = 0): static
    {
        $tradeSymbol = is_string($tradeSymbol) ? TradeSymbols::fromName($tradeSymbol) : $tradeSymbol;
        // purchase as much cargo of this type as possible if not specified
        $units = $units ?: $this->available_cargo_capacity;

        if ($units === 0 || !$this->hasEnoughCargoCapacityFor($units)) {
            throw new \Exception('Not enough cargo capacity available', 1);
        }

        $this->dock()
            ->useApi()
            ->purchaseCargo($this->symbol, $tradeSymbol, $units)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function sellCargo(string|TradeSymbols $tradeSymbol, int $units = 0): static
    {
        $tradeSymbol = TradeSymbols::fromName($tradeSymbol);
        // sell all cargo of this type if no units specified
        $units = $units ?: $this->cargos()->firstWhere('symbol', $tradeSymbol)->units;

        $this->dock()
            ->useApi()
            ->sellCargo($this->symbol, $tradeSymbol, $units)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function jettisonCargo(string|TradeSymbols $tradeSymbol, int $units = 0): static
    {
        $tradeSymbol = TradeSymbols::fromName($tradeSymbol);
        // jettison all cargo of this type if no units specified
        $units = $units ?: $this->cargos()->firstWhere('symbol', $tradeSymbol)->units;

        $this->useApi()
            ->jettisonCargo($this->symbol, $tradeSymbol, $units)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function fetchCargo(): static
    {
        $this->useApi()
            ->getShipCargo($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function deliverCargoToContract(
        string $contractId,
        string|TradeSymbols $tradeSymbol,
        int $units
    ): static {
        $tradeSymbol = is_string($tradeSymbol) ? TradeSymbols::fromName($tradeSymbol) : $tradeSymbol;
        $this->dock()
            ->useApi()
            ->deliverCargoToContract(
                $contractId,
                $this->symbol,
                $tradeSymbol,
                $units
            )->updateShip($this)
            ->save();

        return $this;
    }

    /**
     * @template TWaypointSymbol string
     * @return Collection<TWaypointSymbol, MarketData>
     */
    public function getMarketplacesForCargos(): Collection
    {
        return $this->useApi()
            ->listMarketplacesInSystemForShipCargos($this);
    }

    public function transferCargoTo(
        self|string $receivingShip,
        Cargo|string|TradeSymbols $tradeSymbol,
        int $units = 0
    ): static {
        /** @var Ship */
        $receivingShip = is_string($receivingShip)
            ? Ship::findBySymbol($receivingShip)->symbol
            : $receivingShip;
        $_tradeSymbol = is_string($tradeSymbol)
            ? TradeSymbols::fromName($tradeSymbol)
            : (
                $tradeSymbol instanceof Cargo
                    ? $tradeSymbol->symbol
                    : $tradeSymbol
            );
        $units = $units ?: min(
            $receivingShip->available_cargo_capacity,
            $tradeSymbol instanceof Cargo
                ? $tradeSymbol->units
                : $this->cargos()
                    ->firstWhere('symbol', $_tradeSymbol)
                    ->units,
        );

        $this->useApi()
            ->transferCargo(
                $this->symbol,
                $receivingShip->symbol,
                $_tradeSymbol,
                $units
            )->updateShip($this)
            ->save();

        return $this;
    }

    public function setFlightMode(FlightModes $flightMode): static
    {
        $this->useApi()
            ->patchShipNav($this->symbol, $flightMode)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function supplyCargoToConstructionSite(string|TradeSymbols $tradeSymbol, int $units = 0): static
    {
        $tradeSymbol = is_string($tradeSymbol) ? TradeSymbols::fromName($tradeSymbol) : $tradeSymbol;
        // supply all available cargo of this type if no units specified
        $units = $units ?: $this->cargos()->firstWhere('symbol', $tradeSymbol)->units;

        $this->useApi()
            ->supplyConstructionSite(
                $this->waypoint_symbol,
                $this->symbol,
                $tradeSymbol,
                $units
            )
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function distanceTo(string $waypointSymbol): int
    {
        return LocationHelper::distance(
            $this->waypoint_symbol,
            $waypointSymbol
        );
    }

    private function useApi(): SpaceTraders
    {
        return app(SpaceTraders::class);
    }
}
