<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Arr;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;

trait HasCollectionFromResponse
{
    public static function collectionFromResponse(array $data): DataCollection
    {
        $method = match (true) {
            property_exists(static::class, 'responseTransformer') => static::$responseTransformer,
            is_a(static::class, GeneratableFromResponse::class) => 'fromResponse',
            default => 'from',
        };

        return static::collection(Arr::map($data, fn ($item) => static::{$method}($item)));
    }
}
