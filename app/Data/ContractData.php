<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ContractTypes;
use App\Enums\FactionSymbols;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ContractData extends Data
{
    /**
     * @param Collection<int, DeliveryData> $deliveries
     */
    public function __construct(
        #[MapInputName('identification')]
        public string $identification,
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $factionSymbol,
        #[MapInputName('type')]
        #[WithCast(EnumCast::class)]
        public ContractTypes $type,
        #[MapInputName('accepted')]
        public bool $accepted,
        #[MapInputName('fulfilled')]
        public bool $fulfilled,
        #[MapInputName('terms.deadline')]
        public Carbon $deadline,
        #[MapInputName('deadlineToAccept')]
        public Carbon $deadlineToAccept,
        #[MapInputName('terms.payment.onAccepted')]
        public int $paymentOnAccepted,
        #[MapInputName('terms.payment.onFulfilled')]
        public int $paymentOnFulfilled,
        #[MapInputName('terms.deliver')]
        public Collection $deliveries,
    ) {}
}
