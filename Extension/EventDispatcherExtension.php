<?php

namespace Payum\Bundle\PayumBundle\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\ReplyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Payum\Bundle\PayumBundle\PayumEvents;
use Payum\Bundle\PayumBundle\Event\RequestEvent;
use Payum\Bundle\PayumBundle\Event\ReplyEvent;
use Payum\Bundle\PayumBundle\Event\ExceptionEvent;

class EventDispatcherExtension implements ExtensionInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
        $event = new RequestEvent($request);
        $this->dispatcher->dispatch(PayumEvents::PAYMENT_PRE_EXECUTE, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        $event = new RequestEvent($request, $action);
        $this->dispatcher->dispatch(PayumEvents::PAYMENT_EXECUTE, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $event = new RequestEvent($request, $action);
        $this->dispatcher->dispatch(PayumEvents::PAYMENT_POST_EXECUTE, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action)
    {
        $event = new ReplyEvent($reply, $request, $action);
        $this->dispatcher->dispatch(PayumEvents::PAYMENT_REPLY, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $event = new ExceptionEvent($exception, $request, $action);
        $this->dispatcher->dispatch(PayumEvents::PAYMENT_EXCEPTION, $event);
    }
}
