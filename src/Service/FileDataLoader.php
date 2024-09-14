<?php

declare(strict_types = 1);

namespace App\Service;

use App\Factory\ProductFactory;
use Illuminate\Support\Collection;
use App\DTO\Contracts\ProductInterface;

class FileDataLoader
{
    public function __construct(private string $filePath)
    {
    }

    /**
     * @return Collection<ProductInterface>
     */
    public function __invoke(): Collection
    {
        $jsonContent = file_get_contents($this->filePath);

        if ($jsonContent === false) {
            throw new \RuntimeException('Failed to read the JSON file.');
        }

        $data  = json_decode($jsonContent, associative: true, flags: JSON_THROW_ON_ERROR);

        return Collection::make($data)->map(fn ($item) => ProductFactory::createItem($item));
    }
}
