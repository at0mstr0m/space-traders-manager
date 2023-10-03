<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Data\AgentData;
use App\Data\FactionData;
use App\Data\ContractData;
use App\Data\ShipData;
use App\Data\WaypointData;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
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

    private function post(string $path = '', array $query = []): Response
    {
        return $this->baseRequest()->post($this->url . $path, $query);
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

    public function getContract(string $contractId)
    {
        return $this->get('my/contracts/' . $contractId)->collect('data');
    }

    public function acceptContract(string $contractId)
    {
        return $this->post('my/contracts/' . $contractId . '/accept')->collect('data');
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

    public function getWaypoint(string $symbol): Collection
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);
        return $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol)
            ->collect('data');
    }

    public function getMarket(string $symbol): Collection
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);
        return $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol . '/market')
            ->collect('data');
    }

    public function getShipyard(string $symbol): Collection
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);
        return $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol . '/shipyard')
            ->collect('data');
    }

    public function getJumpGate(string $symbol): Collection
    {
        [$sector, $system, $waypoint] = LocationHelper::parseLocation($symbol);
        return $this->get('systems/' . $sector . '-' . $system . '/waypoints/' . $symbol . '/jump-gate')
            ->collect('data');
    }
}
