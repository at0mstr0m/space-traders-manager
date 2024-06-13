<?php

declare(strict_types=1);

namespace App\Actions\Firebase;

use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use App\Services\Firebase;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadRelationsPotentialTradeRoutesToShipsAction
{
    use AsAction;

    private Firebase $firebase;

    public function __construct()
    {
        $this->firebase = app(Firebase::class);
    }

    public function handle(bool $purge = false)
    {
        if ($purge) {
            PotentialTradeRoute::getQuery()->update(['ship_id' => null]);
        }

        $this->firebase
            ->getPotentialTradeRouteData()
            ->each(function (array $data) {
                $shipSymbol = data_get($data, 'ship_symbol');
                if (!$shipSymbol) {
                    return;
                }

                $shipId = Ship::findBySymbol($shipSymbol)->id;
                PotentialTradeRoute::firstWhere(Arr::except($data, 'ship_symbol'))
                    ->setAttribute('ship_id', $shipId)
                    ->saveQuietly();
            });
    }
}
