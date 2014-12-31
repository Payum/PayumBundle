<?php

namespace Payum\Bundle\PayumBundle\Event;

use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Action\ActionInterface;

class ReplyEvent extends RequestEvent
{
    private $reply;

    public function __construct(ReplyInterface $reply, $request, ActionInterface $action = null)
    {
        $this->reply = $reply;
        parent::__construct($request, $action);
    }

    /**
     * @return ReplyInterface
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * @param ReplyInterface $reply
     */
    public function setReply(ReplyInterface $reply)
    {
        $this->reply = $reply;
    }
}
