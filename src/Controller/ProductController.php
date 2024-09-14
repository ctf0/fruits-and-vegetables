<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Factory\ProductFactory;
use App\Service\ProductListService;
use App\Repository\ProductRepository;
use App\FormRequest\ProductStoreRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductListService $productListService,
    ) {
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = $this->productListService
            ->setQuery($request->query)
            ->filterBy()
            ->convertTo()
            ->getCollection();

        return new JsonResponse([
            'data'    => $data,
            'success' => true,
        ]);
    }

    #[Route('/api/products', name: 'add_product', methods: ['POST'])]
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $dto = ProductFactory::createItem($request->toArray());

        $this->productRepository->store($dto);

        return new JsonResponse([
            'data'    => $this->productListService->getCollection(),
            'success' => true,
            'message' => 'Item added',
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'remove_product', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->productRepository->removeById($id);

        return new JsonResponse([
            'data'    => $this->productListService->getCollection(),
            'success' => true,
            'message' => 'Item removed',
        ]);
    }
}
