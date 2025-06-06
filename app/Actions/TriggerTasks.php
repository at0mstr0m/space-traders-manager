<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TaskTypes;
use App\Jobs\DistributeFuelToMarkets;
use App\Jobs\FulfillProcurement;
use App\Jobs\MultipleMineAndPassOn;
use App\Jobs\MultipleSiphonAndPassOn;
use App\Jobs\ServeBestTradeRoute;
use App\Jobs\ServeHighestProfitTradeRoute;
use App\Jobs\ServeRandomTradeRoute;
use App\Jobs\SupplyConstructionSite;
use App\Models\Ship;
use App\Models\Task;
use Lorisleiva\Actions\Concerns\AsAction;

class TriggerTasks
{
    use AsAction;

    public function handle()
    {
        Task::where('type', TaskTypes::SERVE_TRADE_ROUTE)
            ->each(
                fn (Task $task) => $task->ships->each(
                    fn (Ship $ship) => ServeRandomTradeRoute::dispatch($ship->symbol)
                )
            );
        Task::where('type', TaskTypes::SERVE_BEST_TRADE_ROUTE)
            ->each(
                fn (Task $task) => $task->ships->each(
                    fn (Ship $ship) => ServeBestTradeRoute::dispatch($ship->symbol)
                )
            );
        Task::where('type', TaskTypes::SERVE_HIGHEST_PROFIT_TRADE_ROUTE)
            ->each(
                fn (Task $task) => $task->ships->each(
                    fn (Ship $ship) => ServeHighestProfitTradeRoute::dispatch($ship->symbol)
                )
            );
        Task::where('type', TaskTypes::COLLECTIVE_MINING)
            ->each(
                fn (Task $task) => MultipleMineAndPassOn::dispatch($task->id)
            );
        Task::where('type', TaskTypes::COLLECTIVE_SIPHONING)
            ->each(
                fn (Task $task) => MultipleSiphonAndPassOn::dispatch($task->id)
            );
        Task::where('type', TaskTypes::SUPPLY_CONSTRUCTION_SITE)
            ->each(
                fn (Task $task) => $task->ships->each(
                    fn (Ship $ship) => SupplyConstructionSite::dispatch($ship->symbol)
                )
            );
        Task::where('type', TaskTypes::DISTRIBUTE_FUEL)
            ->each(
                fn (Task $task) => $task->ships->each(
                    fn (Ship $ship) => DistributeFuelToMarkets::dispatch($ship->symbol)
                )
            );
        Task::where('type', TaskTypes::FULFILL_PROCUREMENT)
            ->each(
                fn (Task $task) => $task->ships->each(
                    fn (Ship $ship) => FulfillProcurement::dispatch($ship->symbol)
                )
            );
    }
}
