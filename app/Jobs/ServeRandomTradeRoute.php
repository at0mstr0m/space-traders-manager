<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Enums\TradeSymbols;
use App\Helpers\LocationHelper;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ServeRandomTradeRoute extends ShipJob implements ShouldBeUniqueUntilProcessing
{
    private const MIN_PROFIT = 1.7;

    // could change between executions
    private ?PotentialTradeRoute $tradeRoute = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $shipSymbol,
        private null|PotentialTradeRoute|string $origin = null,
        private ?string $destination = null,
        private null|string|TradeSymbols $tradedGood = null,
        protected ?Ship $ship = null,
    ) {
        if (is_string($origin) || is_null($origin)) {
            $this->constructorArguments = func_get_args();

            return;
        }

        $tradedGood = TradeSymbols::fromName($origin->trade_symbol);
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
        dump("{$this->ship->symbol} serving random trade route, currently located at {$this->ship->waypoint_symbol}");
        if (!$this->origin) {
            dump("{$this->ship->symbol} has no route yet, choosing a new one.");
            $this->chooseNewRoute();

            return;
        }

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
                    // forget the current trade route
                    Cache::tags([PotentialTradeRoute::CACHE_TAG])->forget($this->destination . ':' . $this->origin . ':' . $this->tradedGood->value);
                    $this->chooseNewRoute();

                    return;
                }

                dump("{$this->ship->symbol} purchase cargo {$this->tradedGood->value}");

                while (!$this->ship->refresh()->is_fully_loaded) {
                    $this->ship->purchaseCargo(
                        $this->tradedGood,
                        min($this->tradeRoute->trade_volume_at_origin, $this->ship->available_cargo_capacity)
                    );
                }
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
                // forget the current trade route
                Cache::tags([PotentialTradeRoute::CACHE_TAG])->forget($this->destination . ':' . $this->origin . ':' . $this->tradedGood->value);
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
        $possibleNewRoutes = PotentialTradeRoute::getQuery()
            ->select([
                'id',
                'origin',
                'destination',
                'trade_symbol',
                'profit',
                'profit_per_flight',
                'distance',
            ])
            ->where([
                ['profit', '>', 1.7],
                ['distance', '<=', $this->ship->fuel_capacity],
            ])
            ->get()
            ->map(function (object $route) {
                $route = (array) $route;
                $route['current_distance_to_origin'] = LocationHelper::distance(
                    $route['origin'],
                    $this->ship->waypoint_symbol
                );

                return $route;
            })
            ->filter(fn (array $route) => $route['current_distance_to_origin'] <= $this->ship->fuel_capacity)
            ->shuffle();

        $count = $possibleNewRoutes->count();

        if ($count === 0) {
            dump("{$this->ship->symbol} no new trade routes found");

            return;
        }

        for ($_ = 0; $_ < $count; ++$_) {
            $newRoute = $possibleNewRoutes->pop();
            $cacheKey = implode(':', array_values(Arr::only($newRoute, ['origin', 'destination', 'trade_symbol'])));
            if (Cache::tags([PotentialTradeRoute::CACHE_TAG])->has($cacheKey)) {
                continue;
            }
            Cache::tags([PotentialTradeRoute::CACHE_TAG])->put($cacheKey, true);

            dump("{$this->ship->symbol} new trade route {$newRoute['origin']} -> {$newRoute['destination']} with {$newRoute['trade_symbol']} profit per flight {$newRoute['profit_per_flight']}");

            static::dispatch(
                $this->ship->symbol,
                PotentialTradeRoute::find($newRoute['id']),
            )->delay(1);

            return;
        }

        dump("{$this->ship->symbol} no unserved trade route found!");
    }
}
