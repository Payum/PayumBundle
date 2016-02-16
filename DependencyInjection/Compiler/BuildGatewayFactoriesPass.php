<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuildGatewayFactoriesPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('payum.static_registry');

        $servicesIds = [];
        foreach ($container->findTaggedServiceIds('payum.gateway_factory') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (false == isset($attributes['factory'])) {
                    throw new LogicException('The payum.gateway_factory tag require factory attribute.');
                }

                $servicesIds[$attributes['factory']] = $serviceId;
            }
        }

        $registry->replaceArgument(2, $servicesIds);
    }
}