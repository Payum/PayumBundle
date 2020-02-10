<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ReplyToHttpResponseListener
{
    /**
     * @var ReplyToSymfonyResponseConverter
     */
    private $replyToSymfonyResponseConverter;

    /**
     * @param ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter
     */
    public function __construct(ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter)
    {
        $this->replyToSymfonyResponseConverter = $replyToSymfonyResponseConverter;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if (false === $event->getThrowable() instanceof ReplyInterface) {
            return;
        }

        $response = $this->replyToSymfonyResponseConverter->convert($event->getThrowable());

        $event->allowCustomResponseCode();

        $event->setResponse($response);
    }
}
