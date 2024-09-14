<?php

declare(strict_types = 1);

namespace App\DTO\Contracts;

interface ProductInterface
{
    public function getId(): ?int;

    public function getName(): string;

    public function getType(): string;

    public function getQuantity(): float;

    public function setQuantity(float $quantity): void;

    public function getUnit(): string;

    public function setUnit(string $unit): void;
}
