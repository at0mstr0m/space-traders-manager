<?php

declare(strict_types=1);

namespace App\Traits;

use App\Data\DepositData;
use Illuminate\Support\Arr;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;

trait HasCollectionFromResponse
{
    public static function collectionFromResponse(array $data): DataCollection
    {
        $method = match (true) {
            property_exists(self::class, 'responseTransformer') => self::$responseTransformer,
            is_a(self::class, GeneratableFromResponse::class) => 'fromResponse',
            default => 'from',
        };

        return self::collection(Arr::map($data, fn ($item) => self::{$method}($item)));
    }
}
