<?php
declare(strict_types = 1);

namespace Innmind\Rest\ServerBundle\EventListener;

use Innmind\Http\Exception\Http\ExceptionInterface;
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
}
