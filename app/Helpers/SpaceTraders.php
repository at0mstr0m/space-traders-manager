<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Data\AcceptOrFulfillContractData;
use App\Data\AgentData;
use App\Data\ConstructionSiteData;
use App\Data\ContractData;
use App\Data\CreateChartData;
use App\Data\CreateSurveyData;
use App\Data\DeliverCargoToContractData;
use App\Data\ExtractionData;
use App\Data\FactionData;
use App\Data\ImportExportExchangeGoodData;
use App\Data\InstallRemoveMountData;
use App\Data\JumpGateData;
use App\Data\JumpShipData;
use App\Data\MarketData;
use App\Data\MountData;
use App\Data\NavigateShipData;
use App\Data\NavigationData;
use App\Data\PotentialTradeRouteData;
use App\Data\PurchaseSellCargoData;
use App\Data\PurchaseShipData;
use App\Data\RefuelShipData;
use App\Data\ScanShipsData;
use App\Data\ScanSystemsData;
use App\Data\ScanWaypointsData;
use App\Data\ShipCargoData;
use App\Data\ShipData;
use App\Data\ShipRefineData;
use App\Data\ShipyardData;
use App\Data\ShipyardShipData;
use App\Data\SupplyConstructionSiteData;
use App\Data\SystemData;
use App\Data\TradeGoodsData;
use App\Data\WaypointData;
use App\Enums\FactionSymbols;
use App\Enums\FlightModes;
use App\Enums\MountSymbols;
use App\Enums\RefinementGood;
use App\Enums\ShipTypes;
use App\Enums\TradeSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Enums\WaypointTypes;
use App\Models\Ship;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\DataCollection;

class SpaceTraders
{
    private const LIMITER_ONE = 'REQUEST_COUNT_1';

    private const LIMITER_TWO = 'REQUEST_COUNT_2';

    public function __construct(
        private string $token,
        private string $url = 'https://api.spacetraders.io/v2/',
    ) {}

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
            $this->post('my/contracts/' . $contractId . '/fulfill')
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

    public function purchaseShip(ShipTypes $shipType, string $waypointSymbol): PurchaseShipData
    {
        $payload = [
            'shipType' => $shipType->value,
            'waypointSymbol' => $waypointSymbol,
        ];

        return PurchaseShipData::fromResponse(
            $this->post('my/ships', $payload)
                ->json('data')
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

    public function getShipCooldown(string $shipSymbol): ?Carbon
    {
        $response = data_get(
            $this->get('my/ships/' . $shipSymbol . '/cooldown')
                ->json('data'),
            'expiration'
        );

        return $response ? Carbon::parse($response) : null;
    }

    public function dockShip(string $shipSymbol): NavigationData
    {
        return NavigationData::fromResponse(
            $this->post('my/ships/' . $shipSymbol . '/dock')
                ->json('data')['nav']
        );
    }

    public function createSurvey(string $shipSymbol): CreateSurveyData
    {
        return CreateSurveyData::from(
            $this->post('my/ships/' . $shipSymbol . '/survey')
                ->json('data')
        );
    }

    public function extractResourcesWithSurvey(string $shipSymbol, array $surveyData): ExtractionData
    {
        return ExtractionData::fromResponse(
            $this->post(
                'my/ships/' . $shipSymbol . '/extract/survey',
                $surveyData,
            )->json('data')
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
            'tradeSymbol' => $tradeSymbol->value,
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
                ->json('data')['contract']
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

    /**
     * @return Collection<int, WaypointData>
     */
    public function listWaypointsInSystem(
        string $systemSymbol,
        ?WaypointTypes $waypointType = null,
        ?WaypointTraitSymbols $waypointTrait = null,
        int $perPage = 10,
        int $page = 1,
        bool $all = false
    ): Collection {
        $response = $this->get(
            'systems/' . $systemSymbol . '/waypoints',
            [
                'limit' => $perPage,
                'page' => $page,
                ...($waypointType ? ['type' => $waypointType->value] : []),
                ...($waypointTrait ? ['traits' => $waypointTrait->value] : []),
            ]
        );
        $data = WaypointData::collection($response->json('data'))->toCollection();

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page, [
                'systemSymbol' => $systemSymbol,
                'waypointType' => $waypointType,
                'waypointTrait' => $waypointTrait,
            ])
            : $data;
    }

    /**
     * @return Collection<int, ShipyardShipData>
     */
    public function listPurchasableShipsInSystem(string $systemSymbol): Collection
    {
        return $this->listWaypointsInSystem(
            $systemSymbol,
            waypointTrait: WaypointTraitSymbols::SHIPYARD,
            all: true
        )
            ->map(fn (WaypointData $waypointData) => $this->getShipyard($waypointData->symbol))
            ->reduce(
                fn (Collection $carry, ShipyardData $shipyardData) => $carry->concat($shipyardData->ships),
                collect()
            );
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

        return Cache::remember(
            'get_market:' . $waypointSymbol,
            now()->addMinute(),
            fn () => MarketData::fromResponse(
                $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/market')
                    ->json('data')
            )
        );
    }

    public function listMarketplacesInSystem(string $waypointSymbol): Collection
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return Cache::remember(
            'list_marketplaces_in_system:' . $systemSymbol,
            now()->addMinute(),
            fn () => $this->listWaypointsInSystem(
                $systemSymbol,
                waypointTrait: WaypointTraitSymbols::MARKETPLACE,
                all: true
            )->map(fn (WaypointData $waypoint) => $this->getMarket($waypoint->symbol))
        );
    }

    public function listExportGoodsInSystem(string $waypointSymbol): Collection
    {
        return $this->listGoodsInSystem($waypointSymbol, 'exports');
    }

    public function listImportGoodsInSystem(string $waypointSymbol): Collection
    {
        return $this->listGoodsInSystem($waypointSymbol, 'imports');
    }

    public function listExchangeGoodsInSystem(string $waypointSymbol): Collection
    {
        return $this->listGoodsInSystem($waypointSymbol, 'exchange');
    }

    public function listMarketplacesInSystemImporting(string $waypointSymbol, TradeSymbols $tradeSymbol): Collection
    {
        return static::filterMarketsByTradeSymbol(
            $this->listMarketplacesInSystem($waypointSymbol),
            $tradeSymbol,
            'imports'
        );
    }

    public function listMarketplacesInSystemExporting(string $waypointSymbol, TradeSymbols $tradeSymbol): Collection
    {
        return static::filterMarketsByTradeSymbol(
            $this->listMarketplacesInSystem($waypointSymbol),
            $tradeSymbol,
            'exports'
        );
    }

    public function listMarketplacesInSystemExchanging(string $waypointSymbol, TradeSymbols $tradeSymbol): Collection
    {
        return static::filterMarketsByTradeSymbol(
            $this->listMarketplacesInSystem($waypointSymbol),
            $tradeSymbol,
            'exchange'
        );
    }

    public function listMarketplacesInSystemTrading(string $waypointSymbol, TradeSymbols $tradeSymbol): Collection
    {
        return static::filterMarketsByTradeSymbol(
            $this->listMarketplacesInSystem($waypointSymbol),
            $tradeSymbol,
            'tradeGoods'
        );
    }

    public function listMarketplacesInSystemImportingMany(
        string $waypointSymbol,
        array|Collection $tradeSymbols
    ): Collection {
        return $this->listMarketplacesInSystemForMany(
            $waypointSymbol,
            $tradeSymbols,
            'imports'
        );
    }

    public function listMarketplacesInSystemExportingMany(
        string $waypointSymbol,
        array|Collection $tradeSymbols
    ): Collection {
        return $this->listMarketplacesInSystemForMany(
            $waypointSymbol,
            $tradeSymbols,
            'exports'
        );
    }

    public function listMarketplacesInSystemExchangingMany(
        string $waypointSymbol,
        array|Collection $tradeSymbols
    ): Collection {
        return $this->listMarketplacesInSystemForMany(
            $waypointSymbol,
            $tradeSymbols,
            'exports'
        );
    }

    public function listMarketplacesInSystemTradingMany(
        string $waypointSymbol,
        array|Collection $tradeSymbols
    ): Collection {
        return $this->listMarketplacesInSystemForMany(
            $waypointSymbol,
            $tradeSymbols,
            'tradeGoods'
        );
    }

    public function listMarketplacesInSystemForShipCargos(Ship $ship): Collection
    {
        $waypointSymbol = $ship->waypoint_symbol;
        $tradeSymbols = $ship->cargos()->pluck('symbol');

        return $this->listMarketplacesInSystemImportingMany(
            $waypointSymbol,
            $tradeSymbols
        );
    }

    /**
     * @return Collection<string, Collection<PotentialTradeRouteData>>
     */
    public function listPotentialTradeRoutesInSystem(string $waypointSymbol): Collection
    {
        $imports = $this->listImportGoodsInSystem($waypointSymbol);
        $exports = $this->listExportGoodsInSystem($waypointSymbol);

        return $imports->filter(
            fn (ImportExportExchangeGoodData $import) => $exports->where('symbol', $import->symbol)->isNotEmpty()
        )->mapWithKeys(function (ImportExportExchangeGoodData $goodData) use ($waypointSymbol) {
            $tradeSymbol = TradeSymbols::fromName($goodData->symbol);
            $exportingMarketplaces = $this->listMarketplacesInSystemExporting($waypointSymbol, $tradeSymbol);
            $importingMarketplaces = $this->listMarketplacesInSystemImporting($waypointSymbol, $tradeSymbol);
            $potentialTradeRoutes = $exportingMarketplaces->crossJoin($importingMarketplaces)
                ->map(fn (array $correspondingMarketplaces) => PotentialTradeRouteData::fromAggregatedData([
                    'symbol' => $goodData->symbol,
                    'exportingMarket' => $correspondingMarketplaces[0],
                    'importingMarket' => $correspondingMarketplaces[1],
                ]));

            return [$tradeSymbol->value => $potentialTradeRoutes];
        })->flatten();
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

    public function getConstructionSite(string $waypointSymbol): ConstructionSiteData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return Cache::remember(
            'get_construction_site:' . $waypointSymbol,
            now()->addHour(),
            fn () => ConstructionSiteData::fromResponse(
                $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/construction')
                    ->json('data')
            )
        );
    }

    /**
     * @template TWaypointSymbol string
     *
     * @return Collection<TWaypointSymbol, ConstructionSiteData>
     */
    public function listConstructionSitesInSystem(string $systemSymbol): Collection
    {
        return $this->listWaypointsInSystem($systemSymbol, all: true)
            ->pluck('symbol')
            ->mapWithKeys(fn (string $waypointSymbol) => [
                $waypointSymbol => $this->getConstructionSite($waypointSymbol),
            ])
            ->filter(fn (ConstructionSiteData $constructionSite) => $constructionSite->isComplete === false);
    }

    public function supplyConstructionSite(
        string $waypointSymbol,
        string $shipSymbol,
        TradeSymbols $tradeSymbol,
        int $units
    ): SupplyConstructionSiteData {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);
        $payload = [
            'shipSymbol' => $shipSymbol,
            'tradeSymbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return SupplyConstructionSiteData::fromResponse(
            $this->post(
                'systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/construction/supply',
                $payload
            )->json('data')
        );
    }

    private function listMarketplacesInSystemForMany(
        string $waypointSymbol,
        array|Collection $tradeSymbols,
        string $haystack
    ): Collection {
        $marketplaces = $this->listMarketplacesInSystem($waypointSymbol);

        return collect($tradeSymbols)->unique()
            ->mapWithKeys(fn (TradeSymbols $tradeSymbol) => [
                $tradeSymbol->value => static::filterMarketsByTradeSymbol($marketplaces, $tradeSymbol, $haystack),
            ])
            ->map(fn (Collection $marketplaces) => $marketplaces->first());
    }

    private function listGoodsInSystem(string $waypointSymbol, string $type): Collection
    {
        return $this->listMarketplacesInSystem($waypointSymbol)
            ->pluck($type)
            ->map(fn (DataCollection $exports) => $exports->toCollection())
            ->flatten()
            ->unique('symbol');
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

        for ($currentPage = $page + 1; $currentPage <= $totalPages; ++$currentPage) {
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

    private static function filterMarketsByTradeSymbol(
        Collection $marketplaces,
        TradeSymbols $tradeSymbol,
        string $haystack
    ): Collection {
        return $marketplaces->filter(
            fn (MarketData $marketData) => $marketData->{$haystack}
                ->toCollection()
                ->filter(
                    fn (ImportExportExchangeGoodData|TradeGoodsData $data) => $data->symbol === $tradeSymbol->value
                )->isNotEmpty()
        );
    }
}
