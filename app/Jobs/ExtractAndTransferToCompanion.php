<?php

namespace App\Jobs;

use App\Enums\WaypointTypes;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Cargo;
use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractAndTransferToCompanion implements ShouldQueue
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
        private string $companionSymbol,
        private ?string $extractionLocation = null,
        private ?Ship $ship = null,
    ) {
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();
        $this->initExtractionLocation();
        dump("{$this->ship->symbol} is extracting and transferring at {$this->extractionLocation}");

        if ($this->ship->is_in_transit || $this->ship->cooldown) {
            dump('ship is in transit or on cooldown');
            $this->selfDispatch()->delay($this->ship->cooldown);
        }

        $this->transferToCompanionShip();

        // fly to asteroid
        if ($this->ship->waypoint_symbol !== $this->extractionLocation) {
            dump("fly to asteroid at {$this->extractionLocation}");
            $this->flyToLocation($this->extractionLocation);

            return;
        }
        // extract resource
        dump('extract resources');
        $cooldown = $this->ship
            ->extractResources()
            ->cooldown;
        $this->selfDispatch()->delay($cooldown);
        $this->transferToCompanionShip();
    }

    private function initShip(): void
    {
        $this->ship ??= Ship::findBySymbol($this->shipSymbol);
        if (!$this->ship) {
            throw new \Exception("Ship not found: {$this->shipSymbol}");
        }
        $this->ship = $this->ship->refetch();
    }

    private function initExtractionLocation(): void
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($this->ship->waypoint_symbol);

        $this->extractionLocation ??= $this->api
            ->listWaypointsInSystem($systemSymbol, WaypointTypes::ENGINEERED_ASTEROID)
            ->first()
            ->symbol;
    }

    private function selfDispatch(): PendingDispatch
    {
        return static::dispatch(
            $this->shipSymbol,
            $this->companionSymbol,
            $this->extractionLocation,
            $this->ship,
        );
    }

    private function flyToLocation(string $waypointSymbol): void
    {
        dump("fly to {$waypointSymbol}");
        $cooldown = $this->ship
            ->refuel()
            ->navigateTo($waypointSymbol)
            ->cooldown;
        $this->selfDispatch()->delay($cooldown);
    }

    private function transferToCompanionShip(): void
    {
        if (!$this->ship->is_fully_loaded) {
            return;
        }

        /** @var Ship */
        $companionShip = Ship::findBySymbol($this->companionSymbol)->refetch();
        if (
            $companionShip->waypoint_symbol !== $this->ship->waypoint_symbol
            || $companionShip->is_in_transit
        ) {
            dump('companion ship is not at the same location or is in transit');

            return;
        }

        $companionShip->moveIntoOrbit();
        // move into orbit to enable transfer
        dump('ship is fully loaded');
        $this->ship->cargos->each(function (Cargo $cargo) {
            dump("transfer cargo {$cargo->symbol->value}");
            // todo: handle case that companion ship cannot cope with the cargo in a better way
            try {
                $this->ship->transferCargoTo(
                    $this->companionSymbol,
                    $cargo->symbol,
                );
            } catch (\Throwable $th) {
                dump('companion ship is fully loaded: ' . $th->getMessage());
                WaitAndSell::dispatch(
                    $this->companionSymbol,
                    $this->extractionLocation
                );

                return false; // break loop
            }
        });
    }
}
