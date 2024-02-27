<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\TradeSymbols;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class ServeBestTradeRoute extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    private const MIN_PROFIT = 1.7;

    private const MIN_PROFIT_PER_FLIGHT = 50_000;

    // could change between executions
    private ?PotentialTradeRoute $tradeRoute = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $shipSymbol,
        private PotentialTradeRoute|string $origin,
        private ?string $destination = null,
        private ?TradeSymbols $tradedGood = null,
        protected ?Ship $ship = null,
    ) {
        if (is_string($origin)) {
            $this->constructorArguments = func_get_args();

            return;
        }
        static::__construct(
            $shipSymbol,
            $origin->origin,
            $origin->destination,
            $origin->trade_symbol,
        );
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return static::class . ':' . $this->shipSymbol;
    }

    /**
     * Execute the job.
     */
    public function handleShip(): void
    {
        dump("{$this->ship->symbol} serving trade route {$this->origin} -> {$this->destination} with {$this->tradedGood->value}");

        $this->initTradeRoute();

        if ($this->ship->cargo_is_empty) {
            dump("{$this->ship->symbol} cargo is empty");
            if ($this->ship->waypoint_symbol === $this->origin) {
                if (!$this->tradeRoute) {
                    dump("{$this->ship->symbol} trade route does not exist anymore");

                    return;
                }

                if ($this->tradeRoute->profit <= static::MIN_PROFIT && $this->tradeRoute->profit !== 0) {
                    dump("{$this->ship->symbol} trade route is not profitable enough");
                    $this->chooseNewRoute();

                    return;
                }

                dump("{$this->ship->symbol} purchase cargo {$this->tradedGood->value}");

                // while (!$this->ship->refresh()->is_fully_loaded) {
                    $this->ship->purchaseCargo(
                        $this->tradedGood,
                        min($this->tradeRoute->trade_volume_at_origin, $this->ship->available_cargo_capacity)
                    );
                // }
                dump("{$this->ship->symbol} fly to {$this->destination}");
                $this->flyToLocation($this->destination);

                return;
            }
            dump("{$this->ship->symbol} fly to {$this->origin}");
            $this->flyToLocation($this->origin);

            return;
        }
        dump("{$this->ship->symbol} cargo is not empty");
        if ($this->ship->waypoint_symbol === $this->destination) {
            dump("{$this->ship->symbol} sell cargo {$this->tradedGood->value}");
            while (!$this->ship->refresh()->cargo_is_empty) {
                $cargo = $this->ship->cargos()->firstWhere('symbol', $this->tradedGood);
                $this->ship->sellCargo(
                    $this->tradedGood,
                    min($this->tradeRoute->trade_volume_at_destination, $cargo->units)
                );
            }

            if ($this->tradeRoute->profit <= static::MIN_PROFIT && $this->tradeRoute->profit !== 0) {
                dump("{$this->ship->symbol} trade route is not profitable enough");
                $this->chooseNewRoute();

                return;
            }

            dump("{$this->ship->symbol} fly to {$this->origin}");
            $this->flyToLocation($this->origin);

            return;
        }
        dump("{$this->ship->symbol} fly to {$this->destination}");
        $this->flyToLocation($this->destination);

        return;

        dump('did not match any conditions');
    }

    private function initTradeRoute(): void
    {
        UpdateOrRemovePotentialTradeRoutesAction::run();
        /** @var PotentialTradeRoute */
        $this->tradeRoute = PotentialTradeRoute::firstWhere([
            'trade_symbol' => $this->tradedGood->value,
            'origin' => $this->origin,
            'destination' => $this->destination,
        ]);
    }

    private function chooseNewRoute(): void
    {
        $newRoute = PotentialTradeRoute::orderByDesc('profit_per_flight')
            ->firstWhere([
                ['profit', '>', static::MIN_PROFIT],
                ['profit_per_flight', '>', static::MIN_PROFIT_PER_FLIGHT],
                ['distance', '<', 300],
            ]);

        if (!$newRoute) {
            dump("{$this->ship->symbol} no new trade route found");

            return;
        }

        dump(
            PotentialTradeRoute::orderByDesc('profit_per_flight')
                ->where([
                    ['profit', '>', static::MIN_PROFIT],
                    ['profit_per_flight', '>', static::MIN_PROFIT_PER_FLIGHT],
                    ['distance', '<', 300],
                ])
                ->get()
                ->map(fn (PotentialTradeRoute $potentialTradeRoute) => $potentialTradeRoute->only(['origin', 'destination', 'trade_symbol', 'profit_per_flight', 'profit']))
        );

        dump("{$this->ship->symbol} new trade route {$newRoute->origin} -> {$newRoute->destination} with {$newRoute->trade_symbol->value} profit per flight {$newRoute->profit_per_flight}");

        static::dispatch(
            $this->ship->symbol,
            $newRoute->origin,
            $newRoute->destination,
            $newRoute->trade_symbol
        )->delay(1);

        dump("{$this->ship->symbol} will serve new route now.");
    }
}
