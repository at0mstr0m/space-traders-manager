<?php

declare(strict_types=1);

namespace App\Traits;

trait EnumUtils
{
    // https://stackoverflow.com/a/71680007/13128152
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    // https://stackoverflow.com/a/71680007/13128152
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    // https://stackoverflow.com/a/71680007/13128152
    public static function toArray(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function isValid(string $needle): bool
    {
        return in_array($needle, self::values(), true);
    }
}
