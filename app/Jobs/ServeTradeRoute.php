<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\TradeSymbols;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class ServeTradeRoute extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    private const MIN_PROFIT = 1.7;

    private const MIN_PROFIT_PER_FLIGHT = 50_000;

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

        if ($this->ship->cargo_is_empty) {
            dump("{$this->ship->symbol} cargo is empty");
            if ($this->ship->waypoint_symbol === $this->origin) {
                UpdateOrRemovePotentialTradeRoutesAction::run();
                /** @var PotentialTradeRoute */
                $tradeRoute = PotentialTradeRoute::firstWhere([
                    'trade_symbol' => $this->tradedGood->value,
                    'origin' => $this->origin,
                    'destination' => $this->destination,
                ]);
                if (!$tradeRoute) {
                    dump("{$this->ship->symbol} trade route does not exist anymore");

                    return;
                }

                if ($tradeRoute->profit <= static::MIN_PROFIT && $tradeRoute->profit !== 0) {
                    dump("{$this->ship->symbol} trade route is not profitable enough");
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

                    return;
                }

                dump("{$this->ship->symbol} purchase cargo {$this->tradedGood->value}");
                $this->ship->purchaseCargo(
                    $this->tradedGood,
                    $this->ship->cargo_capacity > $tradeRoute->trade_volume_at_origin
                        ? $tradeRoute->trade_volume_at_origin
                        : $this->ship->cargo_capacity
                );
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
            $this->ship->sellCargo($this->tradedGood);
            dump("{$this->ship->symbol} fly to {$this->origin}");
            $this->flyToLocation($this->origin);

            return;
        }
        dump("{$this->ship->symbol} fly to {$this->destination}");
        $this->flyToLocation($this->destination);

        return;

        dump('did not match any conditions');
    }
}
