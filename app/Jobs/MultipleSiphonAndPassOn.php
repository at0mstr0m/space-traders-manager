<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ship;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MultipleSiphonAndPassOn extends MultipleMineAndPassOn
{
    /**
     * @override
     */
    protected function initCompanions(): void
    {
        $this->companions = new EloquentCollection();
    }

    /**
     * @override
     */
    protected function initSurveyor(): void {}

    /**
     * @override
     */
    protected function performExtraction(Ship $ship): void
    {
        $ship->siphonResources();
    }
}
