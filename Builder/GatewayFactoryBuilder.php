<?php
namespace Payum\Bundle\PayumBundle\Builder;

use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;

class GatewayFactoryBuilder
{
    /**
     * @var string
     */
    private $gatewayFactoryClass;

    /**
     * @param string $gatewayFactoryClass
     */
    public function __construct($gatewayFactoryClass)
    {
        $this->gatewayFactoryClass = $gatewayFactoryClass;
    }

    /**
     * @param StorageInterface $tokenStorage
     *
     * @return HttpRequestVerifierInterface
     */
    public function build(StorageInterface $tokenStorage)
    {
        $gatewayFactoryClass = $this->gatewayFactoryClass;

        return new $gatewayFactoryClass($tokenStorage);
    }
}