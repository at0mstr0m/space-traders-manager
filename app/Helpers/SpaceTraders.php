<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\PendingRequest;

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

    private function get(string $path,  $query = null): Response
    {
        return $this->baseRequest()->get($this->url . $path, $query);
    }

    private function getAllPages(Response $response, string $methodName, int $page): Collection
    {

        $data = $response->collect('data');
        $meta = $response->collect('meta');
        $totalNumber = data_get($meta, 'total');
        $perPage = data_get($meta, 'limit');
        $totalPages = (int) ceil($totalNumber / $perPage);

        for ($currentPage = $page + 1; $currentPage <= $totalPages; $currentPage++) {
            $data = $data->concat($this->{$methodName}($perPage, $currentPage));
        }

        return $data;
    }

    public function getAgent()
    {
        return $this->get('my/agent')->collect('data');
    }

    public function listAgents()
    {
        return $this->get('agents')->collect('data');
    }

    public function getAgentDetails(string $agentSymbol)
    {
        return $this->get('agents/' . $agentSymbol)->collect('data');
    }

    public function listFactions($perPage = 10, $page = 1, $all = false): Collection
    {
        $response =  $this->get('factions', ['limit' => $perPage, 'page' => $page]);

        return $all
            ? $this->getAllPages($response,  __FUNCTION__, $page)
            : $response->collect('data');
    }

    public function listShips($perPage = 10, $page = 1, $all = false)
    {
        $response = $this->get('my/ships', ['limit' => $perPage, 'page' => $page]);

        return $all
            ? $this->getAllPages($response,  __FUNCTION__, $page)
            : $response->collect('data');
    }
}
