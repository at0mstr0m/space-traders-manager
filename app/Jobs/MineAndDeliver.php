<?php

namespace App\Jobs;

use App\Data\MarketData;
use App\Enums\DepositSymbols;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTypes;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Contract;
use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MineAndDeliver implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SpaceTraders $api;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $shipSymbol,
        private string $contractIdentification,
        private ?string $extractionLocation = null,
        private ?Ship $ship = null,
        private ?Contract $contract = null,
    ) {
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();
        $this->initContract();
        $this->initExtractionLocation();

        dump($this->extractionLocation);

        // get first unfulfilled delivery
        $unfulfilledDelivery = $this->contract
            ->deliveries()
            ->whereColumn('units_required', '>', 'units_fulfilled')
            ->first();

        // nothing to deliver => fulfill contract
        if (!$unfulfilledDelivery) {
            $this->contract->fulfill();
        }

        // find out what to mine
        /** @var TradeSymbols */
        $resource = $unfulfilledDelivery->trade_symbol;

        dump($resource->value);
        if (!DepositSymbols::isValid($resource->value)) {
            throw new \Exception("{$resource->value} is not an extractable resource.");
        }

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            $this->selfDispatch()->delay($this->ship->cooldown);
        }

        if ($this->ship->fuel_current < ($this->ship->fuel_capacity / 3)) {
            $this->ship->refuel();
        }

        if (!$this->ship->is_fully_loaded) {
            // fly to asteroid field
            if ($this->ship->waypoint_symbol !== $this->extractionLocation) {
                dump('fly to asteroid field');
                $cooldown = $this->ship
                    ->navigateTo($this->extractionLocation)
                    ->cooldown;
                $this->selfDispatch()->delay($cooldown);

                return;
            }
            // extract resource
            dump('extract resource');
            $cooldown = $this->ship
                ->extractResources()
                ->cooldown;
            $this->selfDispatch()->delay($cooldown);

            return;
        }

        $deliveryDestination = $unfulfilledDelivery->destination_symbol;
        dump($deliveryDestination);
        // fly to delivery destination
        if ($this->ship->waypoint_symbol !== $deliveryDestination) {
            dump('fly to delivery destination');
            $cooldown = $this->ship
                ->navigateTo($deliveryDestination)
                ->cooldown;
            $this->selfDispatch()->delay($cooldown);

            return;
        }

        // check if found resource
        if ($this->ship->isLoadedWith($resource)) {
            dump('check if found resource');
            $this->ship->deliverCargoToContract(
                $this->contract->identification,
                $resource,
                $this->ship->cargos()->firstWhere('symbol', $resource->value)->units
            );
        }

        // sell all cargo
        dump('sell all cargo');
        $this->ship->getMarketplacesForCargos()
            ->each(function (?MarketData $cargo, string $tradeSymbol) {
                $tradeSymbol = TradeSymbols::fromName($tradeSymbol);
                if ($cargo && $this->ship->waypoint_symbol === $cargo->symbol) {
                    $this->ship->sellCargo($tradeSymbol);
                } else {
                    // jettison Cargo that cannot be sold in this system
                    $this->ship->jettisonCargo($tradeSymbol);
                }
            });

        dump('refuel');
        $this->ship->refuel();
        $this->selfDispatch();
    }

    private function initShip(): void
    {
        $this->ship ??= Ship::findBySymbol($this->shipSymbol);
        if (!$this->ship) {
            throw new \Exception("Ship not found: {$this->shipSymbol}");
        }
        $this->ship = $this->ship->refetch();
    }

    private function initContract(): void
    {
        $this->contract ??= Contract::firstWhere('identification', $this->contractIdentification);
        if (!$this->contract) {
            throw new \Exception("Contract not found: {$this->contractIdentification}");
        }
        $this->contract = $this->contract->refetch();
    }

    private function initExtractionLocation(): void
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($this->ship->waypoint_symbol);

        $this->extractionLocation ??= $this->api
            ->listWaypointsInSystemOfType($systemSymbol, WaypointTypes::ASTEROID_FIELD)
            ->first()
            ->symbol;
    }

    private function selfDispatch(): PendingDispatch
    {
        return self::dispatch(
            $this->shipSymbol,
            $this->contractIdentification,
            $this->extractionLocation,
            $this->ship,
            $this->contract,
        );
    }
}
