<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Data\ShipData;
use App\Data\AgentData;
use App\Data\MountData;
use App\Data\MarketData;
use App\Data\SystemData;
use App\Enums\ShipTypes;
use App\Data\FactionData;
use App\Data\ContractData;
use App\Data\JumpGateData;
use App\Data\JumpShipData;
use App\Data\ShipyardData;
use App\Data\WaypointData;
use App\Enums\FlightModes;
use App\Data\ScanShipsData;
use App\Data\ShipCargoData;
use App\Enums\MountSymbols;
use App\Enums\TradeSymbols;
use App\Data\ExtractionData;
use App\Data\NavigationData;
use App\Data\RefuelShipData;
use App\Data\ShipRefineData;
use App\Data\TradeGoodsData;
use App\Enums\WaypointTypes;
use App\Data\CreateChartData;
use App\Data\ScanSystemsData;
use App\Data\TransactionData;
use App\Enums\FactionSymbols;
use App\Enums\RefinementGood;
use App\Data\NavigateShipData;
use Illuminate\Support\Carbon;
use App\Data\ScanWaypointsData;
use App\Data\WaypointTraitData;
use Illuminate\Support\Collection;
use App\Data\PurchaseSellCargoData;
use App\Enums\WaypointTraitSymbols;
use App\Data\InstallRemoveMountData;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\DataCollection;
use App\Data\DeliverCargoToContractData;
use App\Data\AcceptOrFulfillContractData;
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

    private function patch(string $path = '', array $payload = []): Response
    {
        return $this->baseRequest()
            ->patch($this->url . $path, $payload)
            ->throwUnlessStatus(HttpResponse::HTTP_OK);
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

    public function listAgents(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('agents', ['limit' => $perPage, 'page' => $page]);
        $data = AgentData::collection($response->json('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getPublicAgent(string $agentSymbol)
    {
        return $this->get('agents/' . $agentSymbol)->collect('data');
    }

    public function listContracts(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('my/contracts', ['limit' => $perPage, 'page' => $page]);
        $data = ContractData::collection($response->json('data'))->toCollection();

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
        $data = FactionData::collection($response->json('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getFaction(FactionSymbols $factionSymbol): FactionData
    {
        return FactionData::fromResponse(
            $this->get('factions/' . $factionSymbol->value)
                ->json('data')
        );
    }

    public function listShips(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('my/ships', ['limit' => $perPage, 'page' => $page]);
        $data = ShipData::collection($response->json('data'))->toCollection();

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

    public function getShip(string $shipSymbol): ShipData
    {
        return ShipData::fromResponse(
            $this->get('my/ships/' . $shipSymbol)
                ->json('data')
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
        return NavigationData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/orbit')
                ->json('data')['nav']
        );
    }

    public function shipRefine(string $shipSymbol, RefinementGood $refinementGood): ShipRefineData
    {
        $payload = ['produce' => $refinementGood->value];

        return ShipRefineData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/refine', $payload)
                ->json('data')
        );
    }

    public function createChart(string $shipSymbol): CreateChartData
    {
        return CreateChartData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/chart')
                ->json('data')
        );
    }

    public function getShipCooldown(string $shipSymbol): Carbon
    {
        return Carbon::parse(
            $this->get('my/ships/' . $shipSymbol . '/cooldown')
                ->json('data')['expiration']
        );
    }

    public function dockShip(string $shipSymbol): NavigationData
    {
        return NavigationData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/dock')
                ->json('data')['nav']
        );
    }

    public function extractResources(string $shipSymbol): ExtractionData
    {
        return ExtractionData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/extract')
                ->json('data')
        );
    }

    public function jettisonCargo(string $shipSymbol, TradeSymbols $tradeSymbol, int $units): ShipCargoData
    {
        $payload = [
            'symbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return ShipCargoData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/jettison', $payload)
                ->json('data')['cargo']
        );
    }

    public function jumpShip(string $shipSymbol, string $systemSymbol): JumpShipData
    {
        $payload = ['systemSymbol' => $systemSymbol];

        return JumpShipData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/jump', $payload)
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

    public function patchShipNav(string $shipSymbol, FlightModes $flightMode): NavigationData
    {
        $payload = ['flightMode' => $flightMode];

        return NavigationData::fromResponse(
            $this->patch('my/ships/' . $shipSymbol . '/nav', $payload)
                ->json('data')
        );
    }

    public function getShipNav(string $shipSymbol): NavigationData
    {
        return NavigationData::fromResponse(
            $this->get('my/ships/' . $shipSymbol . '/nav')
                ->json('data')
        );
    }

    public function warpShip(string $shipSymbol, string $waypointSymbol): NavigateShipData
    {
        $payload = ['waypointSymbol' => $waypointSymbol];

        return NavigateShipData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/warp', $payload)
                ->json('data')
        );
    }

    public function sellCargo(string $shipSymbol, TradeSymbols $tradeSymbol, int $units): PurchaseSellCargoData
    {
        $payload = [
            'symbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return PurchaseSellCargoData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/sell', $payload)
                ->json('data')
        );
    }

    public function scanSystems(string $shipSymbol): ScanSystemsData
    {
        return ScanSystemsData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/scan/systems')
                ->json('data')
        );
    }

    public function scanWaypoints(string $shipSymbol): ScanWaypointsData
    {
        return ScanWaypointsData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/scan/waypoints')
                ->json('data')
        );
    }

    public function scanShips(string $shipSymbol): ScanShipsData
    {
        return ScanShipsData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/scan/ships')
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

    public function purchaseCargo(string $shipSymbol, TradeSymbols $tradeSymbol, int $units): PurchaseSellCargoData
    {
        $payload = [
            'symbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return PurchaseSellCargoData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/purchase', $payload)
                ->json('data')
        );
    }

    public function transferCargo(
        string $transferringShipSymbol,
        string $receivingShipSymbol,
        TradeSymbols $tradeSymbol,
        int $units
    ): ShipCargoData {
        $payload = [
            'symbol' => $tradeSymbol->value,
            'units' => $units,
            'shipSymbol' => $receivingShipSymbol,
        ];

        return ShipCargoData::fromResponse(
            $this->post('my/ships/' . $transferringShipSymbol . '/transfer', $payload)
                ->json('data')['cargo']
        );
    }

    public function negotiateContract(string $shipSymbol): ContractData
    {
        return ContractData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/negotiate/contract')
                ->json('data')
        );
    }

    public function getMounts(string $shipSymbol): Collection
    {
        return MountData::collectionFromResponse(
            $this->get('my/ships/' . $shipSymbol . '/mounts')
                ->json('data')
        )->toCollection();
    }

    public function installMount(string $shipSymbol, MountSymbols $mountSymbol): InstallRemoveMountData
    {
        $payload = ['symbol' => $mountSymbol->value];

        return InstallRemoveMountData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/mounts/install', $payload)
                ->json('data')
        );
    }

    public function removeMount(string $shipSymbol, MountSymbols $mountSymbol): InstallRemoveMountData
    {
        $payload = ['symbol' => $mountSymbol->value];

        return InstallRemoveMountData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/mounts/remove', $payload)
                ->json('data')
        );
    }

    public function listSystems(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get('systems', ['limit' => $perPage, 'page' => $page]);
        $data = SystemData::collection($response->json('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getSystem(string $systemSymbol): SystemData
    {
        return SystemData::fromResponse(
            $this->get('systems/' . $systemSymbol)
                ->json('data')
        );
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
        $data = WaypointData::collection($response->json('data'))->toCollection();

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

    public function getWaypoint(string $waypointSymbol): WaypointData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return WaypointData::fromResponse(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol)
                ->json('data')
        );
    }

    public function getMarket(string $waypointSymbol): MarketData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return MarketData::fromResponse(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/market')
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

    public function getShipyard(string $waypointSymbol): ShipyardData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return ShipyardData::fromResponse(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/shipyard')
                ->json('data')
        );
    }

    public function getJumpGate(string $waypointSymbol): JumpGateData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return JumpGateData::fromResponse(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/jump-gate')
                ->json('data')
        );
    }
}
