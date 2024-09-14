<?php

declare(strict_types = 1);

namespace App\Enum;

enum ProductUnitEnum
{
    const KILO_GRAM = 'kg';
    const GRAM      = 'g';

    public static function toArray(): array
    {
        return [self::KILO_GRAM, self::GRAM];
    }
}
