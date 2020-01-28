<?php

namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildEntityManagerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $payumConfig = $container->getExtensionConfig('payum')[0];

        $serviceId = \sprintf('doctrine.orm.%s_entity_manager', $payumConfig['entity_manager']);

        if ($container->has($serviceId)) {
            $container->setAlias('payum.entity_manager', $serviceId);
        }
    }
}
