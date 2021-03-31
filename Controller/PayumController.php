<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PayumController extends AbstractController
{
    /**
     * @return Payum
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'payum' => Payum::class,
        ]);
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
