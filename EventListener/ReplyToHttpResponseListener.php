<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ReplyToHttpResponseListener
{
    private ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter;

    public function __construct(ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter)
    {
        $this->replyToSymfonyResponseConverter = $replyToSymfonyResponseConverter;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (false === $event->getThrowable() instanceof ReplyInterface) {
            return;
        }

        /** @var ReplyInterface $throwable */
        $throwable = $event->getThrowable();
        $response = $this->replyToSymfonyResponseConverter->convert($throwable);

        $event->allowCustomResponseCode();

        $event->setResponse($response);
    }
}
