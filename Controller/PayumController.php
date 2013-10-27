<?php

namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Registry\RegistryInterface;
use Payum\Security\HttpRequestVerifierInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

abstract class PayumController extends ContainerAware
{
    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->container->get('payum');
    }

    /**
     * @return HttpRequestVerifierInterface
     */
    protected function getHttpRequestVerifier()
    {
        return $this->container->get('payum.security.http_request_verifier');
    }
}
