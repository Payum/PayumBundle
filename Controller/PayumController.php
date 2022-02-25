<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PayumController extends AbstractController
{
    protected Payum $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    protected function getPayum(): Payum
    {
        return $this->payum;
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'payum' => Payum::class,
        ]);
    }

    /**
     * @deprecated will be removed in 2.0.
     */
    protected function getHttpRequestVerifier(): HttpRequestVerifierInterface
    {
        return $this->getPayum()->getHttpRequestVerifier();
    }

    /**
     * @deprecated will be removed in 2.0.
     */
    protected function getTokenFactory(): GenericTokenFactoryInterface
    {
        return $this->getPayum()->getTokenFactory();
    }
}
