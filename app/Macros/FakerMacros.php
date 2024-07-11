<?php

declare(strict_types=1);

namespace App\Macros;

use Faker\Provider\Base;
use Illuminate\Support\Str;

class FakerMacros extends Base
{
    public function sectorSymbol(): string
    {
        return 'X1';
    }

    public function systemSymbol(): string
    {
        return Str::of($this->sectorSymbol())
            ->append('-')
            ->append($this->randomLetter())
            ->append($this->optional(0.8)->randomLetter())
            ->append($this->randomDigitNotNull())
            ->append($this->randomDigitNotNull())
            ->upper()
            ->toString();
    }

    public function waypointSuffix(): string
    {
        return Str::of($this->randomLetter())
            ->append($this->numberBetween(1, 99))
            ->upper()
            ->toString();
    }

    public function waypointSymbol(): string
    {
        return Str::of($this->systemSymbol())
            ->append('-')
            ->append($this->randomLetter())
            ->append($this->numberBetween(1, 99))
            ->upper()
            ->toString();
    }

    public function waypointSymbols(int $count): array
    {
        $system = $this->systemSymbol();
        $result = [];

        for ($i = 0; $i < $count; ++$i) {
            $result[] = $system . '-' . $this->unique()->waypointSuffix();
        }

        return $result;
    }

    public function shipSymbol(): string
    {
        return Str::of('FAKE-SHIP-')
            ->append($this->randomLetter())
            ->append($this->numberBetween(1, 99))
            ->upper()
            ->toString();
    }
}
