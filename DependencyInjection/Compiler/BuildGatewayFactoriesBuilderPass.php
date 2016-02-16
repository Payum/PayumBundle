<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuildGatewayFactoriesBuilderPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $builder = $container->getDefinition('payum.builder');
        foreach ($container->findTaggedServiceIds('payum.gateway_factory_builder') as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (false == isset($attributes['factory'])) {
                    throw new LogicException('The payum.gateway_factory tag require factory attribute.');
                }

                $builder->addMethodCall('addGatewayFactory', [$attributes['factory'], new Reference($serviceId)]);
            }
        }
    }
}