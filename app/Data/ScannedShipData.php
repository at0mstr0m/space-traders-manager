<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Enums\FrameSymbols;
use App\Enums\ShipRoles;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

class ScannedShipData extends Data implements GeneratableFromResponse
{
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $name,
        public string $factionSymbol,
        public string $role,
        public NavigationData $nav,
        public string $frameSymbol,
        public string $reactorSymbol,
        public string $engineSymbol,
        public array $mounts,
    ) {
        match (true) {
            !ShipRoles::isValid($role) => throw new \InvalidArgumentException("Invalid role: {$role}"),
            !FactionSymbols::isValid($factionSymbol) => throw new \InvalidArgumentException("Invalid faction symbol: {$factionSymbol}"),
            !FrameSymbols::isValid($frameSymbol) => throw new \InvalidArgumentException("Invalid frame symbol: {$frameSymbol}"),
            default => null,
        };
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            name: $response['registration']['name'],
            factionSymbol: $response['registration']['factionSymbol'],
            role: $response['registration']['role'],
            nav: NavigationData::fromResponse($response['nav']),
            frameSymbol: $response['frame']['symbol'],
            reactorSymbol: $response['reactor']['symbol'],
            engineSymbol: $response['engine']['symbol'],
            mounts: Arr::map($response['mounts'], fn (array $mount) => $mount['symbol']),
        );
    }
}
