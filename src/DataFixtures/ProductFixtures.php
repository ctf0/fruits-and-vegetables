<?php

declare(strict_types = 1);

// src/DataFixtures/ProductFixtures.php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Enum\ProductUnitEnum;
use App\Service\UnitConverter;
use App\Service\FileDataLoader;
use Illuminate\Support\Collection;
use App\DTO\Contracts\ProductInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    /**
     * @var Collection<ProductInterface>
     */
    private Collection $collection;

    public function __construct(FileDataLoader $collectionLoader)
    {
        $this->collection  = $collectionLoader();
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->collection as $item) {
            $product = new Product();
            $product->setName($item->getName());
            $product->setType($item->getType());
            $product->setQuantity(UnitConverter::convert($item));
            $product->setUnit(ProductUnitEnum::GRAM);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
