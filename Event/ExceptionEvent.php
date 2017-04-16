<?php

namespace Payum\Bundle\PayumBundle\Event;

use Payum\Core\Action\ActionInterface;
use Exception;

class ExceptionEvent extends RequestEvent
{
    private $exception;

    public function __construct(Exception $exception, $request, ActionInterface $action = null)
    {
        $this->exception = $exception;
        parent::__construct($request, $action);
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->reply;
    }

    /**
     * @param Exception $exception
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
    }
}
