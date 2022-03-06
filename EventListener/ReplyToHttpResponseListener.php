<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ReplyToHttpResponseListener
{
    public function __construct(private ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter)
    {}

    public function onKernelException(ExceptionEvent $event): void
    {
        if (false === $event->getThrowable() instanceof ReplyInterface) {
            return;
        }

        /** @var $throwable ReplyInterface */
        $throwable = $event->getThrowable();
        $response = $this->replyToSymfonyResponseConverter->convert($throwable);

        $event->allowCustomResponseCode();

        $event->setResponse($response);
    }
}
