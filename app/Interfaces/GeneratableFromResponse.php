<?php

declare(strict_types=1);

namespace App\Interfaces;

interface GeneratableFromResponse
{
    public static function fromResponse(array $response): static;
}
