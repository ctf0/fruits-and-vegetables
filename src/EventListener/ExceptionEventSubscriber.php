<?php

declare(strict_types = 1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $environment,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['genericException', 0],
                ['validationException', 1],
            ],
        ];
    }

    public function validationException(ExceptionEvent $event): void
    {
        $exception = $this->getHttpOriginalException($event);

        if ($exception instanceof ValidationFailedException) {
            $violations = $exception->getViolations();

            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            $event->setResponse(
                new JsonResponse([
                    'success' => false,
                    'message' => 'validation error',
                    'data'    => [
                        'errors' => $errors,
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }
    }

    public function genericException(ExceptionEvent $event): void
    {
        $exception = $this->getHttpOriginalException($event);

        $data = [
            'data'    => [],
            'success' => false,
            'message' => $exception->getMessage(),
        ];

        if ($this->environment === 'dev') {
            $data['trace'] = $exception->getTrace();
        }

        $event->setResponse(
            new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR)
        );
    }

    protected function getHttpOriginalException(ExceptionEvent $event): \Throwable
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            return $exception->getPrevious();
        }

        return $exception;
    }
}
