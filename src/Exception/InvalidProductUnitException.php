<?php

declare(strict_types = 1);

namespace App\Exception;

use App\Enum\ProductUnitEnum;

final class InvalidProductUnitException extends \Exception
{
    public function __construct()
    {
        $types = implode(', ', ProductUnitEnum::toArray());

        parent::__construct("Invalid unit, only support: ({$types})");
    }
}
