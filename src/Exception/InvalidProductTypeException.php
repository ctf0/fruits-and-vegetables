<?php

declare(strict_types = 1);

namespace App\Exception;

use App\Enum\ProductTypeEnum;

final class InvalidProductTypeException extends \Exception
{
    public function __construct()
    {
        $types = implode(', ', ProductTypeEnum::toArray());

        parent::__construct("Invalid type, only support: ({$types})");
    }
}
