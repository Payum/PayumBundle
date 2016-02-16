<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuildGatewayFactoryPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $builder = $container->getDefinition('payum.builder');
        foreach ($container->findTaggedServiceIds('payum.gateway_factory_builder') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                // FC layer
                if (isset($attributes['factory_name']) && false == isset($attributes['factory'])) {
                    $attributes['factory'] = $attributes['factory_name'];
                }

                if (false == isset($attributes['factory'])) {
                    throw new LogicException('The payum.gateway_factory tag require factory attribute.');
                }
                $builder->addMethodCall('addGatewayFactory', [$attributes['factory'], new Reference($serviceId)]);
            }
        }

        $this->processDeprecated($container);
    }

    /**
     * @deprecated  since 1.2 and will be removed in 2.0
     */
    protected function processDeprecated(ContainerBuilder $container)
    {
        $gatewayFactory = $container->getDefinition('payum.core_gateway_factory_builder');

        $gatewayFactory->replaceArgument(0, $container->findTaggedServiceIds('payum.action'));
        $gatewayFactory->replaceArgument(1, $container->findTaggedServiceIds('payum.extension'));
        $gatewayFactory->replaceArgument(2, $container->findTaggedServiceIds('payum.api'));
    }
}