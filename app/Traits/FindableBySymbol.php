<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * @mixin Model
 */
trait FindableBySymbol
{
    public static function findBySymbol(string|\UnitEnum $symbol): ?static
    {
        return static::firstWhere('symbol', is_string($symbol) ? $symbol : $symbol->value);
    }

    public function scopeSearchBySymbol(Builder $query, string $search = ''): Builder
    {
        return $query->when(
            $search ?: request('search'),
            fn (Builder $query, string $searchTerm) => $query->where('symbol', 'like', "%{$searchTerm}%")
        );
    }
}
