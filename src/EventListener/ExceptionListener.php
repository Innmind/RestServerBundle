<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Rest\Server\Exception\{
    ExceptionInterface as ServerExceptionInterface,
    ActionNotImplementedException,
    HttpResourceDenormalizationException,
    FilterNotApplicableException
};
use Innmind\Http\Exception\{
    ExceptionInterface as BaseHttpExceptionInterface,
    Http\ExceptionInterface,
    Http\MethodNotAllowedException,
    Http\BadRequestException
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
            !$exception instanceof ExceptionInterface
        ) {
            $exception = new BadRequestException;
        }

        if (!$exception instanceof ExceptionInterface) {
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
            case $exception instanceof ActionNotImplementedException:
                return new MethodNotAllowedException;
            case $exception instanceof HttpResourceDenormalizationException:
            case $exception instanceof FilterNotApplicableException:
                return new BadRequestException;
        }

        return $exception;
    }
}
