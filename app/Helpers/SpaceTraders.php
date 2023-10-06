<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Data\ShipData;
use App\Data\AgentData;
use App\Data\MarketData;
use App\Enums\ShipTypes;
use App\Data\FactionData;
use App\Data\ContractData;
use App\Data\ShipyardData;
use App\Data\WaypointData;
use App\Data\SellCargoData;
use App\Data\ShipCargoData;
use App\Enums\TradeSymbols;
use App\Data\ExtractionData;
use App\Data\NavigationData;
use App\Data\RefuelShipData;
use App\Data\TradeGoodsData;
use App\Enums\WaypointTypes;
use App\Data\TransactionData;
use App\Data\NavigateShipData;
use App\Data\WaypointTraitData;
use App\Data\AcceptOrFulfillContractData;
use Illuminate\Support\Collection;
use App\Enums\WaypointTraitSymbols;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Data\DeliverCargoToContractData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Response as HttpResponse;

class SpaceTraders
{
    private const LIMITER_ONE = 'REQUEST_COUNT_1';
    private const LIMITER_TWO = 'REQUEST_COUNT_2';

    public function __construct(
        private string $token,
        private string $url = 'https://api.spacetraders.io/v2/',
    ) {
    }

    // only allow 2 requests per second
    private function avoidRateLimit(): void
    {
        if (!Cache::get(static::LIMITER_ONE) && !Cache::get(static::LIMITER_TWO)) {
            Cache::remember(static::LIMITER_ONE, now()->addSecond(), fn () => true);
        } elseif (Cache::get(static::LIMITER_ONE) && Cache::get(static::LIMITER_TWO)) {
            sleep(1);
            Cache::remember(static::LIMITER_ONE, now()->addSecond(), fn () => true);
        } elseif (Cache::get(static::LIMITER_ONE)) {
            Cache::remember(static::LIMITER_TWO, now()->addSecond(), fn () => true);
        }
    }

    private function baseRequest(): PendingRequest
    {
        $this->avoidRateLimit();
        return HTTP::withToken($this->token);
    }

    private function get(string $path = '', array $query = []): Response
    {
        return $this->baseRequest()
            ->get($this->url . $path, $query)
            ->throwUnlessStatus(HttpResponse::HTTP_OK);
    }

    private function post(string $path = '', array $payload = []): Response
    {
        return $this->baseRequest()
            ->post($this->url . $path, $payload)
            ->throwUnlessStatus(HttpResponse::HTTP_OK);
    }

    private function getAllPages(Response $response, string $methodName, int $page, array $arguments = []): Collection
    {

        $data = $response->collect('data');
        $meta = $response->collect('meta');
        $totalNumber = data_get($meta, 'total');
        $perPage = data_get($meta, 'limit');
        $totalPages = (int) ceil($totalNumber / $perPage);

        for ($currentPage = $page + 1; $currentPage <= $totalPages; $currentPage++) {
            $data = $data->concat($this->{$methodName}(
                ...[
                    'perPage' => $perPage,
                    'page' => $currentPage,
                    ...$arguments,
                ]
            ));
        }

        return $data;
    }

    private function getAllPagesData(
        Collection $data,
        Response $response,
        string $methodName,
        int $page,
        array $arguments = []
    ): Collection {
        $meta = $response->collect('meta');
        $totalNumber = data_get($meta, 'total');
        $perPage = data_get($meta, 'limit');
        $totalPages = (int) ceil($totalNumber / $perPage);

        for ($currentPage = $page + 1; $currentPage <= $totalPages; $currentPage++) {
            $data = $data->concat($this->{$methodName}(
                ...[
                    'perPage' => $perPage,
                    'page' => $currentPage,
                    ...$arguments,
                ]
            ));
        }

        return $data;
    }

    public function getStatus()
    {
        return $this->get()->collect('data');
    }

    public function getAgent(): AgentData
    {
        return AgentData::from($this->get('my/agent')->collect('data'));
    }

    public function listAgents()
    {
        return $this->get('agents')->collect('data');
    }

    public function getPublicAgent(string $agentSymbol)
    {
        return $this->get('agents/' . $agentSymbol)->collect('data');
    }

    public function listContracts(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('my/contracts', ['limit' => $perPage, 'page' => $page]);
        $data = ContractData::collection($response->collect('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getContract(string $contractId): ContractData
    {
        return ContractData::fromResponse(
            $this->get('my/contracts/' . $contractId)
                ->json('data')
        );
    }

    public function acceptContract(string $contractId): AcceptOrFulfillContractData
    {
        return AcceptOrFulfillContractData::fromResponse(
            $this->post('my/contracts/' . $contractId . '/accept')
                ->json('data')
        );
    }

    public function deliverCargoToContract(
        string $contractId,
        string $shipSymbol,
        TradeSymbols $tradeSymbol,
        int $units
    ): DeliverCargoToContractData {
        $payload = [
            'shipSymbol' => $shipSymbol,
            'tradeSymbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return DeliverCargoToContractData::fromResponse(
            $this->post('my/contracts/' . $contractId . '/deliver', $payload)
                ->json('data')
        );
    }

    public function fulfillContract(string $contractId): AcceptOrFulfillContractData
    {
        return AcceptOrFulfillContractData::fromResponse(
            $this->post('my/contracts/' . $contractId . '/accept')
                ->json('data')
        );
    }

    public function listFactions(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('factions', ['limit' => $perPage, 'page' => $page]);
        $data = FactionData::collection($response->collect('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function listShips(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('my/ships', ['limit' => $perPage, 'page' => $page]);
        $data = ShipData::collection($response->collect('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function purchaseShip(ShipTypes $shipType, string $waypointSymbol): Collection
    {
        $payload = [
            'shipType' => $shipType->value,
            'waypointSymbol' => $waypointSymbol,
        ];
        return $this->post('my/ships', $payload)
            ->collect('data')
            ->pipe(
                fn (Collection $data) => collect([
                    'agent' => AgentData::fromResponse($data['agent']),
                    'ship' => ShipData::fromResponse($data['ship']),
                    'transaction' => TransactionData::fromResponse($data['transaction']),
                ])
            );
    }

    public function getShipCargo(string $shipSymbol): ShipCargoData
    {
        return ShipCargoData::fromResponse(
            $this->get('my/ships/' . $shipSymbol . '/cargo')
                ->json('data')
        );
    }

    public function orbitShip(string $shipSymbol): NavigationData
    {
        return $this->post('my/ships/' . $shipSymbol . '/orbit')
            ->collect('data')
            ->pipe(fn (Collection $data) => NavigationData::fromResponse($data['nav']));
    }

    public function dockShip(string $shipSymbol): NavigationData
    {
        return $this->post('my/ships/' . $shipSymbol . '/dock')
            ->collect('data')
            ->pipe(fn (Collection $data) => NavigationData::fromResponse($data['nav']));
    }

    public function extractResources(string $shipSymbol): ExtractionData
    {
        return ExtractionData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/extract')
                ->json('data')
        );
    }

    public function sellCargo(string $shipSymbol, TradeSymbols $tradeSymbol, int $units): SellCargoData
    {
        $payload =                 [
            'symbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return SellCargoData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/sell', $payload)
                ->json('data')
        );
    }

    public function navigateShip(string $shipSymbol, string $waypointSymbol): NavigateShipData
    {
        $payload = ['waypointSymbol' => $waypointSymbol];

        return NavigateShipData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/navigate', $payload)
                ->json('data')
        );
    }

    public function refuelShip(string $shipSymbol): RefuelShipData
    {
        return RefuelShipData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/refuel')
                ->json('data')
        );
    }

    public function listSystems(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('systems', ['limit' => $perPage, 'page' => $page]);

        return $all
            ? $this->getAllPages($response, __FUNCTION__, $page)
            : $response->collect('data');
    }

    public function getSystem(string $systemSymbol): Collection
    {
        return $this->get('systems/' . $systemSymbol)->collect('data');
    }

    public function listWaypointsInSystem(string $systemSymbol, int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get(
            'systems/' . $systemSymbol . '/waypoints',
            [
                'limit' => $perPage,
                'page' => $page,
            ]
        );
        $data = WaypointData::collection($response->collect('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function listWaypointsInSystemHavingTrait(string $systemSymbol, WaypointTraitSymbols $traitSymbol): Collection
    {
        return $this->listWaypointsInSystem($systemSymbol, all: true)
            ->filter(
                fn (WaypointData $waypoint) => $waypoint->traits
                    ->toCollection()
                    ->filter(
                        fn (WaypointTraitData $trait) => $trait->symbol === $traitSymbol->value
                    )
                    ->isNotEmpty()
            );
    }

    public function listWaypointsInSystemOfType(string $systemSymbol, WaypointTypes $waypointType): Collection
    {
        return $this->listWaypointsInSystem($systemSymbol, all: true)
            ->filter(fn (WaypointData $waypoint) => $waypoint->type === $waypointType->value);
    }

    public function getWaypoint(string $symbol): Collection
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);

        return $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol)
            ->collect('data');
    }

    public function getMarket(string $symbol): MarketData
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);

        return MarketData::fromResponse(
            $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol . '/market')
                ->json('data')
        );
    }

    public function listMarketplacesInSystemTrading(string $waypointSymbol, TradeSymbols $tradeSymbol): Collection
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return $this->listWaypointsInSystemHavingTrait($systemSymbol, WaypointTraitSymbols::MARKETPLACE)
            ->map(fn (WaypointData $waypoint) => $this->getMarket($waypoint->symbol))
            ->filter(
                fn (MarketData $marketData) => $marketData->tradeGoods->toCollection()->filter(
                    fn (TradeGoodsData $tradeGoodsData) => $tradeGoodsData->symbol === $tradeSymbol->value
                )->isNotEmpty()
            );
    }

    public function getShipyard(string $symbol): ShipyardData
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);

        return ShipyardData::fromResponse(
            $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol . '/shipyard')
                ->json('data')
        );
    }

    public function getJumpGate(string $symbol): Collection
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);

        return $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol . '/jump-gate')
            ->collect('data');
    }
}
