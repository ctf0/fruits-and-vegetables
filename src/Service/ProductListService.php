<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\Product;
use App\Enum\ProductTypeEnum;
use App\Factory\ProductFactory;
use Illuminate\Support\Collection;
use App\Repository\ProductRepository;
use App\Exception\InvalidProductTypeException;
use Symfony\Component\HttpFoundation\InputBag;

final class ProductListService
{
    private InputBag $queryString;
    private ?iterable $collection = null;

    public function __construct(
        private ProductRepository $productRepository,
    ) {
    }

    public function setQuery(InputBag $query): self
    {
        $this->queryString = $query;

        return $this;
    }

    /**
     * @throws InvalidProductTypeException
     */
    public function filterBy(): self
    {
        $q    = $this->productRepository->createQueryBuilder('p');
        $andX = $q->expr()->andX();

        $allowedFilters = $this->executeFilters($andX, $q);
        $search         = $this->executeSearch($andX, $q);

        // results
        if (count($allowedFilters) || $search) {
            $this->collection = $q->andWhere($andX)->getQuery()->getArrayResult();
        } else {
            $this->collection = $this->getCollection();
        }

        return $this;
    }

    private function executeSearch($andX, $q): ?string
    {
        $search = $this->queryString->get('search');

        if ($search) {
            $andX->add($q->expr()->like('p.name', ':name'));
            $q->setParameter('name', "%$search%");
        }

        return $search;
    }

    private function executeFilters($andX, $q): ?array
    {
        $query          = $this->queryString;
        $allowedFilters = (new Collection())
                            ->make([
                                'id',
                                'name',
                                'type',
                                'quantity',
                                'unit',
                            ])
                            ->filter(fn ($item) => $query->has($item))
                            ->all();

        if (count($allowedFilters)) {
            foreach ($allowedFilters as $filter) {
                $value = $query->get($filter);

                if ($filter == 'type' && !in_array($value, ProductTypeEnum::toArray())) {
                    throw new InvalidProductTypeException();
                }

                $andX->add($q->expr()->eq("p.{$filter}", ":{$filter}"));
                $q->setParameter($filter, $value);
            }
        }

        return $allowedFilters;
    }

    public function convertTo(): self
    {
        $convert_to = $this->queryString->get('convert_to');

        if ($convert_to) {
            $this->collection = Collection::make($this->getCollection())
                ->map(function (array $product) use ($convert_to) {
                    $product = ProductFactory::createItem($product);

                    $product->setQuantity(UnitConverter::convert($product, $convert_to));
                    $product->setUnit($convert_to);

                    return $product->toArray();
                })
                ->all();
        }

        return $this;
    }

    /**
     * @return Product[]
     */
    public function all(): array
    {
        $q = $this->productRepository->createQueryBuilder('p');

        return $q->getQuery()->getArrayResult();
    }

    /**
     * @return Product[]
     */
    public function getCollection(): ?array
    {
        return is_array($this->collection) ? $this->collection : $this->all();
    }
}
