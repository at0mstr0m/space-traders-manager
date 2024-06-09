<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ship;

class MultipleSiphonAndPassOn extends MultipleMineAndPassOn
{
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
