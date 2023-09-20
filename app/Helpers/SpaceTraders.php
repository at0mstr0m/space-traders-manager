<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Redis;

class SpaceTraders
{
    private const CACHE_KEY = 'REQUEST_COUNT';

    public function __construct(
        private string $token,
        private string $url = 'https://api.spacetraders.io/v2/',
    ) {
    }

    // only allow 2 requests per second
    private function avoidRateLimit(): void
    {
        if (Cache::has(static::CACHE_KEY)) {
            if (Cache::get(static::CACHE_KEY) < 3) {
                Cache::increment(static::CACHE_KEY);
            } else {
                sleep(1);
            }
        } else {
            Cache::add('key', 1, now()->addSecond());
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
