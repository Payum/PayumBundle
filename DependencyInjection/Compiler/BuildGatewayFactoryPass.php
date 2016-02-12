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
        foreach ($container->findTaggedServiceIds('payum.gateway_factory') as $factoryId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (false == isset($attributes['factory_name'])) {
                    throw new LogicException('The payum.gateway_factory tag require factory_name attribute.');
                }

                $builder->addMethodCall('addGatewayFactory', [
                    $attributes['factory_name'],
                    new Reference($factoryId)
                ]);
            }
        }
    }
}