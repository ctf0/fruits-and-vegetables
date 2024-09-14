<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Product;
use App\Enum\ProductUnitEnum;
use App\Service\UnitConverter;
use App\DTO\Contracts\ProductInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(
        private ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Product::class);
    }

    public function store(ProductInterface $item): void
    {
        $product = new Product();
        $product->setName($item->getName());
        $product->setType($item->getType());
        $product->setQuantity(UnitConverter::convert($item));
        $product->setUnit(ProductUnitEnum::GRAM);

        $em = $this->getEntityManager();
        $em->persist($product);
        $em->flush();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function removeById(int $id): void
    {
        $item = $this->find($id);

        if (!$item) {
            throw new EntityNotFoundException('entity not found');
        }

        $em = $this->getEntityManager();
        $em->remove($item);
        $em->flush();
    }
}
