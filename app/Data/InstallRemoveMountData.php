<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class InstallRemoveMountData extends Data
{
    /**
     * @param Collection<int, MountData> $mounts
     */
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('cargo')]
        public ShipCargoData $cargo,
        #[MapInputName('transaction')]
        public ShipModificationTransactionData $transaction,
        #[MapInputName('mounts')]
        public ?Collection $mounts = null,
    ) {}

    // todo: implement UpdatesShip
}
