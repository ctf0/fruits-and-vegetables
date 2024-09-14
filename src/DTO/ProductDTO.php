<?php

declare(strict_types = 1);

namespace App\DTO;

use App\DTO\Contracts\ProductInterface;

class ProductDTO implements ProductInterface
{
    public function __construct(
        public string $name,
        public string $type,
        public float $quantity,
        public string $unit,
        public ?int $id = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->getId(),
            'name'     => $this->getName(),
            'type'     => $this->getType(),
            'quantity' => $this->getQuantity(),
            'unit'     => $this->getUnit(),
        ];
    }
}
