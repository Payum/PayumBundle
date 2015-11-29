<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class PayumController extends Controller
{
    /**
     * @return Payum
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @deprecated will be removed in 2.0.
     *
     * @return HttpRequestVerifierInterface
     */
    protected function getHttpRequestVerifier()
    {
        return $this->getPayum()->getHttpRequestVerifier();
    }

    /**
     * @deprecated will be removed in 2.0.
     *
     * @return GenericTokenFactoryInterface
     */
    protected function getTokenFactory()
    {
        return $this->getPayum()->getTokenFactory();
    }
}