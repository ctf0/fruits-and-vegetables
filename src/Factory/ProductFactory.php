<?php

declare(strict_types = 1);

namespace App\Factory;

use App\DTO\ProductDTO;
use App\DTO\Contracts\ProductInterface;

class ProductFactory
{
    public static function createItem(array $data): ProductInterface
    {
        return new ProductDTO(...$data);
    }
}
