<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PayumController extends AbstractController
{
    /**
     * @var Payum
     */
    private $payum;

    /**
     * PayumController constructor.
     * @param RegistryInterface $payum
     */
    public function __construct(RegistryInterface $payum)
    {
        $this->payum = $payum;
    }

    /**
     * @return Payum
     */
    protected function getPayum()
    {
        return $this->payum;
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
