<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionToJsonSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $event->setResponse(
            new JsonResponse([
                'status' => $this->getStatus($exception),
                'code' => $exception?->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ])
        );
    }

    public function getStatus(\Throwable $exception): int
    {
        // Si ce n'est pas une exception HTTP, on crée une erreur 500 générique.
        return $exception instanceof HttpException
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
