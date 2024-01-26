<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Bundle\PayumBundle\Traits\ControllerTrait;
use Payum\Core\Payum;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class PayumController extends AbstractController
{
    use ControllerTrait;

    protected ?Payum $payum = null;

    public function __construct(?Payum $payum = null)
    {
        if ($payum === null) {
            @trigger_error(
                sprintf(
                    '%s requires an instance of %s as the first argument. Not passing this object is deprecated and it will be required in payum/payum-bundle 3.0.',
                    __METHOD__,
                    Payum::class
                ),
                E_USER_DEPRECATED
            );
        }

        $this->payum = $payum;
    }

    /**
     * @deprecated since 2.5 and will be removed in 3.0. Use $this->payum instead.
     */
    protected function getPayum()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        if (!str_starts_with($backtrace[1]['class'], 'Payum\\Bundle\\PayumBundle')) {
            // Only trigger deprecation if called from outside the bundle
            @trigger_error(
                sprintf(
                    'The method %s is deprecated since 2.5 and will be removed in 3.0. Use $this->payum instead',
                    __METHOD__,
                ),
                E_USER_DEPRECATED
            );
        }

        return $this->payum ?? $this->container->get('payum');
    }

    /**
     * @deprecated will be removed in 3.0.
     *
     * @return HttpRequestVerifierInterface
     */
    protected function getHttpRequestVerifier()
    {
        @trigger_error(
            sprintf(
                'The method %s is deprecated since 2.5 and will be removed in 3.0. Use $this->payum->getHttpRequestVerifier() instead',
                __METHOD__,
            ),
            E_USER_DEPRECATED
        );

        return $this->getPayum()->getHttpRequestVerifier();
    }

    /**
     * @deprecated will be removed in 3.0.
     *
     * @return GenericTokenFactoryInterface
     */
    protected function getTokenFactory()
    {
        @trigger_error(
            sprintf(
                'The method %s is deprecated since 2.5 and will be removed in 3.0. Use $this->payum->getTokenFactory() instead',
                __METHOD__,
            ),
            E_USER_DEPRECATED
        );

        return $this->getPayum()->getTokenFactory();
    }
}
