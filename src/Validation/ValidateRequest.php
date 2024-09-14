<?php

declare(strict_types = 1);

namespace App\Validation;

use Jawira\CaseConverter\Convert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

// https://blog.redrat.com.br/validating-requests-on-symfony-framework
abstract class ValidateRequest
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected RequestStack $requestStack,
    ) {
        $this->populate();
        $this->validate();
    }

    public function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    protected function populate(): void
    {
        $request    = $this->getRequest();
        $reflection = new \ReflectionClass($this);

        foreach ($request->toArray() as $property => $value) {
            $attribute = self::camelCase($property);

            if (property_exists($this, $attribute)) {
                $reflectionProperty = $reflection->getProperty($attribute);
                $reflectionProperty->setValue($this, $value);
            }
        }
    }

    /**
     * @throws ValidationFailedException
     */
    protected function validate(): void
    {
        $violations = $this->validator->validate($this);

        if (count($violations)) {
            throw new ValidationFailedException('', $violations);
        }
    }

    private static function camelCase(string $attribute): string
    {
        return (new Convert($attribute))->toCamel();
    }
}
