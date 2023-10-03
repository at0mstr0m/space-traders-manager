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
        $method = is_a(self::class, GeneratableFromResponse::class) ? 'fromResponse' : 'from';

        return self::collection(
            Arr::map(
                $data,
                fn (array $item) => self::{$method}($item)
            )
        );
    }
}
