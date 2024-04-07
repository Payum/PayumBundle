<?php

namespace Payum\Bundle\PayumBundle\Builder;

use Payum\Bundle\PayumBundle\ContainerAwareCoreGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\ContainerAwareInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\ContainerAwareTrait;
use Payum\Core\GatewayFactoryInterface;

class CoreGatewayFactoryBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    /**
     * @return GatewayFactoryInterface
     */
    public function build(array $defaultConfig)
    {
        $coreGatewayFactory = new ContainerAwareCoreGatewayFactory($defaultConfig);
        $coreGatewayFactory->setContainer($this->container);

        return $coreGatewayFactory;
    }
}
