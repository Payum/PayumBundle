<?php

namespace Payum\Bundle\PayumBundle\Request;

use Payum\Request\BaseInteractiveRequest;
use Symfony\Component\HttpFoundation\Response;

class ResponseInteractiveRequest extends BaseInteractiveRequest
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
