<?php

declare(strict_types = 1);

namespace App\Enum;

enum ProductTypeEnum
{
    const FRUIT     = 'fruit';
    const VEGETABLE = 'vegetable';

    public static function toArray(): array
    {
        return [self::FRUIT, self::VEGETABLE];
    }
}
