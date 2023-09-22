<?php

declare(strict_types=1);

namespace App\Helpers;

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
            Cache::remember(static::LIMITER_ONE, now()->addSecond(), fn() => true);
        } elseif (Cache::get(static::LIMITER_ONE) && Cache::get(static::LIMITER_TWO)) {
            sleep(1);
            Cache::remember(static::LIMITER_ONE, now()->addSecond(), fn() => true);
        } elseif (Cache::get(static::LIMITER_ONE)) {
            Cache::remember(static::LIMITER_TWO, now()->addSecond(), fn() => true);
        }
    }

    private function baseRequest(): PendingRequest
    {
        $this->avoidRateLimit();
        return HTTP::withToken($this->token);
    }

    private function get(string $path): Response
    {
        return $this->baseRequest()->get($this->url . $path);
    }

    public function agent()
    {
        return $this->get('my/agent')->collect('data');
    }

    public function agents()
    {
        return $this->get('agents')->collect('data');
    }

    public function agentDetails(string $agentSymbol)
    {
        return $this->get('agents/' . $agentSymbol)->collect('data');
    }
}
