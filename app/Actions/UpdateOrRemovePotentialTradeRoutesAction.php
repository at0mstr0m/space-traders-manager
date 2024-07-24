<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TradeGoodTypes;
use App\Models\PotentialTradeRoute;
use App\Models\TradeOpportunity;
use App\Models\Waypoint;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOrRemovePotentialTradeRoutesAction
{
    use AsAction;

    private array $identifiers = ['trade_symbol', 'origin', 'destination'];

    public function handle(): void
    {
        $tradeOpportunityTable = TradeOpportunity::query()->getQuery()->from;
        $waypointTable = Waypoint::query()->getQuery()->from;
        $exporterAlias = 'exporting_opportunities';
        $importerAlias = 'importing_opportunities';

        $changedIds = DB::table("{$tradeOpportunityTable}", "{$exporterAlias}")
            ->crossJoin("{$tradeOpportunityTable} as {$importerAlias}")
            ->whereColumn("{$exporterAlias}.symbol", "{$importerAlias}.symbol")
            ->where(
                fn (Builder $query) => $query->where([
                    "{$exporterAlias}.type" => TradeGoodTypes::EXPORT,
                    "{$importerAlias}.type" => TradeGoodTypes::IMPORT,
                ])->orWhere(
                    fn (Builder $builder) => $builder->where([
                        "{$exporterAlias}.type" => TradeGoodTypes::EXPORT,
                        "{$importerAlias}.type" => TradeGoodTypes::EXCHANGE,
                    ])
                )->orWhere(
                    fn (Builder $builder) => $builder->where([
                        "{$exporterAlias}.type" => TradeGoodTypes::EXCHANGE,
                        "{$importerAlias}.type" => TradeGoodTypes::IMPORT,
                    ])
                )
            )
            ->join("{$waypointTable} as w1", "{$exporterAlias}.waypoint_symbol", '=', 'w1.symbol')
            ->join("{$waypointTable} as w2", "{$importerAlias}.waypoint_symbol", '=', 'w2.symbol')
            ->select([
                "{$exporterAlias}.symbol as trade_symbol",
                "{$exporterAlias}.waypoint_symbol as origin",
                "{$importerAlias}.waypoint_symbol as destination",
                "{$exporterAlias}.purchase_price as purchase_price",
                "{$importerAlias}.sell_price as sell_price",
                "{$exporterAlias}.supply as supply_at_origin",
                "{$importerAlias}.supply as supply_at_destination",
                "{$exporterAlias}.activity as activity_at_origin",
                "{$importerAlias}.activity as activity_at_destination",
                "{$exporterAlias}.trade_volume as trade_volume_at_origin",
                "{$importerAlias}.trade_volume as trade_volume_at_destination",
                "{$exporterAlias}.type as origin_type",
                "{$importerAlias}.type as destination_type",
                'w1.x as origin_x',
                'w1.y as origin_y',
                'w2.x as destination_x',
                'w2.y as destination_y',
            ])
            ->get()
            ->map(function (object $potentialTradeRoute) {
                $potentialTradeRoute = (array) $potentialTradeRoute;

                return PotentialTradeRoute::updateOrCreate(
                    Arr::only($potentialTradeRoute, $this->identifiers),
                    Arr::except($potentialTradeRoute, $this->identifiers),
                )->id;
            });

        PotentialTradeRoute::whereNotIn('id', $changedIds)->delete();
    }
}
