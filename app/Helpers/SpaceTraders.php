<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Actions\UpdateWaypointAction;
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
use App\Data\RepairScrapTransactionData;
use App\Data\RepairShipData;
use App\Data\ScanShipsData;
use App\Data\ScanSystemsData;
use App\Data\ScanWaypointsData;
use App\Data\ScrapShipData;
use App\Data\ShipCargoData;
use App\Data\ShipData;
use App\Data\ShipRefineData;
use App\Data\ShipyardData;
use App\Data\ShipyardShipData;
use App\Data\SiphonData;
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
use App\Models\Waypoint;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SpaceTraders
{
    private const MAX_PER_PAGE = 20;

    public function __construct(
        private string $token,
        private string $url = 'https://api.spacetraders.io/v2/',
    ) {}

    public function getStatus(): Collection
    {
        return $this->get()->collect('data');
    }

    public function getAgent(): AgentData
    {
        return AgentData::from($this->get('my/agent')->collect('data'));
    }

    public function listAgents(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get(
            'agents',
            static::paginationParams($perPage, $page, $all)
        );
        /** @var Collection */
        $data = AgentData::collect($response->json('data'), Collection::class);

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
        $response = $this->get(
            'my/contracts',
            static::paginationParams($perPage, $page, $all)
        );
        /** @var Collection */
        $data = ContractData::collect($response->json('data'), Collection::class);

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getContract(string $contractId): ContractData
    {
        return ContractData::from(
            $this->get('my/contracts/' . $contractId)
                ->json('data')
        );
    }

    public function acceptContract(string $contractId): AcceptOrFulfillContractData
    {
        return AcceptOrFulfillContractData::from(
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

        return DeliverCargoToContractData::from(
            $this->post('my/contracts/' . $contractId . '/deliver', $payload)
                ->json('data')
        );
    }

    public function fulfillContract(string $contractId): AcceptOrFulfillContractData
    {
        return AcceptOrFulfillContractData::from(
            $this->post('my/contracts/' . $contractId . '/fulfill')
                ->json('data')
        );
    }

    public function listFactions(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get(
            'factions',
            static::paginationParams($perPage, $page, $all)
        );

        /** @var Collection */
        $data = FactionData::collect($response->json('data'), Collection::class);

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getFaction(FactionSymbols $factionSymbol): FactionData
    {
        return FactionData::from(
            $this->get('factions/' . $factionSymbol->value)
                ->json('data')
        );
    }

    public function listShips(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get(
            'my/ships',
            static::paginationParams($perPage, $page, $all)
        );

        /** @var Collection */
        $data = ShipData::collect($response->json('data'), Collection::class);

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

        return PurchaseShipData::from(
            $this->post('my/ships', $payload)
                ->json('data')
        );
    }

    public function getShip(string $shipSymbol): ShipData
    {
        return ShipData::from(
            $this->get('my/ships/' . $shipSymbol)
                ->json('data')
        );
    }

    public function getShipCargo(string $shipSymbol): ShipCargoData
    {
        return ShipCargoData::from(
            $this->get('my/ships/' . $shipSymbol . '/cargo')
                ->json('data')
        );
    }

    public function orbitShip(string $shipSymbol): NavigationData
    {
        return NavigationData::from(
            $this->post('my/ships/' . $shipSymbol . '/orbit')
                ->json('data')['nav']
        );
    }

    public function shipRefine(string $shipSymbol, RefinementGood $refinementGood): ShipRefineData
    {
        $payload = ['produce' => $refinementGood->value];

        return ShipRefineData::from(
            $this->post('my/ships/' . $shipSymbol . '/refine', $payload)
                ->json('data')
        );
    }

    public function createChart(string $shipSymbol): CreateChartData
    {
        return CreateChartData::from(
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
        return NavigationData::from(
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

    public function extractResources(string $shipSymbol): ExtractionData
    {
        return ExtractionData::from(
            $this->post('my/ships/' . $shipSymbol . '/extract')
                ->json('data')
        );
    }

    public function extractResourcesWithSurvey(string $shipSymbol, array $surveyData): ExtractionData
    {
        return ExtractionData::from(
            $this->post(
                'my/ships/' . $shipSymbol . '/extract/survey',
                $surveyData,
            )->json('data')
        );
    }

    public function siphonResources(string $shipSymbol): SiphonData
    {
        return SiphonData::from(
            $this->post('my/ships/' . $shipSymbol . '/siphon')
                ->json('data')
        );
    }

    public function jettisonCargo(string $shipSymbol, TradeSymbols $tradeSymbol, int $units): ShipCargoData
    {
        $payload = [
            'symbol' => $tradeSymbol->value,
            'units' => $units,
        ];

        return ShipCargoData::from(
            $this->post('my/ships/' . $shipSymbol . '/jettison', $payload)
                ->json('data')['cargo']
        );
    }

    public function jumpShip(string $shipSymbol, string $waypointSymbol): JumpShipData
    {
        $payload = ['waypointSymbol' => $waypointSymbol];

        return JumpShipData::from(
            $this->post('my/ships/' . $shipSymbol . '/jump', $payload)
                ->json('data')
        );
    }

    public function navigateShip(string $shipSymbol, string $waypointSymbol): NavigateShipData
    {
        $payload = ['waypointSymbol' => $waypointSymbol];

        return NavigateShipData::from(
            $this->post('my/ships/' . $shipSymbol . '/navigate', $payload)
                ->json('data')
        );
    }

    public function patchShipNav(string $shipSymbol, FlightModes $flightMode): NavigationData
    {
        $payload = ['flightMode' => $flightMode];

        return NavigationData::from(
            $this->patch('my/ships/' . $shipSymbol . '/nav', $payload)
                ->json('data')
        );
    }

    public function getShipNav(string $shipSymbol): NavigationData
    {
        return NavigationData::from(
            $this->get('my/ships/' . $shipSymbol . '/nav')
                ->json('data')
        );
    }

    public function warpShip(string $shipSymbol, string $waypointSymbol): NavigateShipData
    {
        $payload = ['waypointSymbol' => $waypointSymbol];

        return NavigateShipData::from(
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

        return PurchaseSellCargoData::from(
            $this->post('my/ships/' . $shipSymbol . '/sell', $payload)
                ->json('data')
        );
    }

    public function scanSystems(string $shipSymbol): ScanSystemsData
    {
        return ScanSystemsData::from(
            $this->post('my/ships/' . $shipSymbol . '/scan/systems')
                ->json('data')
        );
    }

    public function scanWaypoints(string $shipSymbol): ScanWaypointsData
    {
        return ScanWaypointsData::from(
            $this->post('my/ships/' . $shipSymbol . '/scan/waypoints')
                ->json('data')
        );
    }

    public function scanShips(string $shipSymbol): ScanShipsData
    {
        return ScanShipsData::from(
            $this->post('my/ships/' . $shipSymbol . '/scan/ships')
                ->json('data')
        );
    }

    public function refuelShip(string $shipSymbol): RefuelShipData
    {
        return RefuelShipData::from(
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

        return PurchaseSellCargoData::from(
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

        return ShipCargoData::from(
            $this->post('my/ships/' . $transferringShipSymbol . '/transfer', $payload)
                ->json('data')['cargo']
        );
    }

    public function negotiateContract(string $shipSymbol): ContractData
    {
        return ContractData::from(
            $this->post('my/ships/' . $shipSymbol . '/negotiate/contract')
                ->json('data')['contract']
        );
    }

    /**
     * @return Collection<int, MountData>
     */
    public function getMounts(string $shipSymbol)
    {
        return MountData::collect(
            $this->get('my/ships/' . $shipSymbol . '/mounts')
                ->json('data'),
            Collection::class
        );
    }

    public function installMount(string $shipSymbol, MountSymbols $mountSymbol): InstallRemoveMountData
    {
        $payload = ['symbol' => $mountSymbol->value];

        return InstallRemoveMountData::from(
            $this->post('my/ships/' . $shipSymbol . '/mounts/install', $payload)
                ->json('data')
        );
    }

    public function removeMount(string $shipSymbol, MountSymbols $mountSymbol): InstallRemoveMountData
    {
        $payload = ['symbol' => $mountSymbol->value];

        return InstallRemoveMountData::from(
            $this->post('my/ships/' . $shipSymbol . '/mounts/remove', $payload)
                ->json('data')
        );
    }

    public function getScrapShip(string $shipSymbol): RepairScrapTransactionData
    {
        return RepairScrapTransactionData::from(
            $this->get('my/ships/' . $shipSymbol . '/scrap')
                ->json('data')['transaction']
        );
    }

    public function scrapShip(string $shipSymbol): ScrapShipData
    {
        return ScrapShipData::from(
            $this->post('my/ships/' . $shipSymbol . '/scrap')
                ->json('data')
        );
    }

    public function getRepairShip(string $shipSymbol): RepairScrapTransactionData
    {
        return RepairScrapTransactionData::from(
            $this->get('my/ships/' . $shipSymbol . '/repair')
                ->json('data')['transaction']
        );
    }

    public function repairShip(string $shipSymbol): RepairShipData
    {
        return RepairShipData::from(
            $this->post('my/ships/' . $shipSymbol . '/repair')
                ->json('data')
        );
    }

    public function listSystems(int $perPage = 10, int $page = 1, bool $all = false): Collection
    {
        $response = $this->get(
            'systems',
            static::paginationParams($perPage, $page, $all)
        );

        /** @var Collection<int, SystemData> */
        $data = SystemData::collect($response->json('data'), Collection::class);

        return $all
            ? $this->getAllPagesData($data, $response, __FUNCTION__, $page)
            : $data;
    }

    public function getSystem(string $systemSymbol): SystemData
    {
        return SystemData::from(
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
                ...static::paginationParams($perPage, $page, $all),
                ...($waypointType ? ['type' => $waypointType->value] : []),
                ...($waypointTrait ? ['traits' => $waypointTrait->value] : []),
            ]
        );
        /** @var Collection */
        $data = WaypointData::collect($response->json('data'), Collection::class);

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
            ->pluck('ships')
            ->flatten(1)
            ->filter();
    }

    public function getWaypoint(string $waypointSymbol): WaypointData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return WaypointData::from(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol)
                ->json('data')
        );
    }

    /**
     * @return MarketData
     */
    public function getMarket(string $waypointSymbol)
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return MarketData::from(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/market')
                ->json('data')
        );
    }

    /**
     * @return Collection<int, MarketData>
     */
    public function listMarketplacesInSystem(string $waypointSymbol)
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return Cache::remember(
            'list_marketplaces_in_system:' . $systemSymbol,
            now()->addMinute(),
            fn () => $this->listWaypointsInSystem(
                $systemSymbol,
                waypointTrait: WaypointTraitSymbols::MARKETPLACE,
                all: true
            )->map(function (WaypointData $waypointData) {
                // avoid making a request if there is no ship present
                $waypointSymbol = $waypointData->symbol;

                // could happen that the waypoint is not in the database
                $waypoint = Waypoint::findBySymbol($waypointSymbol)
                    ?? UpdateWaypointAction::run(
                        $this->getWaypoint($waypointSymbol)
                    );

                return $waypoint->ships()->exists()
                    ? $this->getMarket($waypointSymbol)
                    : null;
            })->filter()
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
     * @return Collection<string, Collection<int, PotentialTradeRouteData>>
     *
     * @deprecated
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

    /**
     * @deprecated
     *
     * @return Collection<string, Collection<TradeGoodsData>>
     */
    public function listTradeGoodsInSystem(string $waypointSymbol): Collection
    {
        return $this->listMarketplacesInSystem($waypointSymbol)
            ->mapWithKeys(fn (MarketData $marketData) => [
                $marketData->symbol => $marketData->tradeGoods,
            ])
            ->filter(fn (?Collection $data) => $data?->isNotEmpty());
    }

    public function getShipyard(string $waypointSymbol): ShipyardData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return ShipyardData::from(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/shipyard')
                ->json('data')
        );
    }

    public function getJumpGate(string $waypointSymbol): JumpGateData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return JumpGateData::from(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/jump-gate')
                ->json('data')
        );
    }

    public function getConstructionSite(string $waypointSymbol): ConstructionSiteData
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($waypointSymbol);

        return ConstructionSiteData::from(
            $this->get('systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/construction')
                ->json('data')
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

        return SupplyConstructionSiteData::from(
            $this->post(
                'systems/' . $systemSymbol . '/waypoints/' . $waypointSymbol . '/construction/supply',
                $payload
            )->json('data')
        );
    }

    private function listGoodsInSystem(string $waypointSymbol, string $type): Collection
    {
        return $this->listMarketplacesInSystem($waypointSymbol)
            ->pluck($type)
            ->flatten()
            ->unique('symbol');
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

    private function logRequest(
        string $method,
        string $path,
        array $payload
    ): void {
        $method = strtoupper($method);
        $payload = json_encode($payload);

        Log::channel('api_requests')
            ->info("{$method} {$path} {$payload}");
    }

    private function baseRequest(
        string $method,
        string $path,
        array $payload,
        int $attempts = 0
    ): Response {
        if ($attempts > 30) {
            throw new \Exception('Too many attempts.');
        }

        /** @var bool|PendingRequest */
        $request = RateLimiter::attempt('API_REQUESTS', 2, fn () => Http::withToken($this->token), 1);

        if (!$request) {
            sleep(1);

            return $this->baseRequest($method, $path, $payload, $attempts + 1);
        }

        $this->logRequest($method, $path, $payload);

        return $request->{$method}($this->url . $path, $payload)
            ->throwUnlessStatus(HttpResponse::HTTP_OK);
    }

    private function get(string $path = '', array $query = []): Response
    {
        return $this->baseRequest('get', $path, $query);
    }

    private function post(string $path, array $payload = []): Response
    {
        return $this->baseRequest('post', $path, $payload);
    }

    private function patch(string $path, array $payload = []): Response
    {
        return $this->baseRequest('patch', $path, $payload);
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
                ->filter(
                    fn (ImportExportExchangeGoodData|TradeGoodsData $data) => $data->symbol === $tradeSymbol->value
                )->isNotEmpty()
        );
    }

    private static function paginationParams(int $perPage, int $page, bool $all): array
    {
        return [
            'limit' => $all ? static::MAX_PER_PAGE : $perPage,
            'page' => $page,
        ];
    }
}
