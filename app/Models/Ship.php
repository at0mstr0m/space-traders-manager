<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\UpdateShipAction;
use App\Actions\UpdateSurveyAction;
use App\Data\SurveyData;
use App\Enums\CrewRotations;
use App\Enums\FlightModes;
use App\Enums\MountSymbols;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Enums\TradeSymbols;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\Ship.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $agent_id
 * @property int $faction_id
 * @property string $symbol
 * @property ShipRoles $role
 * @property string $waypoint_symbol
 * @property ShipNavStatus $status
 * @property FlightModes $flight_mode
 * @property int $crew_current
 * @property int $crew_capacity
 * @property int $crew_required
 * @property CrewRotations $crew_rotation
 * @property int $crew_morale
 * @property int $crew_wages
 * @property int $fuel_current
 * @property int $fuel_capacity
 * @property int $fuel_consumed
 * @property int $cooldown
 * @property int $frame_id
 * @property float $frame_condition
 * @property int $reactor_id
 * @property float $reactor_condition
 * @property int $engine_id
 * @property float $engine_condition
 * @property int $cargo_capacity
 * @property int $cargo_units
 * @property int|null $task_id
 * @property-read Agent $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cargo> $cargos
 * @property-read int|null $cargos_count
 * @property-read Engine $engine
 * @property-read Faction $faction
 * @property-read Frame $frame
 * @property-read int $available_cargo_capacity
 * @property-read bool $cargo_is_empty
 * @property-read bool $is_docked
 * @property-read bool $is_fully_loaded
 * @property-read bool $is_in_orbit
 * @property-read bool $is_in_transit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Module> $modules
 * @property-read int|null $modules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mount> $mounts
 * @property-read int|null $mounts_count
 * @property-read Reactor $reactor
 * @property-read Task|null $task
 *
 * @method static Builder|Ship newModelQuery()
 * @method static Builder|Ship newQuery()
 * @method static Builder|Ship onlyHaulers()
 * @method static Builder|Ship onlyMiners()
 * @method static Builder|Ship onlySiphoners()
 * @method static Builder|Ship query()
 * @method static Builder|Ship whereAgentId($value)
 * @method static Builder|Ship whereCargoCapacity($value)
 * @method static Builder|Ship whereCargoUnits($value)
 * @method static Builder|Ship whereCooldown($value)
 * @method static Builder|Ship whereCreatedAt($value)
 * @method static Builder|Ship whereCrewCapacity($value)
 * @method static Builder|Ship whereCrewCurrent($value)
 * @method static Builder|Ship whereCrewMorale($value)
 * @method static Builder|Ship whereCrewRequired($value)
 * @method static Builder|Ship whereCrewRotation($value)
 * @method static Builder|Ship whereCrewWages($value)
 * @method static Builder|Ship whereEngineCondition($value)
 * @method static Builder|Ship whereEngineId($value)
 * @method static Builder|Ship whereFactionId($value)
 * @method static Builder|Ship whereFlightMode($value)
 * @method static Builder|Ship whereFrameCondition($value)
 * @method static Builder|Ship whereFrameId($value)
 * @method static Builder|Ship whereFuelCapacity($value)
 * @method static Builder|Ship whereFuelConsumed($value)
 * @method static Builder|Ship whereFuelCurrent($value)
 * @method static Builder|Ship whereId($value)
 * @method static Builder|Ship whereReactorCondition($value)
 * @method static Builder|Ship whereReactorId($value)
 * @method static Builder|Ship whereRole($value)
 * @method static Builder|Ship whereStatus($value)
 * @method static Builder|Ship whereSymbol($value)
 * @method static Builder|Ship whereTaskId($value)
 * @method static Builder|Ship whereUpdatedAt($value)
 * @method static Builder|Ship whereWaypointSymbol($value)
 *
 * @property-read Waypoint|null $waypoint
 *
 * @mixin \Eloquent
 */
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
        'frame_condition' => 'float',
        'frame_integrity' => 'float',
        'reactor_condition' => 'float',
        'reactor_integrity' => 'float',
        'engine_condition' => 'float',
        'engine_integrity' => 'float',
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

    public function isLoadedWith(string|TradeSymbols $tradeSymbol): bool
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

    public function waypoint(): HasOne
    {
        return $this->hasOne(Waypoint::class, 'symbol', 'waypoint_symbol');
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

    public function extractResources(): static
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->extractResources($this->symbol)
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function extractResourcesWithSurvey(Survey $survey): static
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->extractResourcesWithSurvey(
                $this->symbol,
                json_decode($survey->raw_response, true)
            );

        return $this;
    }

    public function siphonResources(): static
    {
        $this->moveIntoOrbit()
            ->useApi()
            ->siphonResources($this->symbol)
            ->updateShip($this)
            ->save();

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
     *
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
        if ($receivingShip instanceof static && $tradeSymbol instanceof Cargo) {
            return $this->transferCargoTo(
                $receivingShip,
                $tradeSymbol->symbol,
                $units ?: min($tradeSymbol->units, $receivingShip->available_cargo_capacity)
            );
        }

        /** @var Ship */
        $receivingShip = is_string($receivingShip)
            ? Ship::findBySymbol($receivingShip)
            : $receivingShip;

        /** @var TradeSymbols */
        $_tradeSymbol = TradeSymbols::fromName($tradeSymbol);

        /** @var int */
        $units = $units ?: min(
            $receivingShip->available_cargo_capacity,
            $this->cargos()
                ->firstWhere('symbol', $_tradeSymbol)
                ->units
        );

        $this->useApi()
            ->transferCargo(
                $this->symbol,
                $receivingShip->symbol,
                $_tradeSymbol,
                $units
            )->updateShip($this)
            ->save();

        $receivingShip->update(['cargo_units' => $receivingShip->cargo_units + $units]);

        return $this;
    }

    public function setFlightMode(FlightModes|string $flightMode): static
    {
        $this->useApi()
            ->patchShipNav($this->symbol, FlightModes::fromName($flightMode))
            ->updateShip($this)
            ->save();

        return $this;
    }

    public function supplyCargoToConstructionSite(null|string|TradeSymbols $tradeSymbol = null, int $units = 0): static
    {
        $tradeSymbol = is_null($tradeSymbol) ? $this->cargos()->first()->symbol : $tradeSymbol;
        $tradeSymbol = is_string($tradeSymbol) ? TradeSymbols::fromName($tradeSymbol) : $tradeSymbol;
        // supply all available cargo of this type if no units specified
        $units = $units ?: $this->cargos()->firstWhere('symbol', $tradeSymbol)->units;

        $this->dock()
            ->useApi()
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

    public function scopeOnlyMiners(Builder $query): Builder
    {
        return $query->where('role', ShipRoles::EXCAVATOR)
            ->whereHas('mounts', fn (Builder $query) => $query->whereIn(
                'symbol',
                MountSymbols::miningLasers()
            ));
    }

    public function scopeOnlySiphoners(Builder $query): Builder
    {
        return $query->where('role', ShipRoles::EXCAVATOR)
            ->whereHas('mounts', fn (Builder $query) => $query->whereIn(
                'symbol',
                MountSymbols::gasSiphons()
            ));
    }

    public function scopeOnlyHaulers(Builder $query): Builder
    {
        return $query->where('role', ShipRoles::HAULER);
    }

    public function canRefuelAtCurrentLocation(): bool
    {
        return Waypoint::canRefuel()
            ->where('symbol', $this->waypoint_symbol)
            ->exists();
    }

    public function closestRefuelingStation(): string
    {
        if ($this->canRefuelAtCurrentLocation()) {
            return $this->waypoint_symbol;
        }

        return data_get(
            Waypoint::canRefuel()
                ->get()
                ->map(fn (Waypoint $waypoint) => [
                    'waypoint_symbol' => $waypoint->symbol,
                    'distance' => $this->distanceTo($waypoint->symbol),
                ])
                ->sortBy('distance')
                ->first(),
            'waypoint_symbol',
        );
    }

    private function useApi(): SpaceTraders
    {
        return app(SpaceTraders::class);
    }
}
