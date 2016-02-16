<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated  since 1.2 and will be removed in 2.0
 */
class BuildRegistryPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('payum.static_registry');

        $gatewaysIds = array();
        foreach ($container->findTaggedServiceIds('payum.gateway') as $gatewaysId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $gatewaysIds[$attributes['gateway']] = $gatewaysId;
            }
        }

        $storagesIds = array();
        foreach ($container->findTaggedServiceIds('payum.storage') as $storageId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $storagesIds[$attributes['model_class']] = $storageId;
            }
        }

        $availableGatewayFactories = array();
        $gatewaysFactoriesIds = array();
        foreach ($container->findTaggedServiceIds('payum.gateway_factory') as $gatewayFactoryId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                // FC layer
                if (isset($attributes['factory_name']) && false == isset($attributes['factory'])) {
                    $attributes['factory'] = $attributes['factory_name'];
                }

                if (false == isset($attributes['factory'])) {
                    throw new LogicException('The payum.gateway_factory tag require factory attribute.');
                }

                $gatewaysFactoriesIds[$attributes['factory']] = $gatewayFactoryId;

                $availableGatewayFactories[$attributes['factory']] = isset($attributes['human_name']) ?
                    $attributes['human_name'] :
                    $attributes['factory']
                ;
            }
        }

        $container->setParameter('payum.available_gateway_factories', array_replace(
            $availableGatewayFactories,
            $container->getParameter('payum.available_gateway_factories')
        ));

        $registry->replaceArgument(0, $gatewaysIds);
        $registry->replaceArgument(1, $storagesIds);
        $registry->replaceArgument(2, $gatewaysFactoriesIds);
    }
}
