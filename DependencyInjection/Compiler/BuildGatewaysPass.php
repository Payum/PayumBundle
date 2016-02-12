<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildGatewaysPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('payum.static_registry');

        $servicesIds = [];
        foreach ($container->findTaggedServiceIds('payum.gateway') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (false == isset($attributes['gateway'])) {
                    throw new LogicException('The payum.gateway tag require gateway attribute.');
                }

                $servicesIds[$attributes['gateway']] = $serviceId;
            }
        }

        $registry->replaceArgument(0, $servicesIds);
    }
}
