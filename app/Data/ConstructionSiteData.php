<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ConstructionSiteData extends Data
{
    /**
     * @param Collection<int, ConstructionMaterialData> $constructionMaterial
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $waypointSymbol,
        #[MapInputName('isComplete')]
        public bool $isComplete,
        #[MapInputName('materials')]
        public Collection $constructionMaterial,
    ) {}
}
