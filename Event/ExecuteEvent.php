<?php

namespace Payum\Bundle\PayumBundle\Event;

use Payum\Core\Extension\Context;
use Symfony\Contracts\EventDispatcher\Event;

class ExecuteEvent extends Event
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
