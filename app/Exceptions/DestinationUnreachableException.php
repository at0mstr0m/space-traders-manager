<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Ship;

class DestinationUnreachableException extends \Exception
{
    public function __construct(
        Ship $ship,
        string $destinationWaypointSymbol,
        int $code = 1,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            "{$ship->symbol} cannot reach destination "
                . "{$destinationWaypointSymbol} from {$ship->waypoint_symbol}",
            $code,
            $previous
        );
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
