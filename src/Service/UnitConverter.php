<?php

declare(strict_types = 1);

namespace App\Service;

use App\Enum\ProductUnitEnum;
use App\DTO\Contracts\ProductInterface;
use App\Exception\InvalidProductUnitException;

final class UnitConverter
{
    /**
     * @throws InvalidProductUnitException
     */
    public static function convert(ProductInterface $item, string $convertToUnit = ProductUnitEnum::GRAM): float
    {
        $quantity  = $item->getQuantity();
        $fromUnit  = $item->getUnit();

        if ($fromUnit == ProductUnitEnum::KILO_GRAM && $convertToUnit == ProductUnitEnum::GRAM) {
            return $quantity * 1000;
        }

        if ($fromUnit == ProductUnitEnum::GRAM && $convertToUnit == ProductUnitEnum::KILO_GRAM) {
            return $quantity / 1000;
        }

        if ($fromUnit == $convertToUnit) {
            return $quantity;
        }

        throw new InvalidProductUnitException();
    }
}
