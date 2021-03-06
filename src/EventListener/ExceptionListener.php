<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\Server\Exception\{
    Exception as ServerExceptionInterface,
    ActionNotImplemented,
    HttpResourceDenormalizationException,
    FilterNotApplicable
};
use Innmind\Http\Exception\{
    Exception as BaseHttpExceptionInterface,
    Http\Exception,
    Http\MethodNotAllowed,
    Http\BadRequest
};
use Symfony\Component\{
    EventDispatcher\EventSubscriberInterface,
    HttpKernel\KernelEvents,
    HttpKernel\Event\GetResponseForExceptionEvent,
    HttpKernel\Exception\HttpException
};

final class ExceptionListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [['transformException', 100]],
        ];
    }

    /**
     * Transform an innmind http exception into a symfony one to generate the
     * appropriate http response
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     */
    public function transformException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof ServerExceptionInterface) {
            $exception = $this->map($exception);
        }

        if (
            $exception instanceof BaseHttpExceptionInterface &&
            !$exception instanceof Exception
        ) {
            $exception = new BadRequest;
        }

        if (!$exception instanceof Exception) {
            return;
        }

        $event->setException(
            new HttpException(
                $exception->httpCode(),
                null,
                $exception
            )
        );
    }

    private function map(ServerExceptionInterface $exception): \Throwable
    {
        switch (true) {
            case $exception instanceof ActionNotImplemented:
                return new MethodNotAllowed;
            case $exception instanceof HttpResourceDenormalizationException:
            case $exception instanceof FilterNotApplicable:
                return new BadRequest;
        }

        return $exception;
    }
}
