<?php

declare(strict_types = 1);

namespace App\FormRequest;

use App\Enum\ProductTypeEnum;
use App\Enum\ProductUnitEnum;
use App\Validation\ValidateRequest;
use Symfony\Component\Validator\Constraints as Assert;

class ProductStoreRequest extends ValidateRequest
{
    #[Assert\NotBlank, Assert\Length(min: 4)]
    public string $name;

    #[Assert\NotBlank, Assert\Length(min: 4)]
    #[Assert\Choice(callback: [ProductTypeEnum::class, 'toArray'])]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Type('float')]
    public float $quantity;

    #[Assert\NotBlank, Assert\Length(min: 1)]
    #[Assert\Choice(callback: [ProductUnitEnum::class, 'toArray'])]
    public string $unit;

    public ?int $id = null;

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'type'     => $this->type,
            'quantity' => $this->quantity,
            'unit'     => $this->unit,
        ];
    }
}
