<?php

declare(strict_types=1);

namespace App\Enums;

use App\Jobs\DistributeFuelToMarkets;
use App\Jobs\FulfillProcurement;
use App\Jobs\MultipleMineAndPassOn;
use App\Jobs\MultipleSiphonAndPassOn;
use App\Jobs\ServeBestTradeRoute;
use App\Jobs\ServeHighestProfitTradeRoute;
use App\Jobs\ServeRandomTradeRoute;
use App\Jobs\SupplyConstructionSite;
use App\Jobs\WaitAndSell;
use App\Traits\EnumUtils;

enum TaskTypes: string
{
    use EnumUtils;

    case COLLECTIVE_MINING = 'COLLECTIVE_MINING';
    case COLLECTIVE_SIPHONING = 'COLLECTIVE_SIPHONING';
    case SUPPORT_COLLECTIVE_MINERS = 'SUPPORT_COLLECTIVE_MINERS';
    case SERVE_TRADE_ROUTE = 'SERVE_TRADE_ROUTE';
    case SERVE_BEST_TRADE_ROUTE = 'SERVE_BEST_TRADE_ROUTE';
    case SERVE_HIGHEST_PROFIT_TRADE_ROUTE = 'SERVE_HIGHEST_PROFIT_TRADE_ROUTE';
    case SUPPLY_CONSTRUCTION_SITE = 'SUPPLY_CONSTRUCTION_SITE';
    case DISTRIBUTE_FUEL = 'DISTRIBUTE_FUEL';
    case FULFILL_PROCUREMENT = 'FULFILL_PROCUREMENT';

    public function getCorrespondingJob(): string
    {
        return match ($this) {
            self::COLLECTIVE_MINING => MultipleMineAndPassOn::class,
            self::COLLECTIVE_SIPHONING => MultipleSiphonAndPassOn::class,
            self::SUPPORT_COLLECTIVE_MINERS => WaitAndSell::class,
            self::SERVE_TRADE_ROUTE => ServeRandomTradeRoute::class,
            self::SERVE_BEST_TRADE_ROUTE => ServeBestTradeRoute::class,
            self::SERVE_HIGHEST_PROFIT_TRADE_ROUTE => ServeHighestProfitTradeRoute::class,
            self::SUPPLY_CONSTRUCTION_SITE => SupplyConstructionSite::class,
            self::DISTRIBUTE_FUEL => DistributeFuelToMarkets::class,
            self::FULFILL_PROCUREMENT => FulfillProcurement::class,
        };
    }

    public static function interactingWithPotentialTradeRoutes(): array
    {
        return [
            self::SERVE_TRADE_ROUTE,
            self::SERVE_BEST_TRADE_ROUTE,
            self::SERVE_HIGHEST_PROFIT_TRADE_ROUTE,
        ];
    }
}
