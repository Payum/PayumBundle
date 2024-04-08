<?php

namespace Payum\Bundle\PayumBundle\Builder;

use Payum\Bundle\PayumBundle\ContainerAwareCoreGatewayFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CoreGatewayFactoryBuilder
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    public function build(array $defaultConfig): ContainerAwareCoreGatewayFactory
    {
        return new ContainerAwareCoreGatewayFactory($this->container, $defaultConfig);
    }
}
